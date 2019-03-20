<?php

namespace TRMEngine\DataSource;

use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataMapper\TRMSafetyFields;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataSource\Exceptions\TRMDataSourceNoUpdatebleFieldsException;
use TRMEngine\DataSource\Exceptions\TRMDataSourceSQLEmptyTablesListException;
use TRMEngine\DataSource\Exceptions\TRMDataSourceSQLInsertException;
use TRMEngine\DataSource\Exceptions\TRMDataSourceSQLNoSafetyFieldsException;
use TRMEngine\DataSource\Exceptions\TRMDataSourceWrongTableSortException;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\DataSource\TRMSqlDataSource;
use TRMEngine\Exceptions\TRMSqlQueryException;
use TRMEngine\Helpers\TRMLib;
use TRMEngine\TRMDBObject;

/**
 * абстрактный класс,
 * общий для всех классов обработки записей из таблиц БД,
 * принимает в качестве зависимости объет DataMapper,
 * для работы с БД использует статический объект TRMDBObject,
 * который работает через MySQLi
 */
abstract class TRMSqlDataSource implements TRMDataSourceInterface
{
/**
 * константы для индексов массива параметров дочерних таблиц
 */
//const DATASOURCE_MAIN_FIELD_NAME_INDEX = "MainFieldName";
//const DATASOURCE_CHILD_FIELD_NAME_INDEX = "ChildFieldName";
//const DATASOURCE_OPERATOR_INDEX = "Operator";
//const DATASOURCE_ALIAS_NAME_INDEX = "AliasName";
//const DATASOURCE_JOIN_INDEX = "Join";

/**
 * если не указан тип Join, то принимается это значение = "LEFT"
 */
const DATASOURCE_JOIN_DEFAULT = "LEFT";

/** константа показывающая, что нужно брать имена полей в кавычки */
const NEED_QUOTE = 32000;
/** константа показывающая, что брать имена полей в кавычки НЕ нужно */
const NOQUOTE = 32001;

/**
 * @var string - SQL-запрос сохраненный в виде строки
 */
public $QueryString = "";

/**
 * @var TRMDataObjectInterface - ссылка на объект данных, содержащий массив значений для всех полей данной записи, могут быть собраны из нескольких таблиц
 */
public $DataObject;

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
 * @var TRMSafetyFields - объект полей для каждой таблицы в запросе,
 * указана возможность чтения/записи поля, его тип, индекс и т.д.
 */
public $SafetyFields;
/**
 * @var mysqli - объект MySQLi для работы с БД MySQL
 */
protected $MySQLiObject;


/**
 * 
 * @param TRMSafetyFields $SafetyFields - DataMapprt для объекта, с которым будет работать этот DataSource
 */
public function __construct(TRMSafetyFields $SafetyFields) //$MainTableName, array $MainIndexFields, array $SecondTablesArray = null, $MainAlias = null )
{
    $this->setSafetyFields($SafetyFields);
    $this->MySQLiObject = TRMDBObject::$newlink; // TRMDIContainer::getStatic("TRMDBObject")->$newlink;
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
 * @return TRMSafetyFields - объект DataMapper для текущего набора данных
 */
function getSafetyFields()
{
    return $this->SafetyFields;
}
/**
 * @param TRMSafetyFields $SafetyFields - объект DataMapper для текущего набора данных
 */
function setSafetyFields(TRMSafetyFields $SafetyFields)
{
    $this->SafetyFields = $SafetyFields;
}

/**
 * устанавливает связь с объектом данных,
 * объект данных должен реализовывать интерфейс TRMDataObjectInterface
 * 
 * @param TRMDataObjectInterface $data - объект, данные которого будут получены и/или сохранены/удалены в БД
 */
public function linkData( TRMDataObjectInterface $data )
{
    $this->DataObject = $data;
}

/**
 * очистка параметров WHERE запроса и строки текущего запроса
 */
public function clear()
{
    $this->QueryString = "";
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
 * @return string - строка со списком полей
 * 
 * @throws TRMDataSourceSQLNoSafetyFieldsException
 */
private function generateFieldsString()
{
    if( !$this->SafetyFields || !$this->SafetyFields->count() )
    {
        throw new TRMDataSourceSQLNoSafetyFieldsException(__METHOD__ );
    }
    $fieldstr = "";
    foreach( $this->SafetyFields as $TableName => $TableState )
    {        
        $TableAlias = $this->SafetyFields->getAliasForTableName($TableName);
        $tn = empty($TableAlias) ? $TableName : $TableAlias;
        foreach( $TableState["Fields"] as $fieldname => $state )
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
 * @return string - строка с JOIN-частью запроса
 */
private function generateJoinString()
{
    $JoinedTables = array();
    foreach( $this->SafetyFields as $CurrentTableName => $CurrentTableState )
    {
        foreach ( $CurrentTableState["Fields"] as $CurrentFieldName => $CurrentFieldState )
        {
            // если есть Relation, занчит таблица из Relation должна быть присоединена по полю из Relation
            if( isset($CurrentFieldState[TRMDataMapper::RELATION_INDEX]) )
            {
                $JoinedTables
                    [ $CurrentFieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::OBJECT_NAME_INDEX] ]
                        ["Fields"]
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

        $joinstr .= self::DATASOURCE_JOIN_DEFAULT . " JOIN `" . $TableName . "`";
        // если задан псевдоним таблицы, то добавляем его
        if( !empty($TableState["ObjectAlias"])  ) { $joinstr .= $TableState["ObjectAlias"]; }
        $joinstr .= " ON ";
        
        foreach( $TableState["Fields"] as $FieldName => $FieldRelation )
        {
            $joinstr .= "`" . $FieldRelation[TRMDataMapper::OBJECT_NAME_INDEX] . "`.`" .  $FieldRelation[TRMDataMapper::FIELD_NAME_INDEX] . "`";
            // оператор сравнивает родительский элемент, котороый изначально ссылался через Relation в $this->SafetyFields
            // он идет первым!
            // с текущим элементом присоединяемой таблицы, он идет вторым дальше...
            $joinstr .= $FieldRelation["Operator"];
            // если для присоединяемой таблицы задан альяс, то будем испльзовать его в строке JOIN,
            // если не задан, то имя таблицы
            $joinstr .= !empty($TableState["ObjectAlias"]) ? $TableState["ObjectAlias"] : ("`" . $TableName . "`");
            $joinstr .= ".`{$FieldName}`";
            $joinstr .= " AND ";
        }
        $joinstr = rtrim($joinstr, "AND ");
    }

    return $joinstr;
}

/**
 * формирует часть запроса связанную с условиями WHERE
 *
 * @return string - строка с WHERE-частью запроса
 */
private function generateWhereString()
{
    $wherestr = "";
    foreach( $this->Params as $param )
    {
        $key = $param["key"];
        if( isset($param["alias"]) && strlen($param["alias"])>0 ) { $key = $param["alias"] . "." . $key; }
        if( isset($param["quote"]) && $param["quote"] == TRMSqlDataSource::NEED_QUOTE ) { $key = $this->prepareKey($key); }

        $wherestr .= $param["andor"] . " " . $key . " " . $param["operator"];

        if( $param["operator"] == "IN" || $param["operator"] == "NOT IN" ) { $wherestr .= " (" . $param["value"] . ") "; }
        else if( $param["operator"] == "IS" || 
                 $param["operator"] == "NOT" || 
                 (isset($param["dataquote"]) && $param["dataquote"] == TRMSqlDataSource::NOQUOTE)
                ) { $wherestr .= " " . $param["value"] . " "; }
        else { $wherestr .= "'" . trim($param["value"], "'") . "' "; }
    }

    return ltrim(trim($wherestr), "ANDOR"); // $wherestr;
}

/**
 * формирует список таблиц и их псевдонимов для секции FROM SELECT-запроса
 * имена таблиц и их псевдонимы берутся из SafetyFields
 * 
 * @return string - строка вида "`table1` AS `t1`, `table2` AS `ttt`"
 */
private function generateFromStr()
{
    $MainTables = $this->SafetyFields->getObjectsNamesWithoutBackRelations();
    $fromstr="";
    foreach($MainTables as $TableName)
    {
        $fromstr .= "`{$TableName}`";
        $alias = $this->SafetyFields->getAliasForTableName($TableName);
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
 * параметры WHERE устанавливаются перед вызовом этой функции с помощью - addWhereParam или addWhereParamFromArray
 *
 * @return string
 * 
 * @throws TRMDataSourceSQLEmptyTablesListException
 */
public function makeSelectQuery() // array $params = null, $limit = 1, $offset = null)
{
    // строка с перечислением полей для выборки 
    $fieldstr = $this->generateFieldsString();

    $fromstr = $this->generateFromStr();
    
    if(empty($fromstr))
    {
        throw new TRMDataSourceSQLEmptyTablesListException( __METHOD__ );
    }
    
    $this->QueryString = "SELECT " . $fieldstr . " FROM " . $fromstr;
    
    // строка с секцией JOIN, присоединяются все таблицы из массива Tables
    $joinstr = $this->generateJoinString();
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
public function addWhereParam($fieldname, $data, $operator = "=", $andor = "AND", $quote = TRMSqlDataSource::NEED_QUOTE, $alias = null, $dataquote = TRMSqlDataSource::NEED_QUOTE)
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
    return $this->addWhereParamFromArray( $value );
}

/**
 * добавляет условие в секцию WHERE-запроса
 * 
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
public function addWhereParamFromArray(array $params)
{
    $value = array();

    $value["key"] = $params["key"];
    $value["value"] = $params["value"];
    $value["operator"] = "=";
    $value["andor"] = "AND";
    $value["quote"] = TRMSqlDataSource::NEED_QUOTE;
    $value["alias"] = isset( $params["alias"] ) ? $params["alias"] : null; // $this->AliasName;
    $value["dataquote"] = TRMSqlDataSource::NEED_QUOTE;

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

    $this->Params[] = $value;

    return $this;
}

/**
 * добавляет массив параметров к уже установленному
 *
 * @param array - параметры, используемые в запросе, как правило сюда передается ID-записи 
 * все должно передаваться в массиве array( $fieldname => array(value, operator, andor, quote, alias, dataquote), ...)
 * обязательными являются array(..., $fieldname => array(value), ...)
 */
public function generateParamsFrom(array $params = null)
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
        $this->addWhereParam($key, 
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
 * @return mysqli_result - объект-результат выполнения запроса
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
 * @return mysqli_result - объект-результат выполнения запроса
 * @throws TRMSqlQueryException - в случае неудачного выполнения запроса выбрасывается исключение
 */
private function runSelectQuery()
{
    if( !$this->makeSelectQuery() ) // $params, $this->Count, $this->StartPosition) )
    {
        throw new TRMSqlQueryException( __METHOD__ . " Неудачно сформирован запрос к БД" );
    }

    return $this->executeQuery($this->QueryString);
}
 
/**
 * считываем данные из БД используя запрос, который возвращает функция makeSelectQuery
 * перезаписывает связанный объект с данными
 *
 * @return int - количество прочитанных строк из БД
 * @throws TRMSqlQueryException - в случае неудачного выполнения запроса выбрасывается исключение
 */
public function getDataFrom()
{
    $result = $this->runSelectQuery();
    if( !$result )
    {
        throw new TRMSqlQueryException( __METHOD__ . " Запрос к БД вернул ошибку![{$this->QueryString}]" );
    }
    $this->DataObject->setDataArray( TRMDBObject::fetchAll($result) ); //  $result->fetch_all(MYSQLI_ASSOC) );
    return $result->num_rows;
}

/**
 * считываем данные из БД используя запрос, который возвращает функция makeSelectQuery
 * добавляет полученное множество к имеющимся данным
 *
 * @return int - количество прочитанных строк из БД
 * @throws TRMSqlQueryException - в случае неудачного выполнения запроса выбрасывается исключение
 */
public function addDataFrom() // array $params = null)
{
    $result = $this->runSelectQuery(); //$params);
    if( !$result )
    {
        throw new TRMSqlQueryException( __METHOD__ . " Запрос к БД вернул ошибку![{$this->QueryString}]" );
    }
    $this->DataObject->mergeDataArray( TRMDBObject::fetchAll($result) ); //$result->fetch_all(MYSQLI_ASSOC) );
    return $result->num_rows;
}

/**
 *  
 * @param array $IndexesNames
 * @param array $UpdatableFieldsNames
 * @param array $CurrentKeyFlag
 * 
 * @throws TRMDataSourceWrongTableSortException
 * @throws TRMDataSourceNoUpdatebleFieldsException
 */
private function generateIndexesAndUpdatableFieldsNames( array &$IndexesNames, array &$UpdatableFieldsNames, array &$CurrentKeyFlag = null )
{
    if(!$this->SafetyFields->sortObjectsForRelationOrder())
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
    
    foreach( $this->SafetyFields as $TableName => $TableState )
    {
        
        // получаем массив доступных для записи полей в очередной таблице $TableName
        $UpdatableFieldsNames[$TableName] = $this->SafetyFields->getUpdatableFieldsNamesFor($TableName);
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
            $IndexesNames[$TableName] = $this->SafetyFields->getIndexFieldsNames($TableName, $Key);
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
 * если записи еще нет в таблице, т.е. нет ID для текущей строки, то добавляет ее,
 * в данной версии использует INSERT ... ON DUPLICATE KEY UPDATE ...
 *
 * @return boolean - если обновление прошло успешно, то вернет true, иначе - false
 */
public function update()
{
    if( !$this->DataObject->count() ) { return true; }
    // массив с неудачно добавленными строками!!!
    $ErrorRows = array();

    $IndexesNames = array();
    $UpdatableFieldsNames = array();
    $CurrentKeyFlag = array();

    try
    {
        $this->generateIndexesAndUpdatableFieldsNames($IndexesNames, $UpdatableFieldsNames, $CurrentKeyFlag);
    }
    catch (TRMDataSourceNoUpdatebleFieldsException $ex)
    {
        return false;
    }

    $MultiQueryStr = "";
    foreach( $this->DataObject as $RowNum => $Row )
    {
        foreach ($UpdatableFieldsNames as $TableName => $FieldsNames)
        {
            try
            {
                // если проверяемые данные для очередной таблицы должны быть в первичном ключе,
                // но они там отсутсвуют, значит это новая запись,
                // добавляем ее и переходим к следующей
                if( $CurrentKeyFlag[$TableName] == "PRI" && !$this->DataObject->presentDataIn($RowNum, $IndexesNames[$TableName] ) )
                {
                    // в функцию добавления
                    // передаем сами данные, номер строки в объекте данных из которой вставляются данные,
                    // и где потом должны быть обновлены автоинкрементные поля,
                    // если такие обнаружатся,
                    // а так же передаем массив с полями доступными для обновления ,
                    // что бы не получать его заново рпсходуя ресурсы...
                    // в этой реализации массив передается по ссылке!!!
                    $CurrentInsertId = $this->insertRowToOneTable($TableName, $Row, $FieldsNames);
                    // если ID не вернулся, значит обновления авто-инкрементного поля в БД не произошло, переходим к другой таблице
                    if( !$CurrentInsertId ) { continue; }

                    /**
                     * нужно обновить данные по Relation у объектов, 
                     * которые ссылались на поле AUTO_INCREMENT только-что добавленной записи
                     */
                    $this->checkAutoIncrementFieldUpdate($TableName, $RowNum, $CurrentInsertId);

                    //$this->addNewRowToAndSetLastId( $Row, $RowNum, $UpdatableFieldsNames );
                    // после добавления, переходим к следующей записи в объекте данных
                    continue;
                }
                else
                {
                    // если данные есть в первичном или уникальном ключе
                    // значит запись для этой таблицы нужно обновить
                    $MultiQueryStr .= $this->makeUpdateRowQueryStrForOneTable( $TableName, $Row, $FieldsNames, $IndexesNames[$TableName] );
                }
            }
            catch( TRMSqlQueryException $e )
            {
                $ErrorRows[$RowNum] = $e->getMessage();
            }
        }
    }

    if(!empty($ErrorRows))
    {
        TRMLib::sp(__METHOD__ . " Часть записей не удалось обновить в БД ");
        TRMLib::ap($ErrorRows);
    }

    // в случае неудачи выбрасывается исключение!
    if( !empty($MultiQueryStr) ) { $this->completeMultiQuery($MultiQueryStr); }
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
        $UpdateQuery .= "`{$FieldName}` = '" . addcslashes( $Row[ $FieldName ], "'" ) . "',";
    }
    $UpdateQuery = rtrim($UpdateQuery, ",");

    // все вызовы этой функуии только из цикла по массиву с заполненными полями
//    if( !empty($WhereFieldsNamesForTable) )
    {
        $UpdateQuery .= " WHERE ";

        foreach( $WhereFieldsNamesForTable as $FieldName )
        {
            $UpdateQuery .= "`{$TableName}`.`{$FieldName}` = '" . addcslashes( $Row[ $FieldName ], "'" ) . "' AND ";
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
        $InsertQuery .= "'" . addcslashes( $Row[ $FieldName ], "'" ) . "',";
    }
    $InsertQuery = rtrim($InsertQuery, ",") . ");";

    // не можем вызвать completeMultiQuery, так как надо отслеживать insert_id для каждой таблицы!!!
    if( !$this->MySQLiObject->query($InsertQuery) )
    {
        throw new TRMDataSourceSQLInsertException( __METHOD__ . " [{$InsertQuery}] " );
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
        $InsertQuery .= "'" . addcslashes( $Row[ $FieldName ], "'" ) . "',";
        $ODKUStr .= "`{$FieldName}` = VALUES(`{$FieldName}`),";

    }
    $InsertQuery = rtrim($InsertQuery, ",") . ")" . rtrim($ODKUStr, ",") . ";";

    // не можем вызвать completeMultiQuery, так как надо отслеживать insert_id для каждой таблицы!!!
    if( !$this->MySQLiObject->query($InsertQuery) )
    {
        throw new TRMDataSourceSQLInsertException( __METHOD__ . " не удалось добавить запись: [{$InsertQuery}]" );
    }
    return $this->MySQLiObject->insert_id;
}

/**
 * добавляет новую запись в БД, 
 * в данной версии вызывает insertODKU(), 
 * т.е. реализует метод вставки INSERT INTO ... ON DUPLICATE KEY UPDATE
 *
 * @return boolean - результат работы update(), в случае успеха - true, иначе - false
 */
public function insert()
{
    return $this->insertODKU();
}

/**
 * добавляет новую запись в БД, 
 * в случае дублирования ключей, обновляет зпись
 * INSERT ... ON DUPLICATE KEY UPDATE
 *
 * @return boolean - результат работы update(), в случае успеха - true, иначе - false
 */
public function insertODKU()
{
    if( !$this->DataObject->count() ) { return true; }
    // массив с неудачно добавленными строками!!!
    $ErrorRows = array();

    $IndexesNames = array();
    $UpdatableFieldsNames = array();
    $CurrentKeyFlag = array();

    try
    {
        $this->generateIndexesAndUpdatableFieldsNames($IndexesNames, $UpdatableFieldsNames, $CurrentKeyFlag);
    }
    catch (TRMDataSourceNoUpdatebleFieldsException $ex)
    {
        return false;
    }

    //$MultiQueryStr = "";
    // каждая запись добавляется отдельным SQL-запросом, что бы отследить AUTO_INCREMENT !!!
    foreach( $this->DataObject as $RowNum => $Row )
    {
        foreach ($UpdatableFieldsNames as $TableName => $FieldsNames)
        {
            try
            {
                // в функцию добавления
                // передаем сами данные, номер строки в объекте данных из которой вставляются данные,
                // и где потом должны быть обновлены автоинкрементные поля,
                // если такие обнаружатся,
                // а так же передаем массив с полями доступными для обновления ,
                // что бы не получать его заново расходуя ресурсы...
                // в этой реализации массив передается по ссылке!!!
                $CurrentInsertId = $this->insertODKURowToOneTable($TableName, $Row, $FieldsNames);
                // если ID не вернулся, значит обновления авто-инкрементного поля в БД не произошло, переходим к другой таблице
                if( !$CurrentInsertId ) { continue; }

                /**
                 * нужно обновить данные по Relation у объектов, 
                 * которые ссылались на поле AUTO_INCREMENT только-что добавленной записи
                 */
                $this->checkAutoIncrementFieldUpdate($TableName, $RowNum, $CurrentInsertId);

                //$this->addNewRowToAndSetLastId( $Row, $RowNum, $UpdatableFieldsNames );
                // после добавления, переходим к следующей записи в объекте данных
                continue;
            }
            catch( TRMSqlQueryException $e )
            {
                $ErrorRows[$RowNum] = $e->getMessage();
            }
            //$MultiQueryStr .= $this->makeUpdateRowQueryStr( $Row, $UpdatableFieldsNames, $IndexesNames );
        }
    }

    if(!empty($ErrorRows))
    {
        TRMLib::sp(__METHOD__ . " Часть записей не удалось обновить в БД ");
        TRMLib::ap($ErrorRows);
    }

    // в случае неудачи выбрасывается исключение!
    //if( !empty($MultiQueryStr) ) { $this->completeMultiQuery($MultiQueryStr); }
    return true;
}

/**
 * проверяет связь только что обновленного поля AUTO_INCREMENT в $TableName
 * с другими таблицами, если на это поле кто-то ссылается, то обновляет значение на вновь установленное
 * 
 * @param type $TableName - имя таблицы, где произошло обновление автоинкрементного поля
 * @param type $RowNum - номер строки с данными в DataObject
 * @param type $CurrentInsertId - полученное ID после выполенеия оператора INSERT в MySQL
 */
private function checkAutoIncrementFieldUpdate( $TableName, $RowNum, $CurrentInsertId)
{
    // getAutoIncrementFieldsNamesFor возвращает массив с auto_increment полями для таблицы $TableName
    // при правильной схеме такое поле должно быть ОДНО !
    $AutoIncFieldsArray = $this->SafetyFields->getAutoIncrementFieldsNamesFor($TableName);
    // если в схеме Дата-маппера для данной таблицы не описаны поля auti_increment, 
    // завершаем выполнение
    if( empty($AutoIncFieldsArray) ) { return; }

    // если автоинкрементные поля найдены,
    // то теперь для каждого такого поля
    foreach($AutoIncFieldsArray as $AutoIncFieldName )
    {
        // обновляем данные в автоинкрементном поле для самого объекта 
        // добавленного в очередную таблицу $TableName
        $this->DataObject->setData($RowNum, $AutoIncFieldName, $CurrentInsertId);
        // получаем массив ссылаюшихся (зависимых) полей по всем таблицам
        $BackRelationArray = $this->SafetyFields->getBackRelationFor($TableName, $AutoIncFieldName);
        if( empty($BackRelationArray) ) { continue; }

        // для всех объектов
        foreach( $BackRelationArray as $BackTableName => $BackFieldsNames )
        {
            // во все ссылающиеся поля 
            foreach( $BackFieldsNames as $BackFieldName )
            {
                // устанавливаем новые данные ссылающегося поля!!!
                // только если оно само не является автоинкрементным
                if( !$this->SafetyFields->isFieldAutoIncrement($BackTableName, $BackFieldName) )
                {
                    $this->DataObject->setData($RowNum, $BackFieldName, $CurrentInsertId);
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
 * @return boolean - возвращает результат запроса DELETE
 */
public function delete()
{
    if( !$this->DataObject->count() ) { return true; }

    $IndexesNames = array();
    $UpdatableFieldsNames = array();

    // проверяем сначала на первичный ключ. затем на уникальные ключи ля идентификации записи для удаления,
    // и если не найдены, тогда используем для сравнения все поля записи , что бы по ним по все идентифицировать запись
    $this->generateIndexesAndUpdatableFieldsNames($IndexesNames, $UpdatableFieldsNames);

    $DeleteFromStr = "`" . implode("`,`", array_keys($UpdatableFieldsNames) ) . "`";
    $UsingStr = "";
    foreach( array_keys($UpdatableFieldsNames) as $TableName )
    {
        $UsingStr .= "`$TableName` AS `$TableName`,";
    }
    $UsingStr = rtrim($UsingStr, ",");

    $MultiQueryStr = "";
    // проходим по всем строкам из объекта данных
    foreach ($this->DataObject as $Row)
    {
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
                $CurrentWhereString .= "`{$TableName}`.`{$FieldName}` = '" . addcslashes( $Row[ $FieldName ], "'" ) . "' AND ";
            }
        }

        if( !empty($CurrentWhereString) )
        {
            $MultiQueryStr .= "DELETE FROM "
                    . $DeleteFromStr
                    . " USING "
                    . $UsingStr
                    ." WHERE "
                    . rtrim($CurrentWhereString, "AND ")
                    . ";";
        }

    }

    if( !empty($MultiQueryStr) ) { $this->completeMultiQuery($MultiQueryStr); }
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