<?php

namespace TRMEngine\DataSource;

use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataMapper\TRMSafetyFields;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\TRMDataObjectsCollection;
use TRMEngine\DataSource\Exceptions\TRMDataSourceNoUpdatebleFieldsException;
use TRMEngine\DataSource\Exceptions\TRMDataSourceSQLEmptyTablesListException;
use TRMEngine\DataSource\Exceptions\TRMDataSourceSQLInsertException;
use TRMEngine\DataSource\Exceptions\TRMDataSourceSQLNoSafetyFieldsException;
use TRMEngine\DataSource\Exceptions\TRMDataSourceWrongTableSortException;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\Exceptions\TRMSqlQueryException;
use TRMEngine\Helpers\TRMState;

/**
 * общий для всех классов обработки записей из таблиц БД MySQL,
 * принимает в качестве зависимости объет DataMapper,
 * для работы с БД использует статический объект TRMDBObject,
 * который работает через MySQLi
 */
class TRMSqlDataSource extends TRMState implements TRMDataSourceInterface
{
/** если не указан тип Join, то принимается это значение = "LEFT" */
const DATASOURCE_JOIN_DEFAULT = "LEFT";
/** константа показывающая, что нужно брать имена полей в кавычки */
const NEED_QUOTE = 32000;
/** константа показывающая, что брать имена полей в кавычки НЕ нужно */
const NOQUOTE = 32001;

/**
 * @var string - текущий SQL-запрос для получения записей из БД,
 * если строка не путсая, значит запрос еще не выполнен!
 * после удачного выполнения данного запроса строка опустощается!
 */
protected $QueryString = "";
/**
 * @var string - текущая строка SQL-запроса для вставки и обновления записей в БД,
 * если строка не путсая, значит запрос еще не выполнен!
 * после удачного выполнения данного запроса строка опустощается!
 */
protected $UpdateQueryString = "";
/**
 * @var string - текущая строка SQL-запроса для удаления записей из БД,
 * если строка не путсая, значит запрос еще не выполнен!
 * после удачного выполнения данного запроса строка опустощается!
 */
protected $DeleteQueryString = "";

/**
 * @var array - массив полей и значений $Params[FieldName] = array( FieldValue, Operator, AndOr... ),
 * значения которых будут использоваться при запросе SELECT в секции WHERE
 */
protected $Params = array();
/**
 * @var int - стартовая позиция для выборки - OFFSET, может применяться, например, для пагинации
 */
protected $StartPosition = null;
/**
 * @var int - количество записей для выборки - LIMIT, может применяться, например, для пагинации
 */
protected $Count = null;
/**
 * @var array - массив полей для сортировки - array( fieldname1 => "ASC | DESC", ... ) для ORDER BY
 */
protected $OrderFields = array();
/**
 * @var array - массив полей для группировки - array( fieldname1 => "" ) для GROUP BY
 */
protected $GroupFields = array();

/**
 * @var \mysqli - объект MySQLi для работы с БД MySQL, внедряется как зависимость через конструктор
 */
protected $MySQLiObject;


/**
 * @param \mysqli $MySQLiObject - драйвер для работы с MySQL
 */
public function __construct( \mysqli $MySQLiObject ) //$MainTableName, array $MainIndexFields, array $SecondTablesArray = null, $MainAlias = null )
{
    $this->MySQLiObject = $MySQLiObject; // TRMDBObject::$newlink; // TRMDIContainer::getStatic("TRMDBObject")->$newlink;
}

/**
 * устанавливает с какой записи начинать выборку - StartPosition
 * и какое количество записей выбирать - Count
 *
 * @param int - с какой записи начинать выборку
 * @param int - какое количество записей выбирать
 */
public function setLimit( $Count , $StartPosition = null )
{
    $this->StartPosition = $StartPosition;
    $this->Count = $Count;
}

/**
 * задает массив сортировки по полям, старые значения удаляются
 *
 * @param array - массив полей, по которым сортируется - array( fieldname1 => "ASC | DESC", ... )
 */
public function setOrder( array $orderfields )
{
    $this->OrderFields = array();

    $this->addOrder( $orderfields );
}

/**
 * Устанавливает поле для сортировки
 *
 * @param string $orderfieldname - имя поля , по которому устанавливается сортировка
 * @param int $asc - 1 - сортируется по этому полю как ASC, в противном случае, как DESC
 */
public function setOrderField( $orderfieldname, $asc = 1 )
{
    $this->OrderFields[$orderfieldname] = ( ($asc == 1) ? "ASC" : "DESC");
}

/**
 * добавляем поля в массив сортировки, если уже есть, то старые значения перезаписываются
 *
 * @param array - массив полей, по которым сортируется - array( fieldname1 => "ASC | DESC", ... )
 */
public function addOrder( array $orderfields )
{
    foreach( $orderfields as $field => $order )
    {
        if( empty($order) ) { $this->OrderFields[$field] = "ASC"; continue; }
        $order = trim(strtoupper($order));
        if( $order == "ASC" || $order == "DESC" )
        {
            $this->OrderFields[$field] = $order;
        }
        else
        {
            $this->OrderFields[$field] = "ASC";
        }
    }
}

/**
 * получает, проверяет и возвращает верный SQL-оператор
 * 
 * @param string $operator - оператор для проверки
 * @param string $default - оператор поумолчанию, если $operator не валиден
 * @return string - валидный SQL оператор
 */
public static function makeValidSQLOperator($operator, $default = "=")
{
    $operator = strtoupper(trim($operator));
    switch ($operator)
    {
        case "=":  
        case ">": 
        case "<": 
        case ">=": 
        case "<=": 
        case "!=": 
        case "<>": 
        case "IS": 
        case "NOT": 
        case "IN": 
        case "NOT IN": 
        case "BETWEEN": 
        case "NOT BETWEEN": 
        case "LIKE": 
        case "NOT LIKE": return $operator;
    }
    return $default;
}

/**
 * получает, проверяет и возвращает верный префикс оператора JOIN для SQL-запросов
 * 
 * @param string $join - оператор-приставка для JOIN, который нужно проверить, валидным считается LEGT, RIGHT, INNER, OUTER
 * @param string $default - если оператор не валиден, то присваивается значение $default, поумолчанию установлено в LEGT
 * 
 * @return string - валидный оператор-приставка для JOIN в SQL-запросе
 */
public static function makeValidJoinOperator($join, $default = TRMSqlDataSource::DATASOURCE_JOIN_DEFAULT)
{
    $join = strtoupper($join);
    switch ($join)
    {
        case "LEGT": 
        case "RIGHT": 
        case "INNER": 
        case "OUTER": return $join;
    }
    return $default;
}

/**
 * очистка параметров WHERE запроса и строк запросов SELECT, UPDATE/INSERT, DELETE
 */
public function clear()
{
    $this->QueryString = "";
    $this->UpdateQueryString = "";
    $this->DeleteQueryString = "";
    $this->clearParams();
}

/**
 * очистка параметров для WHERE-условий в SQL-запросе
 */
public function clearParams()
{
    $this->Params = array();
}

/**
 * формирует часть запроса со списком полей, которые выбираются из таблиц
 * 
 * @param TRMSafetyFields $SafetyFields - DataMapper, для которого формируется выборка из БД
 *
 * @return string - строка со списком полей
 * 
 * @throws TRMDataSourceSQLNoSafetyFieldsException
 */
private function generateFieldsString( TRMSafetyFields $SafetyFields )
{
    if( !$SafetyFields->count() )
    {
        throw new TRMDataSourceSQLNoSafetyFieldsException( __METHOD__  . " - " . get_class($this) );
    }
    $fieldstr = "";
    foreach( $SafetyFields as $TableName => $TableState )
    {        
        $TableAlias = $SafetyFields->getAliasForTableName($TableName);
        $tn = empty($TableAlias) ? $TableName : $TableAlias;
        foreach( $TableState[TRMDataMapper::FIELDS_INDEX] as $fieldname => $state )
        {
            if( !empty($tn) ) { $fieldstr .= "`" . $tn . "`."; }

            if( isset($state[ TRMDataMapper::QUOTE_INDEX ]) && $state[ TRMDataMapper::QUOTE_INDEX ] == TRMDataMapper::NEED_QUOTE )
            {
                $fieldstr .= "`" . $fieldname . "`";
            }
            else { $fieldstr .= $fieldname; }

            if( isset($state[TRMDataMapper::FIELDALIAS_INDEX]) && strlen($state[TRMDataMapper::FIELDALIAS_INDEX])>0 )
            {
                $fieldstr .= (" AS ".$state["FieldAlias"]);
            }
            $fieldstr .= ",";
        }
    }
    return rtrim($fieldstr, ",");
}

/**
 * формирует часть запроса связанную с JOIN таблиц
 * 
 * @param TRMSafetyFields $SafetyFields - DataMapper, для которого формируется выборка из БД
 *
 * @return string - строка с JOIN-частью запроса
 */
private function generateJoinString( TRMSafetyFields $SafetyFields )
{
    $JoinedTables = array();
    foreach( $SafetyFields as $CurrentTableName => $CurrentTableState )
    {
        foreach ( $CurrentTableState[TRMDataMapper::FIELDS_INDEX] as $CurrentFieldName => $CurrentFieldState )
        {
            // если есть Relation, занчит таблица из Relation должна быть присоединена по полю из Relation
            if( isset($CurrentFieldState[TRMDataMapper::RELATION_INDEX]) )
            {
                $JoinedTables
                    [ $CurrentFieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::OBJECT_NAME_INDEX] ]
                        [TRMDataMapper::FIELDS_INDEX]
                            [ $CurrentFieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::FIELD_NAME_INDEX] ]
                        = array(
                            // если для главной таблицы задан альяс, то будем испльзовать его в строке JOIN,
                            // если не задан, то имя таблицы
                            TRMDataMapper::OBJECT_NAME_INDEX => isset($CurrentTableState["ObjectAlias"]) ? $CurrentTableState["ObjectAlias"] : $CurrentTableName,
                            TRMDataMapper::FIELD_NAME_INDEX => $CurrentFieldName,
                            // оператор применяется в родительском $CurrentFieldName поле по отношению проверяемому TRMDataMapper::FIELD_NAME_INDEX...
                            "Operator" => isset($CurrentFieldState[TRMDataMapper::RELATION_INDEX]["Operator"]) ?
                                self::makeValidSQLOperator($CurrentFieldState[TRMDataMapper::RELATION_INDEX]["Operator"]) :
                                "=",
                        );
                if( !empty( $CurrentTableState["ObjectAlias"] ) )
                {
                    $JoinedTables
                        [ $CurrentFieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::OBJECT_NAME_INDEX] ]
                            ["ObjectAlias"] = $CurrentTableState["ObjectAlias"];
                }
                
                $JoinedTables
                    [ $CurrentFieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::OBJECT_NAME_INDEX] ]
                        ["Join"] = isset($CurrentFieldState[TRMDataMapper::RELATION_INDEX]["Join"]) ?
                            $CurrentFieldState[TRMDataMapper::RELATION_INDEX]["Join"] :
                            self::DATASOURCE_JOIN_DEFAULT;
            }
        }
    }

    // если никакие связи не нашлись, значит секция JOIN пуста!!!
    if( empty($JoinedTables) )
    {
        return "";
    }

    $joinstr = "";
    foreach ($JoinedTables as $TableName => $TableState)
    {
        if( empty($TableState) ) { continue; }

        $joinstr .= $this->generateJoinStringForTable($TableName, $TableState);
    }

    return $joinstr;
}

