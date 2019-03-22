<?php

namespace TRMEngine\Cache;

use TRMEngine\DiContainer\TRMDIContainer;

/**
 *  ����� ��� ����������� ������ � ����� �� ����� �� �������� ������-������ ������
 */
class TRMCache 
{
/**
 * @var TRMDIContainer - ������������ ��������� ������� ���� TRMDIContainer
 */
protected static $instance;


/**
 * ��������� �����������, 
 * ����� ������ ���� ������� ������ ����� new
 */
protected function __construct(){}

/**
 * ���������� ������ ������, ������������ �� TRMSingletone,
 * � ����������� ������ ���� ��������� ����������� ���������� �������� - $instance
 * 
 * @return Object
 */

public static function getInstance()
{
    if(!isset(static::$instance)) { static::$instance = new static; }
    return static::$instance;
}


/**
 * @var string - ��� �����, � ������� ����� �������� �������������� ������, 
 * ����������� ��� ������� ������ ������ �� ������ �� �����
 */
protected $CacheFileName;
/**
 * @var string - ���������� ������ � ���� ������
 */
protected $DataToCache;
/**
 * @var int - �����, ������� ������������ ������ ���-�����, ��� ������ ���������� � ������������, 
 * � ����� ��� ������ �������� ��������� ������ ����� ��� ����� � ��� �� ������!
 */
protected $CacheRewriteTime;
/**
 * @var string - ����-������� � ���-������
 */
protected $CahePath = "/cache";


/**
 * ���������� �������������� ������, ���� ��� ���� � ����� �� �������
 * 
 * @param string $key - ���� �������������� ������
 * @return string - ���������� ���-����� � �������, ��� null
 */
public function getCache($key)
{
    $this->makeFilePath($key);
    // ��������� ���������� �� ����, ���� ��, �� ����� ��������� ����� ������ ���������� �� ����� ��� �� 120 ������ �� ��������
    // ����� ������ ���������� �� �����
    if( !file_exists($this->CacheFileName) 
        || ( $this->CacheRewriteTime > 0 && (time() - filemtime($this->CacheFileName)) > $this->CacheRewriteTime ) )
    {
        return null;
    }

    $this->DataToCache = file_get_contents($this->CacheFileName);
    return $this->DataToCache;
}

/**
 * �������� ������ � ���-���
 * 
 * @param string $key - ���� - ������������ ������
 * @param string $data - ������ � ���� ������ ��� �����������
 * 
 * @return int - ������ ��������� ������ � ������
 */
public function setCache($key, $data)
{
    $this->DataToCache = $data;
    $this->makeFilePath($key);
    return file_put_contents($this->CacheFileName, $this->DataToCache);	
}

/**
 * @param int $time - ����� � ��������, � ������� �������� ������ �� ���������������� � ���
 */
public function setCacheRewriteTime($time)
{
    $this->CacheRewriteTime = intval($time);
}

/**
 * 
 * @return int - ����� � ��������, � ������� �������� ������ �� ���������������� � ���
 */
public function getCacheRewriteTime()
{
    return $this->CacheRewriteTime;
}

/**
 * ������������� ����-������� ��� ����������� ������ � ����
 * 
 * @param string $dir - ���� � ���-������
 */
public function setCachePath($dir)
{
    $this->CahePath = rtrim($dir, "/");
}

/**
 * @return string - ����-�������, ������������� ��� ����������� ������ � ����
 */
public function getCachePath()
{
    return $this->CahePath;
}

/**
 * ��������� ���������� ��� ��� ����� ���� �� ����� ������ � ������ �����
 * @param string $key - ���� (���) �������
 * 
 * @return string - ���������� ��� ����� $key ��� ����� � ���� � ����
 */
protected function makeFilePath($key)
{
    return $this->CacheFileName = $this->CahePath . "/" . md5(strtolower($key)) . ".tch";
}


} // TRMCache