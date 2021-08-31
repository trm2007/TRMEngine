<?php

/* 
 * абстрактный класс реализующий шаблон проектирования Singleton
 */
trait TRMSingleton
{
/**
 * @var TRMSingleton - экземпляр данного объекта Singleton
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


} // TRMSingleton