/**
 * 
 * @param string $TableName - имя таблицы
 * @param array $TableState - массив состояния (подмассив с именами полей, альяс для таблицы, 
 * возможно, указатель на метод подключения  JOIN (LEFT, RIGHT, INNER...) 
 * 
 * @return string - строка с частью JOIN-запроса для таблицы $TableName
 */
public function generateJoinStringForTable($TableName, array &$TableState)
{
    $joinstr = self::DATASOURCE_JOIN_DEFAULT;

    if( isset($TableState["Join"]) )
    {
        $tmpjoin = trim(strtoupper($TableState["Join"]));
        if( $tmpjoin === "LEFT OUTER" || $tmpjoin === "LEFT" )
        {
            $joinstr = "LEFT";
        }
        else if( $tmpjoin === "RIGHT OUTER" || $tmpjoin === "RIGHT" )
        {
            $joinstr = "RIGHT";
        }
        else if(
            $tmpjoin === "FULL OUTER" ||
            $tmpjoin === "INNER" ||
            $tmpjoin === "CROSS" )
        {
            $joinstr = $tmpjoin;
        }
    }

    $joinstr .= " JOIN `" . $TableName . "`";
    // если задан псевдоним таблицы, то добавляем его
    if( !empty($TableState["ObjectAlias"])  ) { $joinstr .= $TableState["ObjectAlias"]; }
    $joinstr .= " ON ";

    foreach( $TableState[TRMDataMapper::FIELDS_INDEX] as $FieldName => $FieldRelation )
    {
        $joinstr .= "`" . $FieldRelation[TRMDataMapper::OBJECT_NAME_INDEX] . "`.`" .  $FieldRelation[TRMDataMapper::FIELD_NAME_INDEX] . "`";
        // оператор сравнивает родительский элемент, котороый изначально ссылался через Relation в $SafetyFields
        // он идет первым!
        // с текущим элементом присоединяемой таблицы, он идет вторым дальше...
        $joinstr .= $FieldRelation["Operator"];
        // если для присоединяемой таблицы задан альяс, то будем испльзовать его в строке JOIN,
        // если не задан, то имя таблицы
        $joinstr .= !empty($TableState["ObjectAlias"]) ? $TableState["ObjectAlias"] : ("`" . $TableName . "`");
        $joinstr .= ".`{$FieldName}`";
        $joinstr .= " AND ";
    }
    return rtrim($joinstr, "AND ");
}

