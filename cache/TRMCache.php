<?php

namespace TRMEngine\Cache;

use TRMEngine\DiContainer\TRMDIContainer;

/**
 *  класс для кэширования данных в файлы на диске по заданным ключам-именам данных
 */
class TRMCache 
{
/**
 * @var TRMDIContainer - ндинственный экземпляр объекта типа TRMDIContainer
 */
protected static $instance;


/**
 * приватный конструктор, 
 * чтобы нельзя было создать объект через new
 */
protected function __construct(){}

/**
 * возвращает объект класса, наследуемого от TRMSingletone,
 * у наследников должно быть объявлено статическое приватоное свойство - $instance
 * 
 * @return Object
 */

public static function getInstance()
{
    if(!isset(static::$instance)) { static::$instance = new static; }
    return static::$instance;
}


/**
 * @var string - имя файла, в который будут записаны закэшированные данные, 
 * формируется для каждого набора данных на основе их ключа
 */
protected $CacheFileName;
/**
 * @var string - кэшируемые данные в виде строки
 */
protected $DataToCache;
/**
 * @var int - время, которое ограничивает возрас кэш-файла, оно всегда передается в конструкторе, 
 * и можно для разных объектов проверять разное время для одних и тех же данных!
 */
protected $CacheRewriteTime;
/**
 * @var string - путь-каталог в кэш-файлам
 */
protected $CahePath = "/cache";


/**
 * возвращает закэшированные данные, если они есть и время не истекло
 * 
 * @param string $key - ключ закэшированных данных
 * @return string - содержимое кэш-файла с данными, или null
 */
public function getCache($key)
{
    $this->makeFilePath($key);
    // проверяем существует ли файл, если да, то время созднания файла должно отдичаться не более чем на 120 секунд от текущего
    // тогда читаем информацию из файла
    if( !file_exists($this->CacheFileName) 
        || ( $this->CacheRewriteTime > 0 
            && (time() - filemtime($this->CacheFileName)) > $this->CacheRewriteTime ) 
    )
    {
        return null;
    }

    $this->DataToCache = file_get_contents($this->CacheFileName);
    return $this->DataToCache;
}

/**
 * сохранем данные в кэш-фал
 * 
 * @param string $key - ключ - наименование данных
 * @param string $data - данные в виде строки для кэширования
 * 
 * @return int - размер сохраннех данных в байтах
 */
public function setCache($key, $data)
{
    $this->DataToCache = $data;
    $this->makeFilePath($key);
    return file_put_contents($this->CacheFileName, $this->DataToCache);	
}

/**
 * очищает кэш для ключа (удаляет файл с данными)
 * 
 * @param string $key - ключ - наименование данных
 */
public function clearCache($key)
{
    $this->makeFilePath($key);
    if(file_exists($this->CacheFileName))
    {
        unlink($this->CacheFileName);
    }
}

/**
 * @param int $time - время в секундах, в течении которого данные не перезаписываются в кэш
 */
public function setCacheRewriteTime($time)
{
    $this->CacheRewriteTime = intval($time);
}

/**
 * 
 * @return int - время в секундах, в течении которого данные не перезаписываются в кэш
 */
public function getCacheRewriteTime()
{
    return $this->CacheRewriteTime;
}

/**
 * устанавливает путь-каталог для кэширования данных в файл
 * 
 * @param string $dir - путь к кэш-файлам
 */
public function setCachePath($dir)
{
    $this->CahePath = rtrim($dir, "/");
}

/**
 * @return string - путь-каталог, установленный для кэширования данных в файл
 */
public function getCachePath()
{
    return $this->CahePath;
}

/**
 * формирует уникальное имя для файла кэша по ключу вместь с полным путем
 * @param string $key - ключ (имя) даннных
 * 
 * @return string - уникальное для ключа $key имя файла и путь к нему
 */
protected function makeFilePath($key)
{
    return $this->CacheFileName = $this->CahePath . "/" . md5(strtolower($key)) . ".tch";
}


} // TRMCache