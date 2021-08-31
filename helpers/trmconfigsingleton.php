<?php

/* 
 * абстрактный класс реализующий Singleton с массивом конфигурационных данных
 *
 * @author TRM
 */
trait TRMConfigSingleton
{
/**
 * @var array - конфигурационные данные
 */
protected static $ConfigArray = array();

/**
 * загружает конфигурационные данные из файла $filename - должны возвращаться в виде массива
 *
 * @param string - имя файла с конфигурацией
 */
public static function setConfig( $filename )
{
	if( !is_file($filename) )
	{
		TRMLib::dp( __METHOD__ . " Файл с настройками получить на удалось [{$filename}]!" );
		return false;
	}
	self::$ConfigArray = require_once($filename);

	if( !is_array(self::$ConfigArray) || empty(self::$ConfigArray) )
	{
		TRMLib::dp( __METHOD__ . " Файл конфигурации вернул неверный формат данных [{$filename}]!" );
		return false;
	}

	return true;
}

} // TRMConfigSingleton