/**
 * формирует часть запроса связанную с условиями WHERE
 *
 * @return string - строка с WHERE-частью запроса
 */
private function generateWhereString()
{
    $wherestr = "";
    foreach( $this->Params as $TableName => $TableParams )
    {
        foreach( $TableParams as $param )
        {
            $key = $param["key"];
            if( isset($param["alias"]) && strlen($param["alias"])>0 ) { $key = $param["alias"] . "." . $key; }
            else if( !empty($TableName) ){ $key = $TableName . "." . $key; }
            if( isset($param["quote"]) && $param["quote"] == TRMSqlDataSource::NEED_QUOTE ) { $key = $this->prepareKey($key); }

            $wherestr .= $param["andor"] . " " . $key . " " . $param["operator"];

            if( $param["operator"] == "IN" || $param["operator"] == "NOT IN" ) { $wherestr .= " (" . trim( $param["value"], "() " ) . ") "; }
            else if( $param["operator"] == "IS" || 
                     $param["operator"] == "NOT" || 
                     (isset($param["dataquote"]) && $param["dataquote"] == TRMSqlDataSource::NOQUOTE)
                    ) { $wherestr .= " " . $param["value"] . " "; }
            else { $wherestr .= "'" . addcslashes( trim($param["value"], "'"), "'" ) . "' "; }
        }
    }

    return ltrim(trim($wherestr), "ANDOR"); // $wherestr;
}

/**
 * формирует список таблиц и их псевдонимов для секции FROM SELECT-запроса
 * имена таблиц и их псевдонимы берутся из SafetyFields
 * 
 * @param TRMSafetyFields $SafetyFields - DataMapper, для которого формируется выборка из БД
 * 
 * @return string - строка вида "`table1` AS `t1`, `table2` AS `ttt`"
 */
private function generateFromStr( TRMSafetyFields $SafetyFields )
{
    $MainTables = $SafetyFields->getObjectsNamesWithoutBackRelations();
    $fromstr="";
    foreach($MainTables as $TableName)
    {
        $fromstr .= "`{$TableName}`";
        $alias = $SafetyFields->getAliasForTableName($TableName);
        if( $alias )
        {
            $fromstr .= " AS `{$alias}`";
        }
        $fromstr .= ",";
    }
    return rtrim($fromstr, ",");
}

/**
 * формирует и возвращает строку SQL-запроса к БД для выбора записи
 * параметры WHERE устанавливаются перед вызовом этой функции 
 * с помощью - addWhereParam или addWhereParamFromArray
 * 
 * @param TRMSafetyFields $SafetyFields - DataMapper, для которого формируется выборка из БД
 *
 * @return string
 * 
 * @throws TRMDataSourceSQLEmptyTablesListException
 */
public function makeSelectQuery( TRMSafetyFields $SafetyFields )
{
    // строка с перечислением полей для выборки 
    $fieldstr = $this->generateFieldsString($SafetyFields);

    $fromstr = $this->generateFromStr($SafetyFields);
    
    if(empty($fromstr))
    {
        throw new TRMDataSourceSQLEmptyTablesListException( __METHOD__ );
    }
    
    $this->QueryString = "SELECT " . $fieldstr . " FROM " . $fromstr;
    
    // строка с секцией JOIN, присоединяются все таблицы из массива Tables
    $joinstr = $this->generateJoinString($SafetyFields);
    if( strlen($joinstr) ) { $this->QueryString .= $joinstr; }

    // строка с условиями WHERE
    $wherestr = $this->generateWhereString();
    if( strlen($wherestr) ) { $this->QueryString .= " WHERE " . $wherestr . " "; }

    // часть запроса для группировки
    if( !empty($this->GroupFields) )
    {
        $this->QueryString .= " GROUP BY ";
        foreach( $this->GroupFields as $field => $group )
        {
                $this->QueryString .= " {$field},";
        }
        $this->QueryString = rtrim($this->QueryString, ",") . " ";
    }

    // часть запроса для сортировки
    if( !empty($this->OrderFields) )
    {
        $this->QueryString .= " ORDER BY ";
        foreach( $this->OrderFields as $field => $order )
        {
                $this->QueryString .= " {$field} {$order},";
        }
        $this->QueryString = rtrim($this->QueryString, ",");
    }

    // часть запроса для установки начала выборки и ограничения на количество выбираемых записей по условию
    if( is_int($this->Count) ) { $this->QueryString .= " LIMIT {$this->Count}"; }
    if( is_int($this->StartPosition) ) { $this->QueryString .= " OFFSET {$this->StartPosition}"; }

    return $this->QueryString;
}

/**
 * оборачивает ключ в апострофы, убирая лишние и проверяя на наличе точки, т.е. указание на таблицу
 *
 * @param string $key - клюя для подготовки к использованию в запросах,
 * обрамляется апострофами `key`, если указана принадлежность к таблице через точку,
 * то сформируется строка вида `table`.`key`
 * 
 * @return string - подготовленный для вставки в запрос ключ таблицы
 */
protected function prepareKey($key)
{
    $key = "`".str_replace(".", "`.`", $key)."`";

    return str_replace("``", "`", $key);
}

