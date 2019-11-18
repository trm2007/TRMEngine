<?php

namespace TRMEngine;

use TRMEngine\Exceptions\TRMConfigArrayException;
use TRMEngine\Exceptions\TRMConfigFileException;
use TRMEngine\Exceptions\TRMSqlQueryException;

/**
 * базовый класс для работы с БД, создает подключение используя библиотеку MySQLi
 *
 * @author TRM
 */
class TRMDBObject // extends TRMConfigSingleton
{
/**
 * кодировка работы с БД по умолчанию, если в настройках не задан параметр "dbcharset"
 */
const TRM_DB_DEFAULT_CHARSET = "utf8";
/**
 * количество попыток соединиться с сервером
 */
const TRM_DB_TRY_TO_CONNECT_TIMES = 3;
/**
 * время паузы между попытками соединиться с сервером в мкросекундах 10^-6
 * 500000 - 0.5 sec
 */
const TRM_DB_TRY_TO_CONNECT_SLEEPTIME = 500000;
/**
 * индекс для имени базы данных
 */
const DB_NAME_INDEX = "dbname";
/**
 * индекс для имени сервера
 */
const DB_SERVER_INDEX = "dbserver";
/**
 * индекс для номера порта
 */
const DB_PORT_INDEX = "dbport";
/**
 * индекс для имени пользователя
 */
const DB_USER_INDEX = "dbuser";
/**
 * индекс для пароля
 */
const DB_PASSWORD_INDEX = "dbpassword";
/**
 * индекс для обозначения кодировки БД на сервере
 */
const SERVER_DB_CHARSET_INDEX = "dbcharset";
/**
 * индекс для обозначения кодировки в которой будут поступать данные клиенту и от него,
 * на стороне сервера будет происходить перекодировка из и в SERVER_DB_CHARSET
 */
const CLIENT_CHARSET_INDEX = "clientcharset";
/**
 * индекс для обозначения кодировки для сравнения данных в таблицах БД на сервере
 */
const COLLATION_CHARSET_INDEX = "collationcharst";

/**
 * @var string - кодировка БД на сервере
 */
protected static $DBCharset = TRMDBObject::TRM_DB_DEFAULT_CHARSET;
/**
 * @var string - кодировка клиента
 */
protected static $ClientCharset = TRMDBObject::TRM_DB_DEFAULT_CHARSET;
/**
 * @var string - кодировка для сравнения данных в таблицах БД на сервере
 */
protected static $CollationCharset = TRMDBObject::TRM_DB_DEFAULT_CHARSET;

/*
	use TRMSingleton;
	use TRMConfigSingleton;
*/

/**
 * @var TRMDBObject - экземпляр данного объекта Singleton
 */
protected static $Instance = null;

/**
 * возвращает экземпляр данного класса, если он еще не создан, то создает его
 * @return TRMDBObject - экземпляр данного класса
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
 * @var array - конфигурационные данные
 */
protected static $ConfigArray = array();

/**
 * загружает конфигурационные данные из файла $filename - должны возвращаться в виде массива
 *
 * @param string $filename - имя файла с конфигурацией
 */
public static function setConfig( $filename )
{
    if( !is_file($filename) )
    {
        throw new TRMConfigFileException("Файл с настройками получить на удалось [{$filename}]!");
    }
    return self::setConfigArray( require_once($filename) );
}

/**
 * загружает конфигурационные данные из массива
 *
 * @param array $arr - массив с конфигурацией
 */
public static function setConfigArray( array $arr )
{
    if( empty($arr) )
    {
        throw new TRMConfigArrayException("Массив конфигурации данных пустой!");
    }

    static::$ConfigArray = $arr;
    
    static::setCharSetsFromConfigArray();

    return true;
}
/**
 * устанавливает кодировки, если данные под соответсвующими индексами есть в массиве настроек (конфигурации)
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
 * @param string $charset - указывает, в какую кодировку следует преобразовать данные 
 * полученые от клиента перед выполнением запроса,
 */
public static function setDBCharset( $charset )
{
    static::$DBCharset = static::correctCharset($charset);
}
/**
 * @param string $charset - указывает, в какой кодировке будут поступать данные от клиента,
 * а так же указывает серверу не необходимость перекодировать результаты запроса 
 * в эту кодировку перед выдачей их клиенту
 */
public static function setClientCharset( $charset )
{
    static::$ClientCharset = static::correctCharset($charset);
}
/**
 * @param string $charset - указывает, каким образом сравнивать между собой строки в запроса
 */
public static function setCollationCharset( $charset )
{
    static::$CollationCharset = static::correctCharset($charset);
}
/**
 * убирает знаки тире из кодировки, так как в MySQL они не используются,
 * правильная запись utf8 , но не utf-8,
 * так же переводит строку в нижний регистр
 * 
 * @param string $charset - кодировка для корректировки
 * 
 * @return string - возвращает исправленную строку с названием кодировки
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
 * возвращает текущие настройки работы с БД
 * 
 * @return array - массив с конфигурацией
 */
public static function getConfigArray()
{
    return static::$ConfigArray;
}


/**
 * @var \mysqli - объект MySQLi - подключение к БД
 */
static public $newlink = null;

/**
 * подключение к базе в конструкторе объекта, если соединение уже есть, то оставляем его
 */
protected function __construct()
{
    static::ping();
}

/**
 * @param string $Query - строка запроса
 * 
 * @return \mysqli_result - возвращает результат запроса $Query через MySQLi, 
 * либо null, если соединение еще не установлено
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
 * подключается к БД используя настройки из массива $ConfigArray
 * 
 * @return boolean - в случае успеха вернет true
 * @throws \Exception - в случае неудачи - исключение
 */
public static function connect()
{
    $trycounts = 0;

    while(!static::$newlink)
    {
        if( $trycounts == TRMDBObject::TRM_DB_TRY_TO_CONNECT_TIMES )
        {
            throw new \Exceptions( __METHOD__ . " Не удалось установить начальное соединение с БД " 
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

        // указывает, в какой кодировке будут поступать данные от клиента
        static::$newlink->query( "SET character_set_client='" . static::$ClientCharset . "'" );
        // указывает, в какую кодировку следует преобразовать данные 
        // полученые от клиента перед выполнением запроса,
        static::$newlink->query( "SET character_set_connection='" . static::$DBCharset . "'" ); 
        //  указывает серверу не необходимость перекодировать результаты запроса 
        //  в определенную кодировку перед выдачей их клиенту
        static::$newlink->query( "SET character_set_results='" . static::$ClientCharset . "'" ); 
        // указывает, каким образом сравнивать между собой строки в запросах
        static::$newlink->query( "SET collation_connection='" . static::$CollationCharset . "'" );
    }
    return true;
}

/**
 * переподключается к БД, если соединение активно, закрывет его и соединяется вновь
 * 
 * @return boolean - true в случае успешного соединения с БД , иначе функциЯ connect выбрасывает исключение \Exception
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
 * делает запрос к БД. если вернулась ошибка 2006 (server has gone away), то пытается подключиться заново
 *  
 * @return boolean - true в случае успешного соединения с БД , иначе функции connect и reconnect выбрасывают исключение \Exception
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
 * принудительное отключение, закрывает подключение к БД
 */
public static function close()
{
    static::$newlink->close();
}

/**
 * @param string $TableName - имя таблицы, для которой нужно получить параметры колонок-полей из БД
 * @param type $ExtendFlag - если установлен в true, то данные будут получены через схему для таблицы из БД,
 * если в false, то запросом SHOW COLUMNS FROM 
 * 
 * @return array - массив с параметрами колонок
 * 
 * @throws TRMSqlQuery\Exception - если запрос выполнен с ошибкой, вбрасывается исключение
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
                . " Ощибка получения схемы для таблицы [{$TableName}]"
                . " (#" . self::$newlink->errno . "): " . self::$newlink->error);
    }
    return self::fetchAll($Result);
}

/**
 * получает массив данных для результата запроса $res,
 * это полифил, в какой-то версии PHP не работал mysqli_result::fetch_all
 * 
 * @param mysqli_result $res - объект с результатом выполненя SQL-запроса через mysqli_result::query
 * @param int $stat - в каком виде получать результат (MYSQLI_NUM - нумерованный массив, MYSQLI_ASSOC - массив с индексами как поля в таблице БД, MYSQLI_BOTH - вернутся оба варианта)
 * @return array - массив строк с данными запроса
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
