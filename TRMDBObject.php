<?php

namespace TRMEngine;

use TRMEngine\DataArray\TRMDataArray;
use TRMEngine\Exceptions\TRMConfigArrayException;
use TRMEngine\Exceptions\TRMConfigFileException;
use TRMEngine\Exceptions\TRMSqlQueryException;

/**
 * базовый класс для работы с БД, создает подключение используя библиотеку MySQLi
 *
 * @author TRM
 */
class TRMDBObject
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
protected $DBCharset = TRMDBObject::TRM_DB_DEFAULT_CHARSET;
/**
 * @var string - кодировка клиента
 */
protected $ClientCharset = TRMDBObject::TRM_DB_DEFAULT_CHARSET;
/**
 * @var string - кодировка для сравнения данных в таблицах БД на сервере
 */
protected $CollationCharset = TRMDBObject::TRM_DB_DEFAULT_CHARSET;

/**
 * @var array - конфигурационные данные
 */
protected $ConfigArray = array();
/**
 * @var \mysqli - объект MySQLi - подключение к БД
 */
public $newlink = null;
/**
 * @var int - ID последней добавленной записи после запроса
 */
public $LastId = null;

/**
 * подключение к базе в конструкторе объекта
 * 
 * @param array $ConfigArray - массив с настройками подключения и кодировками
 */
public function __construct( array $ConfigArray = array() )
{
    if( !empty($ConfigArray) )
    {
        $this->setConfigArray($ConfigArray);
        $this->connect();
    }
}


/**
 * загружает конфигурационные данные из файла $filename - должны возвращаться в виде массива
 *
 * @param string $filename - имя файла с конфигурацией
 */
public function setConfigFromFile( $filename )
{
    if( !is_file($filename) )
    {
        throw new TRMConfigFileException("Файл с настройками получить на удалось [{$filename}]!");
    }
    return $this->setConfigArray( require_once($filename) );
}

/**
 * загружает конфигурационные данные из массива
 *
 * @param array $arr - массив с конфигурацией
 */
public function setConfigArray( array $arr )
{
    if( empty($arr) )
    {
        throw new TRMConfigArrayException("Массив конфигурации данных пустой!");
    }

    $this->ConfigArray = $arr;
    
    $this->setCharSetsFromConfigArray();

    return true;
}
/**
 * устанавливает кодировки, если данные под соответсвующими индексами есть в массиве настроек (конфигурации)
 */
private function setCharSetsFromConfigArray()
{
    if( empty($this->ConfigArray) ) { return; }
    
    if(array_key_exists(TRMDBObject::SERVER_DB_CHARSET_INDEX, $this->ConfigArray) )
    {
        $this->setDBCharset( $this->ConfigArray[TRMDBObject::SERVER_DB_CHARSET_INDEX] );
    }
    if(array_key_exists(TRMDBObject::CLIENT_CHARSET_INDEX, $this->ConfigArray) )
    {
        $this->setClientCharset( $this->ConfigArray[TRMDBObject::CLIENT_CHARSET_INDEX] );
    }
    if(array_key_exists(TRMDBObject::COLLATION_CHARSET_INDEX, $this->ConfigArray) )
    {
        $this->setCollationCharset( $this->ConfigArray[TRMDBObject::COLLATION_CHARSET_INDEX] );
    }
}
/**
 * @param string $charset - указывает, в какую кодировку следует преобразовать данные 
 * полученые от клиента перед выполнением запроса,
 */
public function setDBCharset( $charset )
{
    $this->DBCharset = $this->correctCharset($charset);
}
/**
 * @param string $charset - указывает, в какой кодировке будут поступать данные от клиента,
 * а так же указывает серверу не необходимость перекодировать результаты запроса 
 * в эту кодировку перед выдачей их клиенту
 */
public function setClientCharset( $charset )
{
    $this->ClientCharset = $this->correctCharset($charset);
}
/**
 * @param string $charset - указывает, каким образом сравнивать между собой строки в запроса
 */