/**
 * добавляет параметр для условия WHERE в запросе
 * 
 * @param string $tablename - имя таблицы для поля, которое добавляется к условию
 * @param string $fieldname - имя поля для сравнения
 * @param string|numeric|boolean $data - данные для сравнения
 * @param string $operator - оператор сравнения (=, !=, >, < и т.д.), поумолчанию =
 * @param string $andor - что ставить перед этим условием OR или AND ? по умолчанию AND
 * @param integer $quote - нужно ли брать в апострофы имена полей, по умолчанию нужно - TRMSqlDataSource::NEED_QUOTE
 * @param string $alias - альяс для таблицы из которой сравнивается поле, если не задан, то будет совпадать с альясом главной таблицы
 * @param integer $dataquote - если нужно оставить сравниваемое выражение без кавычек, 
 * то этот аргумент доложен быть - TRMSqlDataSource::NOQUOTE
 * 
 * @return $this
 */
public function addWhereParam($tablename, $fieldname, $data, $operator = "=", $andor = "AND", $quote = TRMSqlDataSource::NEED_QUOTE, $alias = null, $dataquote = TRMSqlDataSource::NEED_QUOTE)
{
    $value = array(
            "key" => $fieldname,
            "value" => $data,
            "operator" => $operator,
            "andor" => $andor,
            "quote" => $quote,
            "alias" => $alias, //($alias!== null) ? $alias : $this->AliasName,
            "dataquote" => $dataquote,
            );
    return $this->addWhereParamFromArray( $tablename, $value );
}

/**
 * добавляет условие в секцию WHERE-запроса
 * 
 * @param string $tablename - имя объекта для которого устанавливается поле
 * @param array $params - массив с параметрами следующего формата<br>
 * array(
 * "key" => $fieldname,<br>
 * "value" => $data,<br>
 * "operator" => $operator,<br>
 * "andor" => $andor,<br>
 * "quote" => $quote,<br>
 * "alias" => $alias,<br>
 * "dataquote" => $dataquote );
 * 
 * @return $this
 */
public function addWhereParamFromArray($tablename, array $params)
{
    $value = array();

    $value["key"] = $params["key"];
    $value["value"] = $params["value"];
    $value["operator"] = "=";
    $value["andor"] = "AND";
    $value["quote"] = TRMSqlDataSource::NEED_QUOTE;
    $value["alias"] = isset( $params["alias"] ) ? $params["alias"] : null; // $this->AliasName;
    $value["dataquote"] = TRMSqlDataSource::NEED_QUOTE;
    
    // проверяем, есть ли уже такое условие, что бы не добавлять второй раз дубликат
    if( isset($this->Params[$tablename]) )
    {
        foreach( $this->Params[$tablename] as $checkedparams )
        {
            if( $checkedparams["key"] === $value["key"] &&
                $checkedparams["value"] === $value["value"] &&
                $checkedparams["operator"] === $value["operator"] &&
                $checkedparams["andor"] === $value["andor"] &&
                $checkedparams["quote"] === $value["quote"] &&
                $checkedparams["alias"] === $value["alias"] &&
                $checkedparams["dataquote"] === $value["dataquote"]
                    )
            {
                return;
            }
        }
    }

    /* VALUE */
    if( is_string($value["value"]) || is_numeric($value["value"]) || is_bool($value["value"]) )
    {
        // для строчных значений экранируем одинарные кавычки, что бы не было конфликта в запросе
        if( is_string($value["value"]) )
        {
            $value["value"] = str_replace("'", "\\'", $value["value"]);
        }
    }
    
    /* OPERATOR - для простого value это может быть = или НЕ = */
    if( isset($params["operator"]) )
    {
        $value["operator"] = self::makeValidSQLOperator($params["operator"]);
    }

    /* AND OR */
    if( isset($params["andor"]) )
    {
        $value["andor"] = trim(strtoupper($params["andor"]));
        if( !($value["andor"] == "AND") && !($value["andor"] == "OR") )
        {
            $value["andor"] = "AND";
        }
    }

    /* QUOTE */
    // по умолчанию все имена полей берутся в апострофы, этой опцией можно убрать, например для вычисляемых полей */
    if( isset($params["quote"]) && $params["quote"] == TRMSqlDataSource::NOQUOTE )
    {
        $value["quote"] = TRMSqlDataSource::NOQUOTE;
    }
    if( isset($params["dataquote"]) && $params["dataquote"] == TRMSqlDataSource::NOQUOTE )
    {
        $value["dataquote"] = TRMSqlDataSource::NOQUOTE;
    }

    $this->Params[$tablename][] = $value;

    return $this;
}

/**
 * добавляет массив параметров к уже установленному
 *
 * @param string $tablename - имя объекта для которого устанавливаются параметры
 * @param array - параметры, используемые в запросе, как правило сюда передается ID-записи 
 * все должно передаваться в массиве array( $fieldname => array(value, operator, andor, quote, alias, dataquote), ...)
 * обязательными являются array(..., $fieldname => array(value), ...)
 */
public function generateParamsFrom( $tablename, array $params )
{
    if( $params === null )
    {
        return;
    }

    // если передаются параметры, значит удаляем уже установленные
    //unset($this->Params);
    //$this->Params = array();
    // в $params передан массив, перебираем значения
    foreach($params as $key => $value)
    {
        $this->addWhereParam($tablename, 
                        $key, 
                        isset($value["value"]) ? $value["value"] : "",
                        isset($value["operator"]) ? $value["operator"] : null,
                        isset($value["andor"]) ? $value["andor"] : null,
                        isset($value["quote"]) ? $value["quote"] : null,
                        isset($value["alias"]) ? $value["alias"] : null,
                        isset($value["dataquote"]) ? $value["dataquote"] : null

            );
    }
}

/**
 * Выполняет запрос переданный в $query
 * 
 * @param string $query - строка SQL-запроса
 * 
 * @return \mysqli_result - объект-результат выполнения запроса
 * @throws TRMSqlQueryException - в случае неудачного выполнения запроса выбрасывается исключение
 */
public function executeQuery($query)
{
    $result = $this->MySQLiObject->query($query);

    if( !$result )
    {
        throw new TRMSqlQueryException( __METHOD__ . " Запрос к БД вернул ошибку![{$query}]" );
    }
    return $result;
}

