<?php

namespace TRMEngine;

use TRMEngine\Exceptions\TRMSqlQueryException;
use TRMEngine\Helpers\TRMLib;

/**
 * ������� ����� ��� ������ � ��, ������� ����������� ��������� ���������� MySQLi
 *
 * @author TRM
 */
class TRMDBObject // extends TRMConfigSingleton
{
/**
 * ��������� ������ � �� �� ���������, ���� � ���������� �� ����� �������� "dbcharset"
 */
const TRM_DB_DEFAULT_CHARSET = "utf8";
/**
 * ���������� ������� ����������� � ��������
 */
const TRM_DB_TRY_TO_CONNECT_TIMES = 3;
/**
 * ����� ����� ����� ��������� ����������� � �������� � ������������ 10^-6
 * 500000 - 0.5 sec
 */
const TRM_DB_TRY_TO_CONNECT_SLEEPTIME = 500000;

/*
	use TRMSingleton;
	use TRMConfigSingleton;
*/


/**
 * @var TRMDBObject - ��������� ������� ������� Singleton
 */
protected static $Instance = null;

/**
 * ���������� ��������� ������� ������, ���� �� ��� �� ������, �� ������� ���
 * @return TRMDBObject - ��������� ������� ������
 */
public static function getInstance()
{
    if( !isset(static::$Instance) )
    {
        $ClassName = get_called_class();
        static::$Instance = new $ClassName();
    }

    return static::$Instance;
}
/**
 * @var array - ���������������� ������
 */
protected static $ConfigArray = array();

/**
 * ��������� ���������������� ������ �� ����� $filename - ������ ������������ � ���� �������
 *
 * @param string $filename - ��� ����� � �������������
 */
public static function setConfig( $filename )
{
	if( !is_file($filename) )
	{
		TRMLib::dp( __METHOD__ . " ���� � ����������� �������� �� ������� [{$filename}]!" );
		return false;
	}
	return self::setConfigArray( require_once($filename) );
}

/**
 * ��������� ���������������� ������ �� �������
 *
 * @param array $arr - ������ � �������������
 */
public static function setConfigArray( array $arr )
{
	if( empty($arr) )
	{
		TRMLib::dp( __METHOD__ . " ������ ������������ ������ ������!" );
		return false;
	}

	static::$ConfigArray = $arr;

	return true;
}

/**
 * ���������� ������� ��������� ������ � ��
 * 
 * @return array - ������ � �������������
 */
public static function getConfigArray()
{
    return static::$ConfigArray;
}


/**
 * @var \mysqli - ������ MySQLi - ����������� � ��
 */
static public $newlink = null;

static $QB;


/**
 * ����������� � ���� � ������������ �������, ���� ���������� ��� ����, �� ��������� ���
 */
protected function __construct()
{
    static::ping();
}

/**
 * @param string $Query - ������ �������
 * 
 * @return \mysqli_result - ���������� ��������� ������� $Query ����� MySQLi, 
 * ���� null, ���� ���������� ��� �� �����������
 */
public static function query($Query)
{
    if( static::$newlink )
    {
        return static::$newlink->query($Query);
    }
    return null;
}

/**
 * ������������ � �� ��������� ��������� �� ������� $ConfigArray
 * 
 * @return boolean - � ������ ������ ������ true
 * @throws \Exception - � ������ ������� - ����������
 */
public static function connect()
{
    $trycounts = 0;

    while(!static::$newlink)
    {
        if( $trycounts == TRMDBObject::TRM_DB_TRY_TO_CONNECT_TIMES )
        {
            throw new \Exceptions( __METHOD__ . " �� ������� ���������� ��������� ���������� � �� " 
                    . static::$ConfigArray["dbserver"] 
                    . " - " . static::$ConfigArray["dbuser"] );
        }
        static::$newlink = new \mysqli( isset(static::$ConfigArray["dbserver"]) ? static::$ConfigArray["dbserver"] : null,
                                isset(static::$ConfigArray["dbuser"]) ? static::$ConfigArray["dbuser"] : null,
                                isset(static::$ConfigArray["dbpassword"]) ? static::$ConfigArray["dbpassword"] : null,
                                isset(static::$ConfigArray["dbname"]) ? static::$ConfigArray["dbname"] : null,
                                isset(static::$ConfigArray["dbport"]) ? static::$ConfigArray["dbport"] : null );
        if( !static::$newlink->connect_error )
        {
            break;
        }
        usleep(TRMDBObject::TRM_DB_TRY_TO_CONNECT_SLEEPTIME);
        $trycounts++;
    }
    static::$newlink->set_charset( isset(static::$ConfigArray["dbcharset"]) ? static::$ConfigArray["dbcharset"] : static::TRM_DB_DEFAULT_CHARSET );

    return true;
}

/**
 * ���������������� � ��, ���� ���������� �������, �������� ��� � ����������� �����
 * 
 * @return boolean - true � ������ ��������� ���������� � �� , ����� ������� connect ����������� ���������� \Exception
 */
public static function reconnect()
{
    if( static::$newlink )
    {
        static::$newlink->close();
    }
    static::$newlink = null;
    return static::connect();
}


/**
 * ������ ������ � ��. ���� ��������� ������ 2006 (server has gone away), �� �������� ������������ ������
 *  
 * @return boolean - true � ������ ��������� ���������� � �� , ����� ������� connect � reconnect ����������� ���������� \Exception
 */
public static function ping()
{
    if( static::$newlink )
    {
        static::$newlink->query('SELECT LAST_INSERT_ID()');
    }
    else
    {
        return static::connect();
    }

    if (static::$newlink->errno == 2006)
    {
        return static::reconnect();
    }
    return true;
}

/**
 * �������������� ����������, ��������� ����������� � ��
 */
public static function close()
{
    static::$newlink->close();
}

/**
 * @param string $TableName - ��� �������, ��� ������� ����� �������� ��������� �������-����� �� ��
 * @param type $ExtendFlag - ���� ���������� � true, �� ������ ����� �������� ����� ����� ��� ������� �� ��,
 * ���� � false, �� �������� SHOW COLUMNS FROM 
 * 
 * @return array - ������ � ����������� �������
 * 
 * @throws TRMSqlQuery\Exception - ���� ������ �������� � �������, ������������ ����������
 */
public static function getTableColumnsInfo($TableName, $ExtendFlag = false)
{
    if(!$ExtendFlag)
    {
        $Result = self::$newlink->query("SHOW COLUMNS FROM `{$TableName}`");
    }
    else
    {
        $Result = self::$newlink->query("SELECT *
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME LIKE '{$TableName}'");
    }
    if( !$Result )
    {
        throw new TRMSqlQueryException( __METHOD__ 
                . " ������ ��������� ����� ��� ������� [{$TableName}]"
                . " (#" . self::$newlink->errno . "): " . self::$newlink->error);
    }
    return self::fetchAll($Result);
}

/**
 * ��������� ������ ���� �������, � ������� ���� ��� ������������� �������� ����� ��� $ParentFieldName,
 * �����, ���������� ���������� ��� ������� ����������, ��� ����� ������� �������� �������� �������� ���������
 * �������������� (������������) �������� ����������� � ����
 * �������� ��� ������ ���� �� �������� StartId
 * 
 * @param int $StartId - ������ ID, �� �������� ���������� ������� �� ������
 * @param string $TableName - �������, �� ������� ������������ �������
 * @param string $IdFieldName - ��� ���� � ID �������
 * @param string $ParentFieldName - ��� ����, ������� ������� �������� ID
 * @param string $OrderFieldName - ��� ����, �� �������� ������������ ���������� (��������! ���������� � ������ ������� ������ ID)
 * @param string $PresentFieldName - ���� �� null, ����� �������� ��� ����-�����, ������� ���������� �� ������� ������,
 * ��� ���������� � ��������������� ������� ���� ������ �� ���� $PresentFieldName ������ ���� ����������� �� ������, ������� �� 0, � �� NULL
 * @param boolean $first - ��� ���������������� ������ ���� ���� �� ��������� = true, �.�. ������ ����������� �����,
 * �� ��������� �������� ������������ $StartId � ������ ������� ��������������� �������
 * 
 * @return array - ���������� ������ ���� �������� ID �� ���� $IdFieldName
 */
public static function getAllChildsArray($StartId, $TableName, $IdFieldName, $ParentFieldName, $OrderFieldName = null, $PresentFieldName = null, $first=true)
{
    $query  = "SELECT {$IdFieldName} FROM `{$TableName}` WHERE `{$ParentFieldName}`=".$StartId;
    if( isset($PresentFieldName) )
    {
        $query .=" AND `{$TableName}`.`{$PresentFieldName}`<>''  "
                . "AND `{$TableName}`.`{$PresentFieldName}`<>'0' "
                . "AND `{$TableName}`.`{$PresentFieldName}`<>'NULL'";
    }
    if( isset($OrderFieldName) )
    {
        $query .=" ORDER BY `{$TableName}`.`{$OrderFieldName}` ";
    }
    
    $allgroups = array();
    if($first === true) { $allgroups[] = $StartId; }
    $result1 = static::$newlink->query($query);
    if( !$result1 )
    {
        //TRMLib::dp( __METHOD__ . "������ ��������� [{$query}]" );
        return null;
    }
    if( $result1->num_rows == 0 )
    {
        return $allgroups;
    }

    // ��������� ������ �������� ������� � ������ (���� ��� ��� ���) � ���������� �������� ��� ���� ��� �������
    while ($row1 = $result1->fetch_array(MYSQLI_ASSOC)) 
    {
        // ���� ��������� ID ��� ���� � �������, ������ � ��������� ������������, ���������� ���
        if( in_array( $row1[$IdFieldName], $allgroups ) ) { continue; }
        $allgroups[]=$row1[$IdFieldName];
        $currentgroups = static::getAllChildsArray($row1[$IdFieldName], $TableName, $IdFieldName, $ParentFieldName, $OrderFieldName, $PresentFieldName, false);
        if( !empty($currentgroups) )
        {
            $allgroups = array_merge($allgroups, $currentgroups);
        }
    }
    $result1->free();
    return $allgroups;
}

/**
 * �������� ������ ������ ��� ���������� ������� $res,
 * ��� �������, � �����-�� ������ PHP �� ������� mysqli_result::fetch_all
 * 
 * @param mysqli_result $res - ������ � ����������� ��������� SQL-������� ����� mysqli_result::query
 * @param int $stat - � ����� ���� �������� ��������� (MYSQLI_NUM - ������������ ������, MYSQLI_ASSOC - ������ � ��������� ��� ���� � ������� ��, MYSQLI_BOTH - �������� ��� ��������)
 * @return array - ������ ����� � ������� �������
 */
public static function fetchAll($res, $stat = MYSQLI_ASSOC)
{
    if(method_exists($res, "fetch_all") )
    {
        return $res->fetch_all($stat);
    }
    
    $arr = array();
    while( $row = $res->fetch_array($stat) )
    {
        $arr[] = $row;
    }
    return $arr;
}

} // TRMDBObject
