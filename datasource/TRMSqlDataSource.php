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
use TRMEngine\Helpers\TRMLib;
use TRMEngine\TRMDBObject;

/**
 * ����������� �����,
 * ����� ��� ���� ������� ��������� ������� �� ������ ��,
 * ��������� � �������� ����������� ����� DataMapper,
 * ��� ������ � �� ���������� ����������� ������ TRMDBObject,
 * ������� �������� ����� MySQLi
 */
class TRMSqlDataSource implements TRMDataSourceInterface
{
/**
 * ��������� ��� �������� ������� ���������� �������� ������
 */
//const DATASOURCE_MAIN_FIELD_NAME_INDEX = "MainFieldName";
//const DATASOURCE_CHILD_FIELD_NAME_INDEX = "ChildFieldName";
//const DATASOURCE_OPERATOR_INDEX = "Operator";
//const DATASOURCE_ALIAS_NAME_INDEX = "AliasName";
//const DATASOURCE_JOIN_INDEX = "Join";

/**
 * ���� �� ������ ��� Join, �� ����������� ��� �������� = "LEFT"
 */
const DATASOURCE_JOIN_DEFAULT = "LEFT";

/** ��������� ������������, ��� ����� ����� ����� ����� � ������� */
const NEED_QUOTE = 32000;
/** ��������� ������������, ��� ����� ����� ����� � ������� �� ����� */
const NOQUOTE = 32001;

/**
 * @var string - ������� SQL-������ ��� ��������� ������� �� ��,
 * ���� ������ �� ������, ������ ������ ��� �� ��������!
 * ����� �������� ���������� ������� ������� ������ ������������!
 */
protected $QueryString = "";
/**
 * @var string - ������� ������ SQL-������� ��� ������� � ���������� ������� � ��,
 * ���� ������ �� ������, ������ ������ ��� �� ��������!
 * ����� �������� ���������� ������� ������� ������ ������������!
 */
protected $UpdateQueryString = "";
/**
 * @var string - ������� ������ SQL-������� ��� �������� ������� �� ��,
 * ���� ������ �� ������, ������ ������ ��� �� ��������!
 * ����� �������� ���������� ������� ������� ������ ������������!
 */
protected $DeleteQueryString = "";

/**
 * @var TRMDataObjectInterface - ������ �� ������ ������, ���������� ������ �������� ��� ���� ����� ������ ������, ����� ���� ������� �� ���������� ������
 */
protected $DataObject;

/**
 * @var array - ������ ����� � �������� $Params[FieldName] = array( FieldValue, Operator, AndOr... ),
 * �������� ������� ����� �������������� ��� ������� SELECT � ������ WHERE
 */
protected $Params = array();
/**
 * @var int - ��������� ������� ��� ������� - OFFSET, ����� �����������, ��������, ��� ���������
 */
protected $StartPosition = null;
/**
 * @var int - ���������� ������� ��� ������� - LIMIT, ����� �����������, ��������, ��� ���������
 */
protected $Count = null;
/**
 * @var array - ������ ����� ��� ���������� - array( fieldname1 => "ASC | DESC", ... ) ��� ORDER BY
 */
protected $OrderFields = array();
/**
 * @var array - ������ ����� ��� ����������� - array( fieldname1 => "" ) ��� GROUP BY
 */
protected $GroupFields = array();
/**
 * @var TRMSafetyFields - ������ ����� ��� ������ ������� � �������,
 * ������� ����������� ������/������ ����, ��� ���, ������ � �.�.
 */
protected $SafetyFields;
/**
 * @var \mysqli - ������ MySQLi ��� ������ � �� MySQL
 */
protected $MySQLiObject;


/**
 * 
 * @param TRMSafetyFields $SafetyFields - DataMapprt ��� �������, � ������� ����� �������� ���� DataSource
 */
public function __construct(TRMSafetyFields $SafetyFields) //$MainTableName, array $MainIndexFields, array $SecondTablesArray = null, $MainAlias = null )
{
    $this->setSafetyFields($SafetyFields);
    $this->MySQLiObject = TRMDBObject::$newlink; // TRMDIContainer::getStatic("TRMDBObject")->$newlink;
}

/**
 * ������������� � ����� ������ �������� ������� - StartPosition
 * � ����� ���������� ������� �������� - Count
 *
 * @param int - � ����� ������ �������� �������
 * @param int - ����� ���������� ������� ��������
 */
public function setLimit( $Count , $StartPosition = null )
{
    $this->StartPosition = $StartPosition;
    $this->Count = $Count;
}

/**
 * ������ ������ ���������� �� �����, ������ �������� ���������
 *
 * @param array - ������ �����, �� ������� ����������� - array( fieldname1 => "ASC | DESC", ... )
 */
public function setOrder( array $orderfields )
{
    $this->OrderFields = array();

    $this->addOrder( $orderfields );
}

/**
 * ������������� ���� ��� ����������
 *
 * @param string $orderfieldname - ��� ���� , �� �������� ��������������� ����������
 * @param int $asc - 1 - ����������� �� ����� ���� ��� ASC, � ��������� ������, ��� DESC
 */
public function setOrderField( $orderfieldname, $asc = 1 )
{
    $this->OrderFields[$orderfieldname] = ( ($asc == 1) ? "ASC" : "DESC");
}

/**
 * ��������� ���� � ������ ����������, ���� ��� ����, �� ������ �������� ����������������
 *
 * @param array - ������ �����, �� ������� ����������� - array( fieldname1 => "ASC | DESC", ... )
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
 * ��������, ��������� � ���������� ������ SQL-��������
 * 
 * @param string $operator - �������� ��� ��������
 * @param string $default - �������� �����������, ���� $operator �� �������
 * @return string - �������� SQL ��������
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
 * ��������, ��������� � ���������� ������ ������� ��������� JOIN ��� SQL-��������
 * 
 * @param string $join - ��������-��������� ��� JOIN, ������� ����� ���������, �������� ��������� LEGT, RIGHT, INNER, OUTER
 * @param string $default - ���� �������� �� �������, �� ������������� �������� $default, ����������� ����������� � LEGT
 * 
 * @return string - �������� ��������-��������� ��� JOIN � SQL-�������
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
 * @return TRMSafetyFields - ������ DataMapper ��� �������� ������ ������
 */
function getSafetyFields()
{
    return $this->SafetyFields;
}
/**
 * @param TRMSafetyFields $SafetyFields - ������ DataMapper ��� �������� ������ ������
 */
function setSafetyFields(TRMSafetyFields $SafetyFields)
{
    $this->SafetyFields = $SafetyFields;
}

/**
 * ������� ���������� WHERE ������� � ����� �������� SELECT, UPDATE/INSERT, DELETE
 */
public function clear()
{
    $this->QueryString = "";
    $this->UpdateQueryString = "";
    $this->DeleteQueryString = "";
    $this->clearParams();
}

/**
 * ������� ���������� ��� WHERE-������� � SQL-�������
 */
public function clearParams()
{
    $this->Params = array();
}

/**
 * ��������� ����� ������� �� ������� �����, ������� ���������� �� ������
 *
 * @return string - ������ �� ������� �����
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
 * ��������� ����� ������� ��������� � JOIN ������
 *
 * @return string - ������ � JOIN-������ �������
 */
private function generateJoinString()
{
    $JoinedTables = array();
    foreach( $this->SafetyFields as $CurrentTableName => $CurrentTableState )
    {
        foreach ( $CurrentTableState[TRMDataMapper::FIELDS_INDEX] as $CurrentFieldName => $CurrentFieldState )
        {
            // ���� ���� Relation, ������ ������� �� Relation ������ ���� ������������ �� ���� �� Relation
            if( isset($CurrentFieldState[TRMDataMapper::RELATION_INDEX]) )
            {
                $JoinedTables
                    [ $CurrentFieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::OBJECT_NAME_INDEX] ]
                        [TRMDataMapper::FIELDS_INDEX]
                            [ $CurrentFieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::FIELD_NAME_INDEX] ]
                        = array(
                            // ���� ��� ������� ������� ����� �����, �� ����� ����������� ��� � ������ JOIN,
                            // ���� �� �����, �� ��� �������
                            TRMDataMapper::OBJECT_NAME_INDEX => isset($CurrentTableState["ObjectAlias"]) ? $CurrentTableState["ObjectAlias"] : $CurrentTableName,
                            TRMDataMapper::FIELD_NAME_INDEX => $CurrentFieldName,
                            // �������� ����������� � ������������ $CurrentFieldName ���� �� ��������� ������������ TRMDataMapper::FIELD_NAME_INDEX...
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

    // ���� ������� ����� �� �������, ������ ������ JOIN �����!!!
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
 * @param string $TableName - ��� �������
 * @param array $TableState - ������ ��������� (��������� � ������� �����, ����� ��� �������, 
 * ��������, ��������� �� ����� �����������  JOIN (LEFT, RIGHT, INNER...) 
 * 
 * @return string - ������ � ������ JOIN-������� ��� ������� $TableName
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
    // ���� ����� ��������� �������, �� ��������� ���
    if( !empty($TableState["ObjectAlias"])  ) { $joinstr .= $TableState["ObjectAlias"]; }
    $joinstr .= " ON ";

    foreach( $TableState[TRMDataMapper::FIELDS_INDEX] as $FieldName => $FieldRelation )
    {
        $joinstr .= "`" . $FieldRelation[TRMDataMapper::OBJECT_NAME_INDEX] . "`.`" .  $FieldRelation[TRMDataMapper::FIELD_NAME_INDEX] . "`";
        // �������� ���������� ������������ �������, �������� ���������� �������� ����� Relation � $this->SafetyFields
        // �� ���� ������!
        // � ������� ��������� �������������� �������, �� ���� ������ ������...
        $joinstr .= $FieldRelation["Operator"];
        // ���� ��� �������������� ������� ����� �����, �� ����� ����������� ��� � ������ JOIN,
        // ���� �� �����, �� ��� �������
        $joinstr .= !empty($TableState["ObjectAlias"]) ? $TableState["ObjectAlias"] : ("`" . $TableName . "`");
        $joinstr .= ".`{$FieldName}`";
        $joinstr .= " AND ";
    }
    return rtrim($joinstr, "AND ");
}

/**
 * ��������� ����� ������� ��������� � ��������� WHERE
 *
 * @return string - ������ � WHERE-������ �������
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

            if( $param["operator"] == "IN" || $param["operator"] == "NOT IN" ) { $wherestr .= " (" . $param["value"] . ") "; }
            else if( $param["operator"] == "IS" || 
                     $param["operator"] == "NOT" || 
                     (isset($param["dataquote"]) && $param["dataquote"] == TRMSqlDataSource::NOQUOTE)
                    ) { $wherestr .= " " . $param["value"] . " "; }
            else { $wherestr .= "'" . trim($param["value"], "'") . "' "; }
        }
    }

    return ltrim(trim($wherestr), "ANDOR"); // $wherestr;
}

/**
 * ��������� ������ ������ � �� ����������� ��� ������ FROM SELECT-�������
 * ����� ������ � �� ���������� ������� �� SafetyFields
 * 
 * @return string - ������ ���� "`table1` AS `t1`, `table2` AS `ttt`"
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
 * ��������� � ���������� ������ SQL-������� � �� ��� ������ ������
 * ��������� WHERE ��������������� ����� ������� ���� ������� � ������� - addWhereParam ��� addWhereParamFromArray
 *
 * @return string
 * 
 * @throws TRMDataSourceSQLEmptyTablesListException
 */
public function makeSelectQuery() // array $params = null, $limit = 1, $offset = null)
{
    // ������ � ������������� ����� ��� ������� 
    $fieldstr = $this->generateFieldsString();

    $fromstr = $this->generateFromStr();
    
    if(empty($fromstr))
    {
        throw new TRMDataSourceSQLEmptyTablesListException( __METHOD__ );
    }
    
    $this->QueryString = "SELECT " . $fieldstr . " FROM " . $fromstr;
    
    // ������ � ������� JOIN, �������������� ��� ������� �� ������� Tables
    $joinstr = $this->generateJoinString();
    if( strlen($joinstr) ) { $this->QueryString .= $joinstr; }

    // ������ � ��������� WHERE
    $wherestr = $this->generateWhereString();
    if( strlen($wherestr) ) { $this->QueryString .= " WHERE " . $wherestr . " "; }

    // ����� ������� ��� �����������
    if( !empty($this->GroupFields) )
    {
        $this->QueryString .= " GROUP BY ";
        foreach( $this->GroupFields as $field => $group )
        {
                $this->QueryString .= " {$field},";
        }
        $this->QueryString = rtrim($this->QueryString, ",") . " ";
    }

    // ����� ������� ��� ����������
    if( !empty($this->OrderFields) )
    {
        $this->QueryString .= " ORDER BY ";
        foreach( $this->OrderFields as $field => $order )
        {
                $this->QueryString .= " {$field} {$order},";
        }
        $this->QueryString = rtrim($this->QueryString, ",");
    }

    // ����� ������� ��� ��������� ������ ������� � ����������� �� ���������� ���������� ������� �� �������
    if( is_int($this->Count) ) { $this->QueryString .= " LIMIT {$this->Count}"; }
    if( is_int($this->StartPosition) ) { $this->QueryString .= " OFFSET {$this->StartPosition}"; }

    return $this->QueryString;
}

/**
 * ����������� ���� � ���������, ������ ������ � �������� �� ������ �����, �.�. �������� �� �������
 *
 * @param string $key - ���� ��� ���������� � ������������� � ��������,
 * ����������� ����������� `key`, ���� ������� �������������� � ������� ����� �����,
 * �� ������������ ������ ���� `table`.`key`
 * 
 * @return string - �������������� ��� ������� � ������ ���� �������
 */
protected function prepareKey($key)
{
    $key = "`".str_replace(".", "`.`", $key)."`";

    return str_replace("``", "`", $key);
}

/**
 * ��������� �������� ��� ������� WHERE � �������
 * 
 * @param string $tablename - ��� ������� ��� ����, ������� ����������� � �������
 * @param string $fieldname - ��� ���� ��� ���������
 * @param string|numeric|boolean $data - ������ ��� ���������
 * @param string $operator - �������� ��������� (=, !=, >, < � �.�.), ����������� =
 * @param string $andor - ��� ������� ����� ���� �������� OR ��� AND ? �� ��������� AND
 * @param integer $quote - ����� �� ����� � ��������� ����� �����, �� ��������� ����� - TRMSqlDataSource::NEED_QUOTE
 * @param string $alias - ����� ��� ������� �� ������� ������������ ����, ���� �� �����, �� ����� ��������� � ������� ������� �������
 * @param integer $dataquote - ���� ����� �������� ������������ ��������� ��� �������, 
 * �� ���� �������� ������� ���� - TRMSqlDataSource::NOQUOTE
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
 * ��������� ������� � ������ WHERE-�������
 * 
 * @param string $tablename - ��� ������� ��� �������� ��������������� ����
 * @param array $params - ������ � ����������� ���������� �������<br>
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
    
    // ���������, ���� �� ��� ����� �������, ��� �� �� ��������� ������ ��� ��������
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
        // ��� �������� �������� ���������� ��������� �������, ��� �� �� ���� ��������� � �������
        if( is_string($value["value"]) )
        {
            $value["value"] = str_replace("'", "\\'", $value["value"]);
        }
    }
    
    /* OPERATOR - ��� �������� value ��� ����� ���� = ��� �� = */
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
    // �� ��������� ��� ����� ����� ������� � ���������, ���� ������ ����� ������, �������� ��� ����������� ����� */
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
 * ��������� ������ ���������� � ��� ��������������
 *
 * @param string $tablename - ��� ������� ��� �������� ��������������� ���������
 * @param array - ���������, ������������ � �������, ��� ������� ���� ���������� ID-������ 
 * ��� ������ ������������ � ������� array( $fieldname => array(value, operator, andor, quote, alias, dataquote), ...)
 * ������������� �������� array(..., $fieldname => array(value), ...)
 */
public function generateParamsFrom( $tablename, array $params )
{
    if( $params === null )
    {
        return;
    }

    // ���� ���������� ���������, ������ ������� ��� �������������
    //unset($this->Params);
    //$this->Params = array();
    // � $params ������� ������, ���������� ��������
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
 * ��������� ������ ���������� � $query
 * 
 * @param string $query - ������ SQL-�������
 * 
 * @return \mysqli_result - ������-��������� ���������� �������
 * @throws TRMSqlQueryException - � ������ ���������� ���������� ������� ������������� ����������
 */
public function executeQuery($query)
{
    $result = $this->MySQLiObject->query($query);

    if( !$result )
    {
        throw new TRMSqlQueryException( __METHOD__ . " ������ � �� ������ ������![{$query}]" );
    }
    return $result;
}

/**
 * ��������������� ������� ��� �������� � ������������ ������� � ��
 * ���������� ������ �� ����������
 * ������� ���������� �� getFromDB � addFromDB
 *
 * @return \mysqli_result - ������-��������� ���������� �������
 * @throws TRMSqlQueryException - � ������ ���������� ���������� ������� ������������� ����������
 */
private function runSelectQuery()
{
    if( !$this->makeSelectQuery() )
    {
        throw new TRMSqlQueryException( __METHOD__ . " �������� ����������� ������ � ��" );
    }

    return $this->executeQuery($this->QueryString);
}
 
/**
 * ��������� ������ �� �� ��������� ������, 
 * ������� ����������� ������� makeSelectQuery �� ������ SafetyFields 
 * � Where ����������
 *
 * @return \mysqli_result - ������ � ����������� �������
 * @throws TRMSqlQueryException - � ������ ���������� ���������� ������� ������������� ����������
 */
public function getDataFrom()
{
    $result = $this->runSelectQuery();
    if( !$result )
    {
        throw new TRMSqlQueryException( __METHOD__ . " ������ � �� ������ ������![{$this->QueryString}]" );
    }

    $this->QueryString = "";
    return $result;
}

/**
 * ��������� ������ �� �� ��������� ������, ������� ���������� ������� makeSelectQuery
 * ��������� ���������� ��������� � ��������� ������
 *
 * @return int - ���������� ����������� ����� �� ��
 * @throws TRMSqlQueryException - � ������ ���������� ���������� ������� ������������� ����������
 */
//public function addDataFrom() // array $params = null)
//{
//    $result = $this->runSelectQuery(); //$params);
//    if( !$result )
//    {
//        throw new TRMSqlQueryException( __METHOD__ . " ������ � �� ������ ������![{$this->QueryString}]" );
//    }
//    $this->DataObject->mergeDataArray( TRMDBObject::fetchAll($result) ); //$result->fetch_all(MYSQLI_ASSOC) );
//    return $result->num_rows;
//}

/**
 *  
 * @param array $IndexesNames - ����� ������ ������� ����� ��������� ����, 
 * ������� ������� �������� � ������ WHERE update-�������, 
 * ��������� ������� ������� ��������� ��������, 
 * ���� �� ������� ���������, �� ���� ���������� �����, 
 * ���� � ��� �� ��������, ��, ����������, ��� ������� DELETE ����� ��������� ��� ����
 * ��� ������ �� ���������... 
 * @param array $UpdatableFieldsNames - ����� ������ ������� ������� ������ ��������� ��� ������ �����
 * @param array $CurrentKeyFlag - ���� �� null, �� ����� ������ ������� ����� ��������� ������ � ������ ����� 
 * array( "PRI", "UNI", "*" ) ��� 
 * array( "PRI", "UNI" ) ��� 
 * array( "PRI" ), 
 * ��� �� ����� �� �������� ����� ������� ������ Update, ��� �� ����������...
 * ������� � ���, ��� 
 * ��������� ������ ���������� ����� ��� ������ �������, ��� ��� ����� ������ � �� ����� ��������,
 * � ��������� ������ � ���������� ����� �� �������� ����� �������� ������ � ��� ����� ������ ����������
 * 
 * @throws TRMDataSourceWrongTableSortException
 * @throws TRMDataSourceNoUpdatebleFieldsException
 */
private function generateIndexesAndUpdatableFieldsNames( array &$IndexesNames, array &$UpdatableFieldsNames, array &$CurrentKeyFlag = null )
{
    // ��������� ������ � DataMapper, ����� �������,
    // ��� �� ������� ��� ��� ����������� ������, 
    // ��������, �������������, ������, ��. ���������,
    // � ��� ����� ��������� �� ��� 
    if(!$this->SafetyFields->sortObjectsForRelationOrder())
    {
        throw new TRMDataSourceWrongTableSortException(__METHOD__ . " ������������� ������ � ��������� �� �������");
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
        
        // �������� ������ ��������� ��� ������ ����� � ��������� ������� $TableName
        $UpdatableFieldsNames[$TableName] = $this->SafetyFields->getUpdatableFieldsNamesFor($TableName);
        // ���� ������ �������� ������, 
        // �� ���������� ����
        if( empty($UpdatableFieldsNames[$TableName]) )
        {
            unset($UpdatableFieldsNames[$TableName]);
            continue;
        }
        
        $IndexesNames[$TableName] = array();
        // ���� �� ���� ��������� ��� ������ ���� � ���� �������,
        // �� ��������� ����, ������� ����� � ������ WHERE update-������� ��� ������� $TableName
        // ��������� ������� ������� ��������� ��������, 
        // ���� �� ������� ���������, �� ������ ���������� �����,
        // ���� � ��� �� ��������, �� ��� ������� DELETE ����� ��������� ��� ����, 
        // ��� ������ ������ �� ����������� ���� �������� �� ���� ����� �������
        foreach( $Keys as $Key )
        {
            $IndexesNames[$TableName] = $this->SafetyFields->getIndexFieldsNames($TableName, $Key);
            if( !empty($IndexesNames[$TableName]) )
            {
                // ��������� ��� �����, ��� �� ����� �� �������� ����� ����� ������ Update, ��� �� ����������...
                // ������� � ���, ��� ��������� ������ ���������� ����� ��� ������ ������� � ���, ��� ��� ����� ������ � �� ����� ��������,
                // � ��������� ������ � ���������� ����� �� �������� ����� �������� ������ � ��� ����� ������ ����������!!!
                if( isset($CurrentKeyFlag) ) { $CurrentKeyFlag[$TableName] = $Key; }
                break;
            }
        }

        // ���� ������� ���� ��� WHERE ��������� �� �������, 
        // �� ������ WHERE update-������� ��������� ������,
        // � ���� ������ ������ ���� UPDATE TABLE SET FIELD1 = Value
        // ������� �������� ���� FIELD1 �� ���� ������� TABLE...
        // ��� �� ������ ��, ��� �����, 
        // ������� ��������� � ����������� ���� ��� ���� �������, ������ �� ������� ��...
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
 * ��������� ������ � ������� �� ������� �� �������-������ DataObject,
 * ���� ������ ��� ��� � �������, �.�. ��� ID ��� ������� ������,
 * �� ��������� ��, ���� ��� ������� ���������� �������� ����� ��� ����������� ����,
 * �� ��������� ������!!!
 *
 * @param TRMDataObjectInterface $DataObject - ������ � �������
 * @param array $IndexesNames - ������ � ������� ��������� �����, 
 * ������� ������ ����������� ��� ������ � �� ��� ��������� � ������� ��������� ������� 
 * @param array $UpdatableFieldsNames - ��� ����, ������� ����� ���� �������� � ������� ������, 
 * ��� �� ������������ ��� ������ �������, ���� �� ������ ��������� ���� $IndexesNames
 * @param array $CurrentKeyFlag - ����� ���� ��������� - "PRI", "UNI", "*"
 * 
 * @return boolean - ���� ���������� ������ �������, �� ������ true, ����� - false
 */
protected function generateSQLUpdateQueryString(
        TRMDataObjectInterface $DataObject,
        array $IndexesNames,
        array $UpdatableFieldsNames,
        array $CurrentKeyFlag)
{
    if( !$DataObject->count() ) { return true; }
    // ������ � �������� ������������ ��������!!!
    $ErrorRows = array();

    // ������ � ������� �������
    $Row = $DataObject->getDataArray();
    foreach ($UpdatableFieldsNames as $TableName => $FieldsNames)
    {
        try
        {
            // ���� ����������� ������ ��� ��������� ������� ������ ���� � ��������� �����,
            // �� ��� ��� ����������, ������ ��� ����� ������,
            // ��������� �� � ��������� � ���������
            if( $CurrentKeyFlag[$TableName] == "PRI" && !$DataObject->presentDataIn($RowNum, $TableName, $IndexesNames[$TableName] ) )
            {
                // � ������� ����������
                // �������� ���� ������, ����� ������ � ������� ������ �� ������� ����������� ������,
                // � ��� ����� ������ ���� ��������� ���������������� ����,
                // ���� ����� �����������,
                // � ��� �� �������� ������ � ������ ���������� ��� ���������� ,
                // ��� �� �� �������� ��� ������ �������� �������...
                // � ���� ���������� ������ ���������� �� ������!!!
                $CurrentInsertId = $this->insertRowToOneTable($TableName, $Row[$TableName], $FieldsNames);

// ����� ������ ����� ������� � �������� ON DUPLICATE KEY ... UPDATE
//                $CurrentInsertId = $this->insertODKURowToOneTable($TableName, $Row[$TableName], $FieldsNames);

                // ���� ID �� ��������, ������ ���������� ����-������������� ���� � �� �� ���������, 
                // ��������� � ������ �������
                if( !$CurrentInsertId ) { continue; }

                /**
                 * ����� �������� ������ �� Relation � ��������, 
                 * ������� ��������� �� ���� AUTO_INCREMENT ������-��� ����������� ������
                 */
                $this->checkAutoIncrementFieldUpdate( $DataObject, $TableName, $RowNum, $CurrentInsertId);

                //$this->addNewRowToAndSetLastId( $Row, $RowNum, $UpdatableFieldsNames );
                // ����� ����������, ��������� � ��������� ������ � ������� ������
                continue;
            }
            else
            {
                // ���� ������ ���� � ��������� ��� ���������� �����
                // ������ ������ ��� ���� ������� ����� ��������
                $this->UpdateQueryString .= $this->makeUpdateRowQueryStrForOneTable( $TableName, $Row[$TableName], $FieldsNames, $IndexesNames[$TableName] );
            }
        }
        catch( TRMSqlQueryException $e )
        {
            $ErrorRows[$RowNum] = $e->getMessage();
        }
    }

    if(!empty($ErrorRows))
    {
        TRMLib::sp(__METHOD__ . " ����� ������� �� ������� �������� � �� ");
        TRMLib::ap($ErrorRows);
    }
}

/**
 * ��������� ������ � ������� �� ������� �� ��������� ��������-������ $DataCollection,
 * ���� ������ ��� ��� � �������, �.�. ��� ID ��� ������� ������, 
 * �� ��������� ��, ���� ��� ������� ���������� �������� ����� ��� ����������� ����,
 * �� ��������� ������!!!
 *
 * @param TRMDataObjectsCollection $DataCollection - ��������� � ��������� ������
 * 
 * @return boolean - ���� ���������� ������ �������, �� ������ true, ����� - false
 */
public function update(TRMDataObjectsCollection $DataCollection)
{
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
    foreach( $DataCollection as $DataObject )
    {
        // ������� ��������� ������ UPDATE � $this->UpdateQueryString
        // � ���� ����� ������� INSERT ����������� ���������, 
        // ��� ������ ���������� ������ ��� �������� �����, ��� �� ��������� LastID
        $this->generateSQLUpdateQueryString($DataObject, $IndexesNames, $UpdatableFieldsNames, $CurrentKeyFlag);
    }

    if( !empty($this->UpdateQueryString) )
    {
        // ����������� ���������� ������� UPDATE, 
        // � ������ ������� ������������� ����������!
        $this->completeMultiQuery($this->UpdateQueryString);
        $this->UpdateQueryString = "";
    }
    
    return true;
}

/**
 * ��������� SQL-������ ��� ���������� ������ ������ � ����� �������
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

    // ��� ������ ���� ������� ������ �� ����� �� ������� � ������������ ������
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
 * ��������� ������ � ���� ������� ������� INSERT INTO ...
 * 
 * @param string $TableName - ��� �������, � ������� ���������� ������
 * @param array $Row - ���������� ������������� ������-������ � ������� = array( FieldName1 => data1, FieldName2 => data2, ... )
 * @param array $FieldsNames - ���������� ������ � ������� ����� �������, � ������� ����� ��������� ������
 * @return int - insert_id (auto_increment)
 * @throws TRMDataSourceSQLInsertException
 */
private function insertRowToOneTable( $TableName, array &$Row, array &$FieldsNames )
{
    // �������� ������ � ������� ����� � �������,
    // �������� ��� ������� ���� ����������� `
    $FieldsNamesStr = "`" . implode("`,`", $FieldsNames) . "`";
    $InsertQuery = "INSERT INTO `{$TableName}` ({$FieldsNamesStr}) VALUES(";
    foreach( $FieldsNames as $FieldName )
    {
        $InsertQuery .= "'" . addcslashes( $Row[ $FieldName ], "'" ) . "',";
    }
    $InsertQuery = rtrim($InsertQuery, ",") . ");";

    // �� ����� ������� completeMultiQuery, ��� ��� ���� ����������� insert_id ��� ������ �������!!!
    if( !$this->MySQLiObject->query($InsertQuery) )
    {
        throw new TRMDataSourceSQLInsertException( __METHOD__ . " [{$InsertQuery}] " );
    }
    return $this->MySQLiObject->insert_id;
}

/**
 * ��������� ������ � ���� �������, 
 * ��������� ����� ������� INSERT INTO ... ON DUPLICATE KEY UPDATE
 * 
 * @param string $TableName - ��� �������, � ������� ���������� ������
 * @param array $Row - ���������� ������������� ������-������ � ������� = array( FieldName1 => data1, FieldName2 => data2, ... )
 * @param array $FieldsNames - ���������� ������ � ������� ����� �������, � ������� ����� ��������� ������
 * @return int - insert_id (auto_increment)
 * @throws TRMDataSourceSQLInsertException
 */
private function insertODKURowToOneTable( $TableName, array &$Row, array &$FieldsNames )
{
    // �������� ������ � ������� ����� � �������,
    // �������� ��� ������� ���� ����������� `
    $FieldsNamesStr = "`" . implode("`,`", $FieldsNames) . "`";
    $InsertQuery = "INSERT INTO `{$TableName}` ({$FieldsNamesStr}) VALUES(";
    $ODKUStr = "ON DUPLICATE KEY UPDATE ";
    foreach( $FieldsNames as $FieldName )
    {
        $InsertQuery .= "'" . addcslashes( $Row[ $FieldName ], "'" ) . "',";
        $ODKUStr .= "`{$FieldName}` = VALUES(`{$FieldName}`),";

    }
    $InsertQuery = rtrim($InsertQuery, ",") . ")" . rtrim($ODKUStr, ",") . ";";

    // �� ����� ������� completeMultiQuery, ��� ��� ���� ����������� insert_id ��� ������ �������!!!
    if( !$this->MySQLiObject->query($InsertQuery) )
    {
        throw new TRMDataSourceSQLInsertException( __METHOD__ . " �� ������� �������� ������: [{$InsertQuery}]" );
    }
    return $this->MySQLiObject->insert_id;
}

/**
 * ��������� ����� ������ � ��, 
 * � ������ ������ ���������� update(), 
 * 
 * @return boolean - ���� ���������� ������ �������, �� ������ true, ����� - false
 */
public function insert(TRMDataObjectsCollection $DataCollection)
{
    return $this->update($DataCollection);
}


/**
 * ��������� ����� ������ ��� ������������ ���� AUTO_INCREMENT � $TableName
 * � ������� ���������, ���� �� ��� ���� ���-�� ���������, �� ��������� �������� �� ����� �������������
 * 
 * @param TRMDataObjectInterface $DataObject - ������ � �������
 * @param string $TableName - ��� �������, ��� ��������� ���������� ����������������� ����
 * @param string $RowNum - ����� ������ � ������� � DataObject
 * @param string $CurrentInsertId - ���������� ID ����� ���������� ��������� INSERT � MySQL
 */
private function checkAutoIncrementFieldUpdate( TRMDataObjectInterface $DataObject, $TableName, $RowNum, $CurrentInsertId)
{
    // getAutoIncrementFieldsNamesFor ���������� ������ � auto_increment ������ ��� ������� $TableName
    // ��� ���������� ����� ����� ���� ������ ���� ���� !
    $AutoIncFieldsArray = $this->SafetyFields->getAutoIncrementFieldsNamesFor($TableName);
    // ���� � ����� ����-������� ��� ������ ������� �� ������� ���� auti_increment, 
    // ��������� ����������
    if( empty($AutoIncFieldsArray) ) { return; }

    // ���� ���������������� ���� �������,
    // �� ������ ��� ������� ������ ����
    foreach($AutoIncFieldsArray as $AutoIncFieldName )
    {
        // ��������� ������ � ���������������� ���� ��� ������ ������� 
        // ������������ � ��������� ������� $TableName
        $DataObject->setData($RowNum, $TableName, $AutoIncFieldName, $CurrentInsertId);
        // �������� ������ ����������� (���������) ����� �� ���� ��������
        $BackRelationArray = $this->SafetyFields->getBackRelationFor($TableName, $AutoIncFieldName);
        if( empty($BackRelationArray) ) { continue; }

        // ��� ���� ��������
        foreach( $BackRelationArray as $BackTableName => $BackFieldsNames )
        {
            // �� ��� ����������� ���� 
            foreach( $BackFieldsNames as $BackFieldName )
            {
                // ������������� ����� ������ ������������ ����!!!
                // ������ ���� ��� ���� �� �������� ����������������
                if( !$this->SafetyFields->isFieldAutoIncrement($BackTableName, $BackFieldName) )
                {
                    $DataObject->setData($RowNum, $BackTableName, $BackFieldName, $CurrentInsertId);
                }
            }
        }
    }
}

/**
 * ������� ������ ��������� �� ������ ��,
 * �� �������� ������� ��������� ������, ������� ������������� �������� ������������ ID-����,
 * ���� ������ ���, �� ������������ �� ���������� �������� �� ���� ����� 
 * (��������� ��� ������, ������� ���� ���� UPDATABLE_FIELD) � ��������� ������ ���������,
 * ��� �� �������� ������ �� �������� ������, 
 * ���� � ��� ����� ���� �� ���� ���� ��������� ��� �������������� - UPDATABLE_FIELD
 * 
 * @param TRMDataObjectInterface $DataObject - ������ � �������
 * @param array $IndexesNames - ������ � ������� ��������� �����, 
 * ������� ������ ����������� ��� ������ � �� ��� ��������� � ������� ��������� ������� 
 * @param array $UpdatableFieldsNames - ��� ����, ������� ����� ���� �������� � ������� ������, 
 * ��� �� ������������ ��� ������ �������, ���� �� ������ ��������� ���� $IndexesNames
 * 
 * @return boolean - ���������� ��������� ������� DELETE
 */

protected function generateSQLDeleteQueryString(
        TRMDataObjectInterface $DataObject, 
        array &$IndexesNames, 
        array &$UpdatableFieldsNames)
{
    if( !$DataObject->count() ) { return true; }

    $DeleteFromStr = "`" . implode("`,`", array_keys($UpdatableFieldsNames) ) . "`";
    $UsingStr = "";
    foreach( array_keys($UpdatableFieldsNames) as $TableName )
    {
        $UsingStr .= "`$TableName` AS `$TableName`,";
    }
    $UsingStr = rtrim($UsingStr, ",");

    // ������ � ������� �������
    $Row = $DataObject->getDataArray();

    $CurrentWhereString = "";
    // $UpdatableFieldsNames - ����� ������ ��� ������ ������, � ������� ���� ��������� ��� ��������� ������,
    // �.�. ������� ����� �������...
    foreach( array_keys($UpdatableFieldsNames) as $TableName )
    {
        // ���� $IndexesNames[$TableName] ������, �� ��� ����� ��� ������ WHERE � DELETE-�������
        // ������ ��������� � ��������� �������
        if( empty( $IndexesNames[$TableName] ) ) { continue; }

        // ����� � $IndexesNames[$TableName] ����������� ���� ��� ������ � ������ WHERE � DELETE-�������,
        // � ������...
        // ���� ��������� ���� ���� �� ���������� � DataMapper,
        // ����� � $IndexesNames[$TableName] ������� ��� ���� ��� ������ �������,
        // ����� ������� ��� ������ �� ��, 
        // � ������� �������� ����� � �� � � ������� ������ ���������!!!
        // �� ��� �������� 3-� ������� ������� $Keys => * � ������� generateIndexesAndUpdatableFieldsNames
        foreach( $IndexesNames[$TableName] as $FieldName )
        {
            $CurrentWhereString .= "`{$TableName}`.`{$FieldName}` = '" . addcslashes( $Row[ $FieldName ], "'" ) . "' AND ";
        }
    }

    // ���� ������������ ������� ��� ����� ���������� ������� � ��,
    // �� ��������� ��������� ������ DELETE � �������
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
 * ������� ������ ��������� �� ������ ��,
 * �� �������� ������� ��������� ������, ������� ������������� �������� ������������ ID-����,
 * ���� ������ ���, �� ������������ �� ���������� �������� �� ���� ����� 
 * (��������� ��� ������, ������� ���� ���� UPDATABLE_FIELD) � ��������� ������ ���������,
 * ��� �� �������� ������ �� �������� ������, 
 * ���� � ��� ����� ���� �� ���� ���� ��������� ��� �������������� - UPDATABLE_FIELD
 * 
 * @param TRMDataObjectsCollection $DataCollection - ��������� � ��������� ������
 * @return boolean - ���������� ��������� ������� DELETE
 */
public function delete(TRMDataObjectsCollection $DataCollection)
{
    $IndexesNames = array();
    $UpdatableFieldsNames = array();

    // ��������� ������� �� ��������� ����,
    // ����� �� ���������� ����� ��� ������������� ������ ��� ��������,
    // � ���� �� �������, ����� ���������� ��� ��������� ��� ���� ������ , 
    // ��� �� �� ��� �� ��� ���������������� ������
    $this->generateIndexesAndUpdatableFieldsNames($IndexesNames, $UpdatableFieldsNames);

    foreach( $DataCollection as $DataObject )
    {
        $this->generateSQLDeleteQueryString($DataObject, $IndexesNames, $UpdatableFieldsNames);
    }

    if( !empty($this->DeleteQueryString) )
    {
        $this->completeMultiQuery($MultiQueryStr);
        $this->DeleteQueryString = "";
    }
    return true;
}

/**
 * ��������� ������ �� ���������� (��� ������) SQL-���������
 * � ��������� ����������, ������� ����� ��� ����������� ���������� ��������� ��������, 
 * ���������� ��� ����������
 * 
 * @param string $querystring - ������ SQL-�������
 * @throws TRMSqlQueryException - � ������ ���������� ������� ������������� ����������!
 */
private function completeMultiQuery($querystring)
{
    if( !$this->MySQLiObject->multi_query($querystring) )
    {
        throw new TRMSqlQueryException( __METHOD__ . " ������ ��������� �� ������� [{$querystring}] - ������ #(" . $this->MySQLiObject->sqlstate . "): " . $this->MySQLiObject->error );
    }
    if( $this->MySQLiObject->insert_id ) { $this->LastId = $this->MySQLiObject->insert_id; }

    // ������� ����� multi_query($query), ����� ��������� ������� �� ���������
    while($this->MySQLiObject->more_results())
    {
        $this->MySQLiObject->next_result();
    }
}


} // TRMSqlDataSource