/**
 * вспомогательная функция для проверки и формирования запроса к БД
 * отправляет запрос на выполнение
 * функция вызывается из getFromDB и addFromDB
 * 
 * @param TRMSafetyFields $SafetyFields - DataMapper, для которого формируется выборка из БД
 *
 * @return \mysqli_result - объект-результат выполнения запроса
 * @throws TRMSqlQueryException - в случае неудачного выполнения запроса выбрасывается исключение
 */
private function runSelectQuery( TRMSafetyFields $SafetyFields )
{
    if( !$this->makeSelectQuery( $SafetyFields ) )
    {
        throw new TRMSqlQueryException( __METHOD__ . " Неудачно сформирован запрос к БД" );
    }

    return $this->executeQuery($this->QueryString);
}
 
/**
 * считывает данные из БД используя запрос, 
 * который формируется функция makeSelectQuery на основе SafetyFields 
 * и Where параметров
 * 
 * @param TRMSafetyFields $SafetyFields - DataMapper, для которого формируется выборка из БД
 *
 * @return \mysqli_result - объект с результатом запроса
 * @throws TRMSqlQueryException - в случае неудачного выполнения запроса выбрасывается исключение
 */
public function getDataFrom( TRMSafetyFields $SafetyFields )
{
    $result = $this->runSelectQuery($SafetyFields);
    if( !$result )
    {
        throw new TRMSqlQueryException( __METHOD__ . " Запрос к БД вернул ошибку![{$this->QueryString}]" );
    }

    $this->QueryString = "";
    return $result;
}

/**
 * @param TRMSafetyFields $SafetyFields - DataMapper, для которого формируется выборка из БД
 * @param array $IndexesNames - после работы функции будет содержать поля, 
 * которые следует включать в секцию WHERE update-запроса, 
 * проверяет сначала наличие первичных индексов, 
 * если не найдены первичные, то ищет уникальные ключи, 
 * если и они не неайдены, то, специально, для запроса DELETE будут добавлены все поля
 * для поиска по сравнению... 
 * @param array $UpdatableFieldsNames - после работы функции соержит массив доступных для записи полей
 * @param array $CurrentKeyFlag - если не null, то после работы функции будет содержать массив с видами ключа 
 * array( "PRI", "UNI", "*" ) или 
 * array( "PRI", "UNI" ) или 
 * array( "PRI" ), 
 * что бы знать по ключевым полям следует делать Update, или по уникальным...
 * разница в том, что 
 * отсутсвие данных первичного ключа для записи говорит, что это новая запись и ее нужно добавить,
 * а отсутсвие данных в уникальном ключе не позволит никак обновить данные и это будет ошибка обновления
 * 
 * @throws TRMDataSourceWrongTableSortException
 * @throws TRMDataSourceNoUpdatebleFieldsException
 */
private function generateIndexesAndUpdatableFieldsNames( TRMSafetyFields $SafetyFields, array &$IndexesNames, array &$UpdatableFieldsNames, array &$CurrentKeyFlag = null )
{
    // сортирует данные в DataMapper, таким образом,
    // что бы сначала шли все назависимые объеты, 
    // например, производители, группы, ед. измерения,
    // у уже потом зависимые от них 
    if(!$SafetyFields->sortObjectsForRelationOrder())
    {
        throw new TRMDataSourceWrongTableSortException(__METHOD__ . " отсортировать массив с таблицами не удалось");
    }
    if( isset($CurrentKeyFlag) )
    {
        $Keys = array( "PRI", "UNI" );
        $CurrentKeyFlag = array();
    }
    else
    {
        $Keys = array( "PRI", "UNI", "*" );
    }
    
    $UpdatableFieldsNames = array();
    $IndexesNames = array();
    
    foreach( $SafetyFields as $TableName => $TableState )
    {
        
        // получаем массив доступных для записи полей в очередной таблице $TableName
        $UpdatableFieldsNames[$TableName] = $SafetyFields->getUpdatableFieldsNamesFor($TableName);
        // если массив оказался пустым, 
        // то продолжаем цикл
        if( empty($UpdatableFieldsNames[$TableName]) )
        {
            unset($UpdatableFieldsNames[$TableName]);
            continue;
        }
        
        $IndexesNames[$TableName] = array();
        // если же есть доступные для записи поля в этой таблице,
        // то формируем поля, которые будут в секции WHERE update-запроса для таблицы $TableName
        // проверяем сначала наличие первичных индексов, 
        // если не найдены первичные, то поищем уникальные ключи,
        // если и они не неайлены, то для запроса DELETE будут добавлены все поля, 
        // для поиска записи по совпаденияю всех значений во всех полях таблицы
        foreach( $Keys as $Key )
        {
            $IndexesNames[$TableName] = $SafetyFields->getIndexFieldsNames($TableName, $Key);
            if( !empty($IndexesNames[$TableName]) )
            {
                // сохраняем вид ключа, что бы знать по ключевым полям будем делать Update, или по уникальным...
                // разница в том, что отсутсвие данных первичного ключа для записи говорит о том, что это новая запись и ее нужно добавить,
                // а отсутсвие данных в уникальном ключе не позволит никак обновить данные и это будет ошибка обновления!!!
                if( isset($CurrentKeyFlag) ) { $CurrentKeyFlag[$TableName] = $Key; }
                break;
            }
        }

        // если никакие поля для WHERE подобрать не удалось, 
        // то секция WHERE update-запроса останется пустой,
        // в этом случае запрос вида UPDATE TABLE SET FIELD1 = Value
        // обновит значения поля FIELD1 во всех записях TABLE...
        // это не совсем то, что нужно, 
        // поэтому обновляем и обновляемые поля для этой таблицы, вообще не трогаем ее...
        if( empty($IndexesNames[$TableName]) )
        {
            //$UpdatableFieldsNames[$TableName] = array();
            unset($UpdatableFieldsNames[$TableName]);
            unset($IndexesNames[$TableName]);
            continue;
        }
    }

    if( empty($UpdatableFieldsNames) )
    {
        throw new TRMDataSourceNoUpdatebleFieldsException( __METHOD__ );
    }
}

/**
 * обновляет записи в таблице БД данными из объекта-данных DataObject,
 * если записи еще нет в таблице, т.е. нет ID для текущей строки,
 * то добавляет ее, если при вставке встретится дубликат ключа или уникального поля,
 * то возникнет ошибка!!!
 *
 * @param TRMDataObjectInterface $DataObject - объект с данными
 * @param array $IndexesNames - массив с именами индексных полей, 
 * которые должны проверяться при поиске в БД для сравнения с текущим удаляемым объетом 
 * @param array $UpdatableFieldsNames - все поля, которые могут быть изменены у данного объект, 
 * так же используются для поиска объекта, если не заданы индексные поля $IndexesNames
 * @param array $CurrentKeyFlag - какие поля проверять - "PRI", "UNI", "*"
 * 
 * @return void
 */
