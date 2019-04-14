<?php

namespace TRMEngine;

use TRMEngine\Exceptions\TRMConfigArrayException;
use TRMEngine\Exceptions\TRMConfigFileException;
use TRMEngine\Exceptions\TRMSqlQueryException;

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
/**
 * ������ ��� ����� ���� ������
 */
const DB_NAME_INDEX = "dbname";
/**
 * ������ ��� ����� �������
 */
const DB_SERVER_INDEX = "dbserver";
/**
 * ������ ��� ������ �����
 */
const DB_PORT_INDEX = "dbport";
/**
 * ������ ��� ����� ������������
 */
const DB_USER_INDEX = "dbuser";
/**
 * ������ ��� ������
 */
const DB_PASSWORD_INDEX = "dbpassword";
/**
 * ������ ��� ����������� ��������� �� �� �������
 */
const SERVER_DB_CHARSET_INDEX = "dbcharset";
/**
 * ������ ��� ����������� ��������� � ������� ����� ��������� ������ ������� � �� ����,
 * �� ������� ������� ����� ����������� ������������� �� � � SERVER_DB_CHARSET
 */
const CLIENT_CHARSET_INDEX = "clientcharset";
/**
 * ������ ��� ����������� ��������� ��� ��������� ������ � �������� �� �� �������
 */
const COLLATION_CHARSET_INDEX = "collationcharst";

/**
 * @var string - ��������� �� �� �������
 */
protected static $DBCharset = TRMDBObject::TRM_DB_DEFAULT_CHARSET;
/**
 * @var string - ��������� �������
 */
protected static $ClientCharset = TRMDBObject::TRM_DB_DEFAULT_CHARSET;
/**
 * @var string - ��������� ��� ��������� ������ � �������� �� �� �������
 */
protected static $CollationCharset = TRMDBObject::TRM_DB_DEFAULT_CHARSET;

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
        throw new TRMConfigFileException("���� � ����������� �������� �� ������� [{$filename}]!");
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
        throw new TRMConfigArrayException("������ ������������ ������ ������!");
    }

    static::$ConfigArray = $arr;
    
    static::setCharSetsFromConfigArray();

    return true;
}
/**
 * ������������� ���������, ���� ������ ��� ��������������� ��������� ���� � ������� �������� (������������)
 */
private static function setCharSetsFromConfigArray()
{
    if( empty(static::$ConfigArray) ) { return; }
    
    if(array_key_exists(static::SERVER_DB_CHARSET_INDEX, static::$ConfigArray) )
    {
        static::setDBCharset( static::$ConfigArray[static::SERVER_DB_CHARSET_INDEX] );
    }
    if(array_key_exists(static::CLIENT_CHARSET_INDEX, static::$ConfigArray) )
    {
        static::setClientCharset( static::$ConfigArray[static::CLIENT_CHARSET_INDEX] );
    }
    if(array_key_exists(static::COLLATION_CHARSET_INDEX, static::$ConfigArray) )
    {
        static::setCollationCharset( static::$ConfigArray[static::COLLATION_CHARSET_INDEX] );
    }
}
/**
 * @param string $charset - ���������, � ����� ��������� ������� ������������� ������ 
 * ��������� �� ������� ����� ����������� �������,
 */
public static function setDBCharset( $charset )
{
    static::$DBCharset = static::correctCharset($charset);
}
/**
 * @param string $charset - ���������, � ����� ��������� ����� ��������� ������ �� �������,
 * � ��� �� ��������� ������� �� ������������� �������������� ���������� ������� 
 * � ��� ��������� ����� ������� �� �������
 */
public static function setClientCharset( $charset )
{
    static::$ClientCharset = static::correctCharset($charset);
}
/**
 * @param string $charset - ���������, ����� ������� ���������� ����� ����� ������ � �������
 */
public static function setCollationCharset( $charset )
{
    static::$CollationCharset = static::correctCharset($charset);
}
/**
 * ������� ����� ���� �� ���������, ��� ��� � MySQL ��� �� ������������,
 * ���������� ������ utf8 , �� �� utf-8,
 * ��� �� ��������� ������ � ������ �������
 * 
 * @param string $charset - ��������� ��� �������������
 * 
 * @return string - ���������� ������������ ������ � ��������� ���������
 */
private static function correctCharset( $charset )
{
    $charset = strtolower($charset);
    switch ($charset)
    {
        case "windows-1251": return "cp1251";
        case "utf-8": return "utf8";
    }

    return str_replace("-", "", $charset );
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
    if( static::$ClientCharset == static::$DBCharset )
    {
        static::$newlink->set_charset( static::$DBCharset );
    }
    else
    {
        static::$newlink->set_charset( static::$ClientCharset );

        // ���������, � ����� ��������� ����� ��������� ������ �� �������
        static::$newlink->query( "SET character_set_client='" . static::$ClientCharset . "'" );
        // ���������, � ����� ��������� ������� ������������� ������ 
        // ��������� �� ������� ����� ����������� �������,
        static::$newlink->query( "SET character_set_connection='" . static::$DBCharset . "'" ); 
        //  ��������� ������� �� ������������� �������������� ���������� ������� 
        //  � ������������ ��������� ����� ������� �� �������
        static::$newlink->query( "SET character_set_results='" . static::$ClientCharset . "'" ); 
        // ���������, ����� ������� ���������� ����� ����� ������ � ��������
        static::$newlink->query( "SET collation_connection='" . static::$CollationCharset . "'" );
    }
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