public function setCollationCharset( $charset )
{
    $this->CollationCharset = $this->correctCharset($charset);
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
private function correctCharset( $charset )
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
public function getConfigArray()
{
    return $this->ConfigArray;
}

/**
 * @param string $Query - строка запроса
 * 
 * @return \mysqli_result - возвращает результат запроса $Query через MySQLi, 
 * либо null, если соединение еще не установлено
 */
public function query($Query)
{
    if( $this->newlink )
    {
        $res = $this->newlink->query($Query);
        if( !$res ) { return null; }
    }
    $this->LastId = $this->newlink->insert_id;
    return $res;
}

/**
 * подключается к БД используя настройки из массива $ConfigArray
 * 
 * @return boolean - в случае успеха вернет true
 * @throws \Exception - в случае неудачи - исключение
 */
public function connect()
{
    $trycounts = 0;

    while(!$this->newlink)
    {
        if( $trycounts == TRMDBObject::TRM_DB_TRY_TO_CONNECT_TIMES )
        {
            throw new \Exceptions( __METHOD__ . " Не удалось установить начальное соединение с БД " 
                    . $this->ConfigArray["dbserver"] 
                    . " - " . $this->ConfigArray["dbuser"] );
        }
        $this->newlink = new \mysqli( isset($this->ConfigArray["dbserver"]) ? $this->ConfigArray["dbserver"] : null,
                                isset($this->ConfigArray["dbuser"]) ? $this->ConfigArray["dbuser"] : null,
                                isset($this->ConfigArray["dbpassword"]) ? $this->ConfigArray["dbpassword"] : null,
                                isset($this->ConfigArray["dbname"]) ? $this->ConfigArray["dbname"] : null,
                                isset($this->ConfigArray["dbport"]) ? $this->ConfigArray["dbport"] : null );
        if( !$this->newlink->connect_error )
        {
            break;
        }
        usleep(TRMDBObject::TRM_DB_TRY_TO_CONNECT_SLEEPTIME);
        $trycounts++;
    }
    if( $this->ClientCharset == $this->DBCharset )
    {
        $this->newlink->set_charset( $this->DBCharset );
    }
    else
    {
        $this->newlink->set_charset( $this->ClientCharset );

        // указывает, в какой кодировке будут поступать данные от клиента
        $this->newlink->query( "SET character_set_client='" . $this->ClientCharset . "'" );
        // указывает, в какую кодировку следует преобразовать данные 
        // полученые от клиента перед выполнением запроса,
        $this->newlink->query( "SET character_set_connection='" . $this->DBCharset . "'" ); 
        //  указывает серверу не необходимость перекодировать результаты запроса 
        //  в определенную кодировку перед выдачей их клиенту
        $this->newlink->query( "SET character_set_results='" . $this->ClientCharset . "'" ); 
        // указывает, каким образом сравнивать между собой строки в запросах
        $this->newlink->query( "SET collation_connection='" . $this->CollationCharset . "'" );
    }
    return true;
}

/**
 * переподключается к БД, если соединение активно, закрывет его и соединяется вновь
 * 
 * @return boolean - true в случае успешного соединения с БД , иначе функциЯ connect выбрасывает исключение \Exception
 */
public function reconnect()
{
    if( $this->newlink )
    {
        $this->newlink->close();
    }
    $this->newlink = null;
    return $this->connect();
}

/**
 * делает запрос к БД,
 * если вернулась ошибка 2006 (server has gone away), то пытается подключиться повторно
 *  
 * @return boolean - true в случае успешного соединения с БД , 
 * иначе функции connect и reconnect выбрасывают исключение \Exception
 */
public function ping()
{
    if( $this->newlink )
    {
        $this->newlink->query('SELECT LAST_INSERT_ID()');
    }
    else
    {
        return $this->connect();
    }

    if ($this->newlink->errno == 2006)
    {
        return $this->reconnect();
    }
    return true;
}

/**
 * принудительное отключение, закрывает соединение с БД
 */
public function close()
{
    $this->newlink->close();
}

/**
 * выполняет запрос из нескольких (или одного) SQL-выражений
 * и завершает выполнение, очищает буфер для возможности выполнения следующих запросов, 
 * перебирает все результаты
 * 
 * @param string $querystring - строка SQL-запроса
 * @throws TRMSqlQueryException - в случае неудачного запроса выбрасывается исключение!
 */
public function multiQuery($querystring)
{
    if( !$this->newlink->multi_query($querystring) )
    {
        throw new TRMSqlQueryException( 
                __METHOD__ 
                . " Запрос выполнить не удалось [{$querystring}] - Ошибка #(" 
                . $this->newlink->sqlstate . "): " 
                . $this->newlink->error 
            );
    }

    $ResArr = new TRMDataArray();
    // перебор всех результатов для мультизапроса, и сохранение в массив
    do
    {
        $ResArr->setRow( "result", $this->newlink->store_result() );
        $this->LastId = $this->newlink->insert_id;

        if( $this->newlink->insert_id ) { $ResArr->setRow( "insert_id", $this->newlink->insert_id ); }
    }while( $this->newlink->more_results() && $this->newlink->next_result() );
    
    return $ResArr;
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
public function getTableColumnsInfo($TableName, $ExtendFlag = false)
{
    if(!$ExtendFlag)
    {
        $Result = $this->newlink->query("SHOW COLUMNS FROM `{$TableName}`");
    }
    else
    {
        $Result = $this->newlink->query("SELECT *
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME LIKE '{$TableName}'");
    }
    if( !$Result )
    {
        throw new TRMSqlQueryException( __METHOD__ 
                . " Ощибка получения схемы для таблицы [{$TableName}]"
                . " (#" . $this->newlink->errno . "): " . $this->newlink->error);
    }
    return $this->fetchAll($Result);
}

/**
 * получает массив данных для результата запроса $res,
 * это полифил, в версии PHP < 5.3 не работал \mysqli_result::fetch_all
 * 
 * @param mysqli_result $res - объект с результатом выполненя SQL-запроса через mysqli_result::query
 * @param int $stat - в каком виде получать результат (MYSQLI_NUM - нумерованный массив, MYSQLI_ASSOC - массив с индексами как поля в таблице БД, MYSQLI_BOTH - вернутся оба варианта)
 * @return array - массив строк с данными запроса
 */
public function fetchAll($res, $stat = MYSQLI_ASSOC)
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