protected function generateSQLUpdateQueryString(
        TRMDataObjectInterface $DataObject,
        array $IndexesNames,
        array $UpdatableFieldsNames,
        array $CurrentKeyFlag)
{
    if( !$DataObject->count() ) { return; }

    // с 2019-03-30 - работа с объектом, как с единичной записью, поэтому только 0-я строка используется!
    $RowNum = 0;
    // массив с данными объекта
    $Row = $DataObject->getDataArray()[$RowNum];
    foreach ($UpdatableFieldsNames as $TableName => $FieldsNames)
    {
        // если проверяемые данные для очередной таблицы должны быть в первичном ключе,
        // но они там отсутсвуют, значит это новая запись,
        // добавляем ее и переходим к следующей
        if( $CurrentKeyFlag[$TableName] == "PRI" && !$DataObject->presentDataIn($RowNum, $TableName, $IndexesNames[$TableName] ) )
        {
            // в функцию добавления
            // передаем сами данные, номер строки в объекте данных из которой вставляются данные,
            // и где потом должны быть обновлены автоинкрементные поля,
            // если такие обнаружатся,
            // а так же передаем массив с полями доступными для обновления ,
            // что бы не получать его заново рпсходуя ресурсы...
            // в этой реализации массив передается по ссылке!!!
            $CurrentInsertId = $this->insertRowToOneTable($TableName, $Row[$TableName], $FieldsNames);

// можно менять метод вставки и вызывать ON DUPLICATE KEY ... UPDATE
//            $CurrentInsertId = $this->insertODKURowToOneTable($TableName, $Row[$TableName], $FieldsNames);

            // если ID не вернулся, значит обновления авто-инкрементного поля в БД не произошло, 
            // переходим к другой таблице
            if( !$CurrentInsertId ) { continue; }

            /**
             * нужно обновить данные по Relation у объектов, 
             * которые ссылались на поле AUTO_INCREMENT только-что добавленной записи
             */
            $this->checkAndUpdateAutoIncrementFieldsAfterInsert( $DataObject, $TableName, $RowNum, $CurrentInsertId);

            //$this->addNewRowToAndSetLastId( $Row, $RowNum, $UpdatableFieldsNames );
            // после добавления, переходим к следующей записи в объекте данных
            continue;
        }
        else
        {
            // если данные есть в первичном или уникальном ключе
            // значит запись для этой таблицы нужно обновить
            $this->UpdateQueryString .= $this->makeUpdateRowQueryStrForOneTable( $TableName, $Row[$TableName], $FieldsNames, $IndexesNames[$TableName] );
        }
    }
}

/**
 * обновляет записи в таблице БД данными из коллекции объектов-данных $DataCollection,
 * если записи еще нет в таблице, т.е. нет ID для текущей строки, 
 * то добавляет ее, если при вставке встретится дубликат ключа или уникального поля,
 * то возникнет ошибка!!!
 *
 * @param TRMSafetyFields $SafetyFields - DataMapper, для которого формируется выборка из БД
 * @param TRMDataObjectsCollection $DataCollection - коллекция с объектами данных
 * 
 * @return boolean - если обновление прошло успешно, то вернет true, иначе - false
 */
public function update(TRMSafetyFields $SafetyFields, TRMDataObjectsCollection $DataCollection)
{
    $this->clearState();

    $IndexesNames = array();
    $UpdatableFieldsNames = array();
    $CurrentKeyFlag = array();

    try
    {
        $this->generateIndexesAndUpdatableFieldsNames($SafetyFields, $IndexesNames, $UpdatableFieldsNames, $CurrentKeyFlag);
    }
    catch (TRMDataSourceNoUpdatebleFieldsException $ex)
    {
        return false;
    }
    foreach( $DataCollection as $DataObject )
    {
        try
        {
            // функция добавляет строки UPDATE к $this->UpdateQueryString
            // в тоже время вставки INSERT выполняются мгновенно, 
            // как только встретится запись без ключевых полей, что бы отследить LastID
            $this->generateSQLUpdateQueryString($DataObject, $IndexesNames, $UpdatableFieldsNames, $CurrentKeyFlag);
        }
        catch(TRMDataSourceSQLInsertException $e)
        {
            $this->setStateCode(1);
            $this->addStateString( $e->getMessage() );
        }
    }

    if( $this->getStateCode() )
    {
        throw new TRMSqlQueryException("Не удалось добавить следующие записи: " . $this->getStateString() );
    }
    
\TRMEngine\Helpers\TRMLib::sp($this->UpdateQueryString);
    if( !empty($this->UpdateQueryString) )
    {
        // фактическое выполнение запроса UPDATE, 
        // в случае неудачи выбрасывается исключение!
//        $this->completeMultiQuery($this->UpdateQueryString);
        $this->UpdateQueryString = "";
    }
    
    return true;
}

/**
 * формирует SQL-запрос для обновления данных только в одной таблице
 * 
 * @param string $TableName
 * @param array $Row
 * @param array $FieldsNames
 * @param array $WhereFieldsNamesForTable
 * @return string
 */
private function makeUpdateRowQueryStrForOneTable( $TableName, array &$Row, array &$FieldsNames, array &$WhereFieldsNamesForTable )
{
    $UpdateQuery = "UPDATE `{$TableName}` SET ";
    foreach( $FieldsNames as $FieldName )
    {
        $UpdateQuery .= "`{$FieldName}` = '" . addcslashes( trim($Row[ $FieldName ], "'"), "'" ) . "',";
    }
    $UpdateQuery = rtrim($UpdateQuery, ",");

    // все вызовы этой функуии только из цикла по массиву с заполненными полями
//    if( !empty($WhereFieldsNamesForTable) )
    {
        $UpdateQuery .= " WHERE ";

        foreach( $WhereFieldsNamesForTable as $FieldName )
        {
            $UpdateQuery .= "`{$TableName}`.`{$FieldName}` = '" . addcslashes( trim($Row[ $FieldName ], "'"), "'" ) . "' AND ";
        }
        $UpdateQuery = rtrim($UpdateQuery, "AND ");
    }
    $UpdateQuery .= ";";
 
    return $UpdateQuery;
}

