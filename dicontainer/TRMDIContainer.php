<?php

namespace TRMEngine\DiContainer;

use TRMEngine\DiContainer\Exceptions\TRMDiCanNotCreateObjectException;
use TRMEngine\DiContainer\Exceptions\TRMDiClassNotFoundedException;
use TRMEngine\DiContainer\Exceptions\TRMDiNoDefaultArgsException;
use TRMEngine\DiContainer\Exceptions\TRMDiNotPublicConstructorException;

/**
 * Depended injection container
 * глобальный контейнер хранения объектов типа Singletone
 * и для вндрениея зависимостей,
 * создается как singletone
 */
class TRMDIContainer
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


private static $FuncName = array( "__construct", "getInstance" );
    /**
 * @var array - массив для статических объектов - фактически singletone
 */
private static $Container = array();
/**
 * @var array - массив с аргументами конструктора для создаваемых объектов 
 */
private static $Params = array();

/**
 * Проверяет, есть ли объект типа $classname в контейнере статических объектов
 * 
 * @param string $classname
 */
public static function has( $classname )
{
    return array_key_exists( $classname, self::$Container );
}

/**
 * создаем статический объект, фактически singletone, 
 * если он уже был ранее запрощен, то возвращается из контейнера
 * 
 * @param string $classname - имя класса создаваемого объекта
 * @param array $params - аргументы конструктора
 * 
 * @return Object - созданный объект
 */
public static function get( $classname, array $params = array() )
{
    // если объект данного класса присутствует уже в контейнере, возвращаем его
    if(isset(static::$Container[$classname]))
    {
        return static::$Container[$classname];
    }
    // иначе берем новый и помещаем созданный объект в контейнер
    return static::$Container[$classname] = static::getNew($classname, $params);
}

/**
 * устанавливает значения аргументов с которыми будет создан объект, 
 * если они не будут указаны вторым параметром в методе get
 * 
 * @param string $classname - имя класса объекта, для которого устанавливаются аргументы
 * @param array $params - аргументы для конструктора при создании нового объекта
 */
public static function set( $classname, array $params = array() )
{
    static::$Params[$classname] = $params;
}

/**
 * создает новый объект класса $classname, не помещая его в контейнер, 
 * при каждом вызове, будует создан новый объект типа $classname
 * 
 * @param string $classname - имя класса создаваемого объекта
 * @param array $params - аргументы конструктора
 * 
 * @return \TRMEngine\DiContainer\classname
 * 
 * @throws TRMDiClassNotFoundedException
 * @throws TRMDiNotPublicConstructorException
 * @throws TRMDiCanNotCreateObjectException
 */
