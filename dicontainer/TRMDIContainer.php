<?php

namespace TRMEngine\DiContainer;

use TRMEngine\DiContainer\Exceptions\TRMDiClassNotFoundedException;
use TRMEngine\DiContainer\Exceptions\TRMDiExceptions;
use TRMEngine\DiContainer\Interfaces\TRMSimpleFactoryInterface;

/**
 * Depended injection container
 * глобальный контейнер хранения объектов типа Singletone
 * и для вндрениея зависимостей,
 * создается как singletone
 */
class TRMDIContainer
{
/**
 * @var array - массив с именами фцнкций, которые могут вызываться для создания объекта
 */
protected $FuncName = array( "__construct", "getInstance", "instance" );
/**
 * @var array - массив для статических объектов в контейнере
 */
protected $Container = array();
/**
 * @var array - массив соответсвий ( "название сервиса" => "тип класса, который за него отвечает" )
 */
protected $LocatorArray = array();
/**
 * @var array - массив с начальными переметрами для создаваемых объектов,
 * передаются как аргументы конструктора, если он не доступен, то 
 * будет предпринята попытка передать через другие функции,
 * если аргументов у функйи нет, то  через соответствующие сеттеры,
 * если в массиве в качетсве ключей указаны имена аргументов: 
 * array( "ArgName" => "ArgValue", ... )
 */
protected $Params = array();
/**
 * @var array - массив с объектами фабрик, которые могут создавать объекты разных типов,
 */
protected $Factories = array();


/**
 * @param boolean $DefaulFactoryFlag - флаг по умолчанию укзывающий, 
 * что будет создана фабрика по умолчанию TRMStaticFactory 
 * и она будет помещена в контейнер 
 */
public function __construct($DefaulFactoryFlag = true)
{
    // при создании, помещаем в контейнер экземпляр данного объект TRMDIContainer
    $this->Container[get_class($this)] = $this;
    // а так же создается объект фабрики и помещается в контейнер, если задан флаг
    if($DefaulFactoryFlag)
    {
        $this->Container[TRMStaticFactory::class] = new TRMStaticFactory($this);
    }
}

/**
 * Проверяет, есть ли объект типа $ClassName в контейнере уже созданных объекто,
 * для $ClassName есть соответсвие другого типа объектов и он может быть создан,
 * либо для $ClassName задана фабрика, которая создает объект
 * 
 * @param string $ClassName
 */
public function has( $ClassName )
{
    if( array_key_exists( $ClassName, $this->Container ) ) { return true; }
    if( array_key_exists( $ClassName, $this->LocatorArray ) ) { return true; }
    if( array_key_exists( $ClassName, $this->Factories ) ) { return true; }
     
     return false;
}

/**
 * создаем статический объект, фактически singletone, 
 * если он уже был ранее запрощен, то возвращается из контейнера
 * 
 * @param string $ClassName - имя класса создаваемого объекта
 * 
 * @return mixed - созданный объект
 */
public function get( $ClassName )
{
    // если объект данного вида/класса уже присутствует в контейнере, возвращаем его
    if( isset($this->Container[$ClassName]) )
    {
        return $this->Container[$ClassName];
    }
    $Params = array();
    if( isset($this->Params[$ClassName]) ) { $Params = $this->Params[$ClassName]; }
    // если запрошен объект по имени 
    // и соответсвие его типа есть в $LocatorArray,
    // создаем объект этого типа
    if( isset($this->LocatorArray[$ClassName]) )
    {
        // в данном случае $ClassName - это не имя класса, 
        // а какой-то заданный пользователем индекс для объектов,
        // это может быть название интерфейса, объекты которого созданы быть не могут,
        // и этот интерфейс реализует объект определенного типа,
        // который хранится в LocatorArray под индексом $ClassName, 
        // пытаемся теперь получить из контейнера объект нового типа
        
        //сначала проверяем параметры, если пустые, 
        //то пытаемся установить параметры нового класса
        if( empty($Params) && isset($this->Params[$this->LocatorArray[$ClassName]]) )
        {
            $Params = $this->Params[$this->LocatorArray[$ClassName]];
        }
        return $this->get($this->LocatorArray[$ClassName], $Params);
    }
    // проверяем есть ли фабрика для таких объектов
    // если есть, то создаем объект через фабрику передав параметры
    // и сохраняем его в контейнере
    if( isset($this->Factories[$ClassName]) )
    {
        return $this->Container[$ClassName] = $this->Factories[$ClassName]->create($Params);
    }
    // если объект еще не создан,
    // но в контейнере есть фабрика по умолчанию TRMStaticFactory,
    // то создаем через нее
    if( isset($this->Container[TRMStaticFactory::class]) )
    {
        return $this->Container[$ClassName] = $this->Container[TRMStaticFactory::class]->create($ClassName, $Params);
    }
    
    // иначе возвращает null
    return null;
}

/**
 * устанавливает значения аргументов с которыми будет создан объект, 
 * если они не будут указаны вторым параметром в методе get
 * 
 * @param string $ClassName - имя класса объекта, для которого устанавливаются аргументы
 * @param array $Params - аргументы для конструктора при создании нового объекта
 */
public function setParams( $ClassName, array $Params = array() )
{
    $this->Params[$ClassName] = $Params;
}

/**
 * Добавляет фабрику объектов для типа $ClassName, 
 * объект $Factory добавляется в контейнер
 * 
 * @param string $ClassName - имя класса
 * @param object $Factory - фабрика объектов
 * 
 * @throws TRMDiExceptions
 */
public function setFactory($ClassName, TRMSimpleFactoryInterface $Factory)
{
    if( !$Factory )
    {
        throw new TRMDiExceptions("Передан пустой объект фабрики для {$ClassName}");
    }
    $this->Factories[$ClassName] = $Factory;
    $this->set( $Factory );
}

/**
 * Задает соответсвие имени (ключ, индекс) $Name и 
 * классу объектов, которые будут предоставлены по запросу по этому названию
 * 
 * @param string $Name
 * @param string $ClassName
 */
public function register($Name, $ClassName)
{
    $this->LocatorArray[$Name] = $ClassName;
}

/**
 * Помещает уже созданный объект в контейнер,
 * псевдоним для set($Object)
 * 
 * @param mixed $Object
 */
public function add($Object)
{
    $this->set($Object);
}
/**
 * Помещает уже созданный объект в контейнер
 * 
 * @param mixed $Object
 */
public function set($Object)
{
    $ClassName = get_class($Object);
    if(!$ClassName)
    {
        throw new TRMDiClassNotFoundedException(" Класс объекта опредилть не удалось! ");
    }
    $this->Container[$ClassName] = $Object;
}


} // TRMDIContainer