/**
 * добавляет данные в одну таблицу обычным INSERT INTO ...
 * 
 * @param string $TableName - имя таблицы, в которую вставлются данные
 * @param array $Row - одномерный ассоциативный массив-строка с данными = array( FieldName1 => data1, FieldName2 => data2, ... )
 * @param array $FieldsNames - одномерный массив с именами полей таблицы, в которые будут добавлены данные
 * @return int - insert_id (auto_increment)
 * @throws TRMDataSourceSQLInsertException
 */
private function insertRowToOneTable( $TableName, array &$Row, array &$FieldsNames )
{
    // собираем массив с именами полей в строчку,
    // обрамляя имя каждого поля апострофами `
    $FieldsNamesStr = "`" . implode("`,`", $FieldsNames) . "`";
    $InsertQuery = "INSERT INTO `{$TableName}` ({$FieldsNamesStr}) VALUES(";
    foreach( $FieldsNames as $FieldName )
    {
        $InsertQuery .= "'" . addcslashes( trim($Row[ $FieldName ], "'"), "'" ) . "',";
    }
    $InsertQuery = rtrim($InsertQuery, ",") . ");";

    // не можем вызвать completeMultiQuery, так как надо отслеживать insert_id для каждой таблицы!!!
    if( !$this->MySQLiObject->query($InsertQuery) )
    {
        throw new TRMDataSourceSQLInsertException( __METHOD__ . " [{$InsertQuery}] " . get_class($this) );
    }
    return $this->MySQLiObject->insert_id;
}

/**
 * добавляет данные в одну таблицу, 
 * используя метод вставки INSERT INTO ... ON DUPLICATE KEY UPDATE
 * 
 * @param string $TableName - имя таблицы, в которую вставлются данные
 * @param array $Row - одномерный ассоциативный массив-строка с данными = array( FieldName1 => data1, FieldName2 => data2, ... )
 * @param array $FieldsNames - одномерный массив с именами полей таблицы, в которые будут добавлены данные
 * @return int - insert_id (auto_increment)
 * @throws TRMDataSourceSQLInsertException
 */
private function insertODKURowToOneTable( $TableName, array &$Row, array &$FieldsNames )
{
    // собираем массив с именами полей в строчку,
    // обрамляя имя каждого поля апострофами `
    $FieldsNamesStr = "`" . implode("`,`", $FieldsNames) . "`";
    $InsertQuery = "INSERT INTO `{$TableName}` ({$FieldsNamesStr}) VALUES(";
    $ODKUStr = "ON DUPLICATE KEY UPDATE ";
    foreach( $FieldsNames as $FieldName )
    {
        $InsertQuery .= "'" . addcslashes( trim($Row[ $FieldName ], "'"), "'" ) . "',";
        $ODKUStr .= "`{$FieldName}` = VALUES(`{$FieldName}`),";
    }
    $InsertQuery = rtrim($InsertQuery, ",") . ")" . rtrim($ODKUStr, ",") . ";";

    // не можем вызвать completeMultiQuery, так как надо отслеживать insert_id для каждой таблицы!!!
    if( !$this->MySQLiObject->query($InsertQuery) )
    {
        throw new TRMDataSourceSQLInsertException( __METHOD__ . " [{$InsertQuery}] " . get_class($this) );
    }
    return $this->MySQLiObject->insert_id;
}

/**
 * добавляет новую запись в БД, 
 * в данной версии вызывается update(),
 * 
 * @param TRMSafetyFields $SafetyFields - DataMapper, для которого формируется выборка из БД
 * 
 * @return boolean - если обновление прошло успешно, то вернет true, иначе - false
 */
public function insert( TRMSafetyFields $SafetyFields, TRMDataObjectsCollection $DataCollection )
{
    return $this->update($SafetyFields, $DataCollection);
}


/**
 * проверяет связь только что обновленного поля AUTO_INCREMENT в $TableName
 * с другими таблицами, если на это поле кто-то ссылается, то обновляет значение на вновь установленное
 * 
 * @param TRMSafetyFields $SafetyFields - DataMapper, для которого формируется выборка из БД
 * @param TRMDataObjectInterface $DataObject - объект с данными
 * @param string $TableName - имя таблицы, где произошло обновление автоинкрементного поля
 * @param string $RowNum - номер строки с данными в DataObject
 * @param string $CurrentInsertId - полученное ID после выполенеия оператора INSERT в MySQL
 */
private function checkAndUpdateAutoIncrementFieldsAfterInsert( TRMSafetyFields $SafetyFields, TRMDataObjectInterface $DataObject, $TableName, $RowNum, $CurrentInsertId)
{
    // getAutoIncrementFieldsNamesFor возвращает массив с auto_increment полями для таблицы $TableName
    // при правильной схеме такое поле должно быть ОДНО !
    $AutoIncFieldsArray = $SafetyFields->getAutoIncrementFieldsNamesFor($TableName);
    // если в схеме Дата-маппера для данной таблицы не описаны поля auti_increment, 
    // завершаем выполнение
    if( empty($AutoIncFieldsArray) ) { return; }

    // если автоинкрементные поля найдены,
    // то теперь для каждого такого поля
    foreach($AutoIncFieldsArray as $AutoIncFieldName )
    {
        // обновляем данные в автоинкрементном поле для самого объекта 
        // добавленного в очередную таблицу $TableName
        $DataObject->setData($RowNum, $TableName, $AutoIncFieldName, $CurrentInsertId);
        // получаем массив ссылаюшихся (зависимых) полей по всем таблицам
        $BackRelationArray = $SafetyFields->getBackRelationFor($TableName, $AutoIncFieldName);
        if( empty($BackRelationArray) ) { continue; }

        // для всех объектов
        foreach( $BackRelationArray as $BackTableName => $BackFieldsNames )
        {
            // во все ссылающиеся поля 
            foreach( $BackFieldsNames as $BackFieldName )
            {
                // устанавливаем новые данные ссылающегося поля!!!
                // только если оно само не является автоинкрементным
                if( !$SafetyFields->isFieldAutoIncrement($BackTableName, $BackFieldName) )
                {
                    $DataObject->setData($RowNum, $BackTableName, $BackFieldName, $CurrentInsertId);
                }
            }
        }
    }
}