public static function getNew( $classname, $params = array() )
{
    // проверяем наличие класса $classname в нашем приложении,
    // если его нет, то выбрасываем исклюсение
    if(!class_exists($classname))
    {
        throw new TRMDiClassNotFoundedException( $classname );
    }

    $NewObj = null;
    $nonpublicconstruct = false;
    // класс присутствует, 
    // значит проверяем наличие конструктора, 
    // либо другой функции, которая может создать запрашиваемый объект
    // список имен функций хранится в статическом массиве static::$FuncName,
    // например, "__construct", "getInstance"
    foreach( static::$FuncName as $funcname )
    {
        if(method_exists($classname, $funcname ) ) // "__construct"))
        {
            // получаем объект описывающий класс $classname
            $RefObj = new ReflectionClass($classname);
            // проверяем, если метод $funcname в объектах типа $classname не публичный, 
            // то вызвать его не получится, тогда продолжаем цикл дальше
            if( !($RefObj->getMethod($funcname)->isPublic()) )
            {
                // если конструктор не публичный, то ставим соответсвующий флаг
                if( $funcname == "__construct" ) { $nonpublicconstruct = true; }
                continue;
            }

            // если не переданы параметры-аргументы конструктора, с которыми нужно создать объект, 
            // то проверяем аргументы в установках (задаются в конфигурации объектов методом set)
            if( empty($params) )
            {
                // если найдены установки для $classname, то берем их
                if( isset(static::$Params[$classname]) ){ $params = static::$Params[$classname]; }
                // иначе пытаемся создать класс с аргументами поумолчанию, 
                // создавая требуемые в аргументах очередного конструктора объекты классов рекурсивно
                else { $params = static::getMethodParams($classname, $funcname); }// получаем массив значений для аргументов метода "__construct"
            }

            // создаем и возвращаем объект типа $classname с аргументами $params
            if( !$nonpublicconstruct ) { $NewObj = $RefObj->newInstanceArgs($params); }
            // если конструктор недоступен, значит создаем, вызывая другой метод
            else { $NewObj = call_user_func_array( array($classname, $funcname), $params ); }

            // если объект создан как экземпляр $classname, прерываем цикл
            if( $NewObj instanceof $classname ) { break; }
        }
    }
    // если объект еще не создан
    if(!$NewObj)
    {
        if( $nonpublicconstruct )
        {
            throw new TRMDiNotPublicConstructorException( $classname );
        }
        // если нет конструктора, и методом для получения экземпляра объекта, 
        // то просто создаем объект оператором new без параметров
        if( !($NewObj = new $classname) )
        {
            throw new TRMDiCanNotCreateObjectException( $classname );
        }
    }
    // если установлены аргументы, 
    // то пытаемся их установить вызывая соответсующие функции-сеттеры,
    // если они есть у созданного объекта
    if( isset(static::$Params[$classname]) )
    {
        static::tryToSetParams($NewObj, static::$Params[$classname]);
    }

    return $NewObj;
}

/**
 * создаем статический объект, фактически singletone, 
 * если он уже был ранее запрощен, то возвращается из контейнера
 * 
 * @param string $classname - имя класса создаваемого объекта
 * @param array $params - аргументы конструктора
 * 
 * @return Object - созданный объект
 */
public static function getStatic( $classname, array $params = array() )
{
    return static::get($classname, $params);
}

/**
 * создает массив с аргументами поумолчанию, 
 * либо вместе с созданными объектами, если такие требуются в методе $methodname
 * и их удалось создать
 * 
 * @param string $classname
 * @param string $methodname
 * 
 * @return array - массив с необходимыми для метода $methodname аргументами
 * 
 * @throws TRMDiNoDefaultArgsException - если в аргументах встречается примитив без значения поумолчания, то выбрасывается исключение
 */
private static function getMethodParams( $classname, $methodname )
{
    $reflect = new ReflectionMethod($classname, $methodname);
    $params = $reflect->getParameters();

    $args = array();
    // проходимся в цикле по все параметрам необходимым для создания объекта запрашиваемого класса
    foreach ($params as $key => $param)
    {
        // если у очередного параметра есть значение по умолчанию, которое передается в конструктор, то получаем его
        if($param->isDefaultValueAvailable())
        {
            $args[$param->name] = $param->getDefaultValue();
        }
        else
        {
            $tmpclass = $param->getClass();
            // если нет класса, то это скорее всего примитив (string, int , boolean, или что-то похожее), а для них нужно обязательно задавать значения
            if($tmpclass === NULL)
            {
                throw new TRMDiNoDefaultArgsException(" [{$param->name}] в методе [{$methodname}] объекта [{$classname}] ");
            }
            //если найден класс очередного параметра в конструкторе, то создаем этот объет, 
            //вызывая для него наш метод get(...)
            $args[$param->name] = static::get($tmpclass->name);
        }
    }

    return $args;
}

/**
 * пытается найти сеттеры для параметров объекта $object в массиве $params 
 * и вызвать их передавая значниея из массива
 * имя сеттера ищется по шаблону setXXXXXX -  "set" . $paramname
 * 
 * @param object $object - объект, для которого производятся попытки вызвать сеттеры
 * @param array $params - массив параметров для поиска - array( $paramname => $paramvalue, ... )
 */
private static function tryToSetParams( $object, array $params )
{
    foreach( $params as $paramname => $paramvalue )
    {
        $methodname = "set" . $paramname;
        if( method_exists($object, $methodname) )
        {
            $object->$methodname($paramvalue);
        }
    }
}

} // TRMDIContainer
