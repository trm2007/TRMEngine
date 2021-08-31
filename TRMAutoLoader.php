<?php

namespace TRMEngine;

use TRMEngine\Exceptions\TRMException;

/**
 *  класс для авто-подключения,
 *  список с используемыми классами и место их расположения передаются в виде массива...
 * 
 * @author TRM - 2018
 */
class TRMAutoLoader
{
    /**
     * @type array - массив классов для подключения [ ClassName => Path ]
     */
    private static $MyClasses = array();

    /**
     * Автоматически подключает все классы TRMEngine
     */
    public function __construct($CurrentRootDirectory = "")
    {
        spl_autoload_register(array($this, "loadClass"));
        // ******************************************************************
        // ****** С 30.08.2021 работает стандвртный composer autoloder ******
        // ******************************************************************
        // // общие классы для работы TRMEngine
        // $this->setClassArray( require __DIR__ . "/config/classespath.php" );
    }

    /**
     * функция загрузки класса, она должна быть передана в качестве callback-а в spl_autoload_register
     * 
     * @param string $class - имя класса
     */
    public function loadClass($class)
    {
        if (!isset(self::$MyClasses[$class])) {
            throw new TRMException("Для класса $class не указан php-файл!");
        }
        $Path = self::$MyClasses[$class];
        if (!is_file($Path)) {
            throw new TRMException("Путь $Path не является файлом!");
        }
        require_once($Path);
    }

    /**
     * добавляет один класс для автозагрузки как пара - ( имя класса => путь к фалу .php )
     * 
     * @param string $name - имя класса
     * @param string $path - полный путь к php файлу в системе
     * 
     * @throws TRMException
     */
    public function addAddClass($name, $path)
    {
        if (!is_string($name)) {
            throw new TRMException("В качестве имен классов для автозагрузки можно задавать только строковые значения!");
        }
        self::$MyClasses[$name] = $path;
    }

    /**
     * функция добавления массива классов для автозагрузки,
     * присутсвующие уже в массиве пути для классов будут перезаписаны,
     * если в новом массиве $VarsArray встретится такое же имя...
     * 
     * @param array $VarsArray - массив array( ClassName => ClassPath, ... )
     * 
     * @throws TRMException
     */
    public function setClassArray(array $VarsArray)
    {
        self::$MyClasses = array_replace(self::$MyClasses, $VarsArray);

        if (empty(self::$MyClasses)) {
            throw new TRMException("Не удалось добавить новые классы из массива для автозагрузки!");
        }
    }
} // TRMAutoLoader