/**
 * удаляет записи коллекции из таблиц БД,
 * из основной таблицы удаляются записи, которые удовлетворяют значению сохраненного ID-поля,
 * если такого нет, то сравниваются на совпадение значения из всех полей 
 * (доступных для записи, которые имют флаг UPDATABLE_FIELD) и найденная запись удаляется,
 * так же удалются записи из дочерних таблиц, 
 * если у них стоит хотя бы одно поле доступное для редактирования - UPDATABLE_FIELD
 * 
 * @param TRMDataObjectInterface $DataObject - объект с данными
 * @param array $IndexesNames - массив с именами индексных полей, 
 * которые должны проверяться при поиске в БД для сравнения с текущим удаляемым объетом 
 * @param array $UpdatableFieldsNames - все поля, которые могут быть изменены у данного объект, 
 * так же используются для поиска объекта, если не заданы индексные поля $IndexesNames
 * @param string $DeleteFromStr - заранее сформированная строка со списком таблиц, из которых происходит удаление,
 * эта строка одинаковая для всех записей, 
 * @param string $UsingStr - строка для секции Delete-запроса USING 
 * `table1` as `table1`, `description` as `description` 
 * (без слова USING)
 * 
 * @return boolean - возвращает результат запроса DELETE
 */
protected function generateSQLDeleteQueryString(
        TRMDataObjectInterface $DataObject, 
        array &$IndexesNames, 
        array &$UpdatableFieldsNames,
        $DeleteFromStr,
        $UsingStr)
{
    if( !$DataObject->count() ) { return true; }

    // массив с данными объекта, 
    // с 2019-03-30 объект - это единичная зпрись, 
    // поэтому работаем только с 0-й строкой данных
    $Row = $DataObject->getDataArray()[0];

    $CurrentWhereString = "";
    // $UpdatableFieldsNames - нужен только для списка таблиц, в которых есть доступные для изменения данные,
    // т.е. которые можно удалять...
    foreach( array_keys($UpdatableFieldsNames) as $TableName )
    {
        // если $IndexesNames[$TableName] пустой, то нет полей для секции WHERE в DELETE-запросе
        // значит переходим к следующей таблице
        if( empty( $IndexesNames[$TableName] ) ) { continue; }

        // иначе в $IndexesNames[$TableName] перечислены поля для поиска в секции WHERE в DELETE-запросе,
        // а именно...
        // если индексные поля были не определены в DataMapper,
        // тогда в $IndexesNames[$TableName] попадут все поля для данной таблицы,
        // тогда удаляем все записи из БД, 
        // в которых значения полей в БД и в объекте данных совпадают!!!
        // за это отвечает 3-й элемент массива $Keys => * в функции generateIndexesAndUpdatableFieldsNames
        foreach( $IndexesNames[$TableName] as $FieldName )
        {
            $CurrentWhereString .= "`{$TableName}`.`{$FieldName}` = '" . addcslashes( trim( $Row[$TableName][ $FieldName ], "'" ), "'" ) . "' AND ";
        }
    }

    // если сформированы условия для поска удаляемого объекта в БД,
    // то добавляем очередную строку DELETE к запросу
    if( !empty($CurrentWhereString) )
    {
        $this->DeleteQueryString .= "DELETE FROM "
                . $DeleteFromStr
                . " USING "
                . $UsingStr
                ." WHERE "
                . rtrim($CurrentWhereString, "AND ")
                . ";";
    }
}

/**
 * удаляет записи коллекции из таблиц БД,
 * из основной таблицы удаляются записи, которые удовлетворяют значению сохраненного ID-поля,
 * если такого нет, то сравниваются на совпадение значения из всех полей 
 * (доступных для записи, которые имют флаг UPDATABLE_FIELD) и найденная запись удаляется,
 * так же удалются записи из дочерних таблиц, 
 * если у них стоит хотя бы одно поле доступное для редактирования - UPDATABLE_FIELD
 * 
 * @param TRMSafetyFields $SafetyFields - DataMapper, для которого формируется выборка из БД
 * @param TRMDataObjectsCollection $DataCollection - коллекция с объектами данных
 * @return boolean - возвращает результат запроса DELETE
 */
public function delete(TRMSafetyFields $SafetyFields, TRMDataObjectsCollection $DataCollection)
{
    $IndexesNames = array();
    $UpdatableFieldsNames = array();

    // проверяем сначала на первичный ключ,
    // затем на уникальные ключи для идентификации записи для удаления,
    // и если не найдены, тогда используем для сравнения все поля записи , 
    // что бы по ним по все идентифицировать запись
    $this->generateIndexesAndUpdatableFieldsNames($SafetyFields, $IndexesNames, $UpdatableFieldsNames);

    $DeleteFromStr = "`" . implode("`,`", array_keys($UpdatableFieldsNames) ) . "`";
    $UsingStr = "";
    foreach( array_keys($UpdatableFieldsNames) as $TableName )
    {
        $UsingStr .= "`$TableName` AS `$TableName`,";
    }
    $UsingStr = rtrim($UsingStr, ",");

    foreach( $DataCollection as $DataObject )
    {
        $this->generateSQLDeleteQueryString($DataObject, $IndexesNames, $UpdatableFieldsNames, $DeleteFromStr, $UsingStr);
    }
\TRMEngine\Helpers\TRMLib::sp($this->DeleteQueryString);
    if( !empty($this->DeleteQueryString) )
    {
//        $this->completeMultiQuery($this->DeleteQueryString);
        $this->DeleteQueryString = "";
    }
    return true;
}

/**
 * выполняет запрос из нескольких (или одного) SQL-выражений
 * и завершает выполнение, очищает буфер для возможности выполнения следующих запросов, 
 * перебирает все результаты
 * 
 * @param string $querystring - строка SQL-запроса
 * @throws TRMSqlQueryException - в случае неудачного запроса выбрасывается исключение!
 */
private function completeMultiQuery($querystring)
{
    if( !$this->MySQLiObject->multi_query($querystring) )
    {
        throw new TRMSqlQueryException( __METHOD__ . " Запрос выполнить не удалось [{$querystring}] - Ошибка #(" . $this->MySQLiObject->sqlstate . "): " . $this->MySQLiObject->error );
    }
    if( $this->MySQLiObject->insert_id ) { $this->LastId = $this->MySQLiObject->insert_id; }

    // очистка после multi_query($query), иначе следующие запросы не сработают
    while($this->MySQLiObject->more_results())
    {
        $this->MySQLiObject->next_result();
    }
}


} // TRMSqlDataSource