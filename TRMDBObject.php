<?php

namespace TRMEngine;

use TRMEngine\Exceptions\TRMSqlQueryException;
use TRMEngine\Helpers\TRMLib;

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
		TRMLib::dp( __METHOD__ . " Файл с настройками получить на удалось [{$filename}]!" );
		return false;
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
		TRMLib::dp( __METHOD__ . " Массив конфигурации данных пустой!" );
		return false;
	}

	static::$ConfigArray = $arr;

	return true;
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

static $QB;


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
    static::$newlink->set_charset( isset(static::$ConfigArray["dbcharset"]) ? static::$ConfigArray["dbcharset"] : static::TRM_DB_DEFAULT_CHARSET );

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
 * соибирает номера всех записей, у которых поле для родительского элемента имеет имя $ParentFieldName,
 * далее, рекурсивно вызывается для каждого найденного, тем самым собирая дочерние элементы дочерних элементов
 * результирующие (возвращенные) массиывы соединяются в один
 * проходит все дерево вниз от заданной StartId
 * 
 * @param int $StartId - первый ID, от которого начинается выборка по дереву
 * @param string $TableName - таблица, из которой производится выборка
 * @param string $IdFieldName - имя поля с ID записей
 * @param string $ParentFieldName - имя поля, которое соержти дочернее ID
 * @param string $OrderFieldName - имя поля, по которому производится сортировка (ВНИМАНИЕ! сортировка в рамках выборки одного ID)
 * @param string $PresentFieldName - если не null, тогда слжержит имя поля-флага, которое проверятся на наличие данных,
 * для добавления к результирующему массиву этой записи ее поле $PresentFieldName должно быть обязательно не пустым, оличным от 0, и не NULL
 * @param boolean $first - при пользовательском вызове этот флаг по умолчанию = true, т.е. первый рекурсивный вызов,
 * он позволяет добавить передаваемый $StartId в первый элемент результирующего массива
 * 
 * @return array - одномерный массив всех дочерних ID из поля $IdFieldName
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
        //TRMLib::dp( __METHOD__ . "Запрос неудачный [{$query}]" );
        return null;
    }
    if( $result1->num_rows == 0 )
    {
        return $allgroups;
    }

    // добавляем каждый дочерний элемент в массив (если его там нет) и рекурсивно вызываем для него эту функцию
    while ($row1 = $result1->fetch_array(MYSQLI_ASSOC)) 
    {
        // если очередной ID уже есть в массиве, значит в структуре зацикливание, пропускаем его
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
