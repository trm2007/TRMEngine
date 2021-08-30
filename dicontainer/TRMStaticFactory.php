<?php

namespace TRMEngine\DiContainer;

use TRMEngine\DiContainer\Exceptions\TRMDiCanNotCreateObjectException;
use TRMEngine\DiContainer\Exceptions\TRMDiClassNotFoundedException;
use TRMEngine\DiContainer\Exceptions\TRMDiNoDefaultArgsException;
use TRMEngine\DiContainer\Exceptions\TRMDiNotPublicConstructorException;
use TRMEngine\DiContainer\Interfaces\TRMStaticFactoryInterface;

/**
 * фабрика для создания объектов заданного типа через контейнер зависимости,
 * 
 */
class TRMStaticFactory implements TRMStaticFactoryInterface
{
/**
 * @var array - массив с именами фцнкций, которые могут вызываться для создания объекта
 */
protected $FuncName = array( "__construct", "getInstance", "instance" );
/**
 * @var TRMDIContainer 
 */
protected $DIC;


public function __construct(TRMDIContainer $DIC)
{
    $this->DIC = $DIC;
}

/**
 * Создает новый объект класса $ClassName с параметрами $Params
 * 
 * @param string $ClassName
 * @param array $Params
 * 
 * @return $ClassName - созданый объект
 * 
 * @throws TRMDiClassNotFoundedException
 */
public function create($ClassName, array $Params = array())
{
    // проверяем наличие класса $ClassName в нашем приложении,
    // если его нет, то выбрасываем исклюсение
    if (!class_exists($ClassName))
    {
        throw new TRMDiClassNotFoundedException("Не наден класс {$ClassName}");
    }

    return $this->getNew($ClassName, $Params);
}

/**
 * создает новый объект класса $ClassName, не помещая его в контейнер, 
 * при каждом вызове, будует создан новый объект типа $ClassName
 * 
 * @param string $ClassName - имя класса создаваемого объекта
 * @param array $Params - аргументы конструктора
 * 
 * @return $ClassName
 * 
 * @throws TRMDiNotPublicConstructorException
 * @throws TRMDiCanNotCreateObjectException
 */
protected function getNew( $ClassName, $Params = array() )
{
    $NewObj = null;
    $NoPublicConstructorFlag = false;
    $RefObj = null;
    $NewParams = null;
    // класс присутствует, 
    // далее проверяем наличие конструктора, 
    // либо другой функции, которая может создать запрашиваемый объект
    // список имен функций хранится в статическом массиве $this->FuncName,
    // например, "__construct", "getInstance"
    foreach( $this->FuncName as $funcname )
    {
        if(!method_exists($ClassName, $funcname ) )
        {
            continue;
        }
        // получаем объект описывающий класс $ClassName
        // с проверкой, что бы не создавать объект рефлексии 
        // для одного и тогоже класса дважды
        if( !$RefObj )
        {
            $RefObj = new \ReflectionClass($ClassName);
        }

        // проверяем, если метод $funcname в объектах типа $ClassName не публичный, 
        // то вызвать его не получится, тогда продолжаем цикл дальше
        if( !($RefObj->getMethod($funcname)->isPublic()) )
        {
            // если конструктор не публичный, то ставим соответсвующий флаг
            if( $funcname == "__construct" ) { $NoPublicConstructorFlag = true; }
            continue;
        }

        // передаем имеющиемя параметры,
        // вернется дополненный массив аргументов, 
        // если в $Params заданы не все требуемые аргументы, 
        // либо если он пустой
        $NewParams = $this->getMethodParams($ClassName, $funcname, $Params);

        // если конструктор доступен, то создаем объект типа $ClassName с аргументами $Params
        if( !$NoPublicConstructorFlag ) { $NewObj = $RefObj->newInstanceArgs($NewParams); }
        // если конструктор недоступен, значит создаем, вызывая другой метод
        else { $NewObj = call_user_func_array( array($ClassName, $funcname), $NewParams ); }

        // если объект создан как экземпляр $ClassName, прерываем цикл
        if( $NewObj instanceof $ClassName ) { break; }
    }
    // если объект еще не создан
    if(!$NewObj)
    {
        // если есть конструктор и он не публичный, 
        // и на этом этапе все известные функции для создания не сработали
        if( $NoPublicConstructorFlag )
        {
            throw new TRMDiNotPublicConstructorException( $ClassName );
        }
        // если нет конструктора, и методом для получения экземпляра объекта, 
        // создать его не удалось, 
        // то просто пробуем создать объект оператором new без параметров
        if( !($NewObj = new $ClassName) )
        {
            throw new TRMDiCanNotCreateObjectException( $ClassName );
        }
    }
    // на этом этапе создать объект удалось, теперь...
    // если параметры по умолчанию еще не заданы,
    // но переданы параметры в $Params, то устанавливаем их
    if( !$NewParams )
    {
        if( empty($Params) )
        {
            return $NewObj;
        }
        $NewParams = &$Params;
    }
    // пытаемся их установить вызывая соответсующие функции-сеттеры в tryToSetParams,
    // если они есть у созданного объекта
    $this->tryToSetParams($NewObj, $NewParams);

    return $NewObj;
}

/**
 * создает массив с аргументами по умолчанию, 
 * либо вместе с созданными объектами, если такие требуются в методе $methodname
 * и их удалось создать
 * 
 * @param string $ClassName
 * @param string $methodname
 * @param array $userparams - массив по ссылке с установленными из вне 
 * пользовательскими значеними аргументов, они останутся не тронутыми
 * 
 * @return array - массив с необходимыми для метода $methodname аргументами:
 * array( "ParamName" => "ParamValue", ... )
 * 
 * @throws TRMDiNoDefaultArgsException - если в аргументах встречается примитив 
 * без значения поумолчания, то выбрасывается исключение
 */
protected function getMethodParams( $ClassName, $methodname, array &$userparams = null )
{
    $reflect = new \ReflectionMethod($ClassName, $methodname);
    $Params = $reflect->getParameters();

    $args = array();
    // счетчик номера параметра
    $k = 0;
    // проходимся в цикле по все параметрам необходимым для создания объекта запрашиваемого класса
    foreach ($Params as $param)
    {
        $k++;
        // если такой параметр с таким ключом (или аргумент под текущим номером) 
        // уже установлен в $userparams, то оставляем его
        if( isset($userparams[$param->name]) )
        {
            $args[$param->name] = $userparams[$param->name];
            continue;
        }
        if( isset($userparams[$k-1]) )
        {
            $args[$param->name] = $userparams[$k-1];
            continue;
        }
        // если параметр может быть null, 
        // проверяем это перед значениями по умолчанию
        if( $param->allowsNull() )
        {
            $args[$param->name] = null;
            continue;
        }
        // если у очередного параметра есть значение по умолчанию, 
        // которое передается в метод, то получаем и сохраняем его
        if($param->isDefaultValueAvailable())
        {
            $args[$param->name] = $param->getDefaultValue();
            continue;
        }
        $tmpclass = $param->getClass();
        // если нет класса, то это скорее всего примитив (string, int , boolean, или что-то похожее), 
        // для них нужно обязательно задавать значения
        if($tmpclass === NULL)
        {
            throw new TRMDiNoDefaultArgsException(" [{$param->name}] в методе [{$methodname}] объекта [{$ClassName}] ");
        }
        // если определен класс очередного параметра функции, 
        // то пытаемся получить объект этого класса, 
        // вызывая для него метод DIContainer-a get(...)
        // Если его езе нет в контейнере, то будет вызвана фабрики классов
        // (получается рекурсия)
        $args[$param->name] = $this->DIC->get($tmpclass->name);
        if( !$args[$param->name] )
        {
            throw new TRMDiNoDefaultArgsException(" [{$param->name}] типа [{$tmpclass->name}] в методе [{$methodname}] объекта [{$ClassName}] ");
        }

    }

    return $args;
}

/**
 * пытается найти сеттеры для параметров объекта $object в массиве $Params 
 * и вызвать их передавая значниея из массива
 * имя сеттера ищется по шаблону setXXXXXX -  "set" . $paramname
 * 
 * @param object $object - объект, для которого производятся попытки вызвать сеттеры
 * @param array $Params - массив параметров для поиска - array( $paramname => $paramvalue, ... )
 */
protected function tryToSetParams( $object, array $Params )
{
    foreach( $Params as $paramname => $paramvalue )
    {
        $methodname = "set" . $paramname;
        if( method_exists($object, $methodname) )
        {
            $object->$methodname($paramvalue);
        }
    }
}


} // TRMStaticFactory
