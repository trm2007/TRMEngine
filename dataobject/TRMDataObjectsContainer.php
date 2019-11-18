<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Exceptions\TRMDataObjectContainerNoMainException;
use TRMEngine\DataObject\Exceptions\TRMDataObjectsContainerWrongDependenceObjectException;
use TRMEngine\DataObject\Exceptions\TRMDataObjectsContainerWrongDependenceTypeException;
use TRMEngine\DataObject\Exceptions\TRMDataObjectsContainerWrongIndexException;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMParentedCollectionInterface;
use TRMEngine\DataObject\Interfaces\TRMTypedCollectionInterface;

/**
 * класс контейнер объектов данных, используется для составных объектов.
 * 
 * Используется 
 * 1. как для коллекций объектов-детей,
 * например, для составного продукта со всеми дополнительными коллекциями и объектами,
 * которые зависят от ID-главного объекта
 * (коллекции характеристик, комплектующие продукты, доп.изображения для товара и т.д.)
 * 
 * 2. так и для коллекция зависимостей (как правило в коллекции каждого типа только одна зависимости),
 * когда есть главный объект и объекты-зависимости,
 * от которых главный объект зависит и связан с ними через их ID.
 * Сами зависимости являются автономными сущностями, например,
 * производитель никак не зависит от товара, 
 * но товар связан через свой ID_vendor и зависит от производителя по его ID...
 */
class TRMDataObjectsContainer implements TRMDataObjectsContainerInterface
{
const MAIN_INDEX = "Main";
const CHILDRENS_INDEX = "Childrens";
const DEPENDENCIES_INDEX = "Dependencies";

/**
 * @var TRMIdDataObjectInterface - основной объект с уникальным идентификатором ID,
 * по униакльному ID объекты в контейнере связываются с главным объектом
 */
protected $MainDataObject;
/**
 * @var array(TRMTypedCollectionInterface) - массив с коллекциями объектов данных, дополняющих основной объект, 
 * например коллекция характеристик, доп.изображения, комплекты, скидки и т.д.
 */
protected $ChildCollectionsArray = array();
/**
 * @var array(TRMIdDataObjectInterface) - массив объектов данных, дополняющих основной объект, 
 * например коллекция характеристик, доп.изображения, комплекты, скидки и т.д.
 */
protected $DependenciesObjectsArray = array();
/**
 * @var array - массив зависимостей, 
 * каждый элемент массива - это поименованный элемент с подмассивом,
 * содержащим имя суб-объекта в главном объекте и имя поля этого суб-объекта
 * для связи с ID-зависимости
 * (..., "ObjectIndex" => array( "RelationSubObjectName" => type, "RelationFieldName" =>fieldname ), ... )
 */
protected $DependenciesFieldsArray = array();

/**
 * @var integer - текущая позиция указателя, для реализации интерфейса итератора - Iterator
 */
private $Position = 0;


/**
 * @return TRMIdDataObjectInterface - возвращает главный (сохраненный под 0-м номером в массиве) объект данных
 */
public function getMainDataObject()
{
    return $this->MainDataObject;
}

/**
 * устанавливает главный объект данных,
 * 
 * @param TRMIdDataObjectInterface $do - главный объект данных
 */
public function setMainDataObject(TRMIdDataObjectInterface $do)
{
    $this->MainDataObject = $do;
}


/**
 * помещает объект данных с именем $Index в массив-контейнер зависимостей, 
 * сохраняется только ссылка, объект не клонируется!!!
 * 
 * @param string $Index - имя/номер-индекс, под которым будет сохранен объект в контейнере
 * @param string $dotype - тип объекта-зависимости, который будет установлен для данного объекта
 * @param string $ObjectName - имя суб-объекта в главном объекте, по которому связывается зависимость
 * @param string $FieldName - имя поля основного суб-объекта в главном объекте, 
 * по которому установлена связь зависимостью
 */
public function setDependence($Index, $dotype, $ObjectName, $FieldName )
{
    if( !class_exists($dotype) )
    {
        throw new TRMDataObjectsContainerWrongDependenceTypeException( 
                get_class($this) . " - " . __METHOD__ . " - " . $dotype 
            );
    }
    $this->DependenciesFieldsArray[$Index] = array( strval($ObjectName), strval($FieldName), $dotype );
    // если в массиве объектов-зависимостей данного типа еще нет объекта,
    // или там сохранен объект другого типа от $dotype,
    // то создаем пустой элемент массива
    if( !key_exists( $dotype, $this->DependenciesObjectsArray ) ||
        get_class($this->DependenciesObjectsArray[$dotype]) !== $dotype   )
    {
        $this->DependenciesObjectsArray[$dotype] = null;
    }
}

/**
 * помещает объект в массив-контейнер зависимостей,
 * индекс в массиве будет соответсвовать типу объекта!
 * сохраняется только ссылка, объект не клонируется!!!
 * 
 * @param TRMIdDataObjectInterface $do - объект зависимости, который устанавливается для данного объекта
 */
public function setDependenceObject( TRMIdDataObjectInterface $do )
{
    $dotype = get_class($do);
    foreach( $this->DependenciesFieldsArray as $DependenceField )
    {
        if( $DependenceField[2] === $dotype )
        {
            $this->MainDataObject->setData( $DependenceField[0], $DependenceField[1], $do->getId() );
            $this->DependenciesObjectsArray[$dotype] = $do;
            return;
        }
    }
    
    // если не установлена связь с зависимостью, то выбрасываем исключение
    throw new TRMDataObjectsContainerWrongDependenceObjectException( 
            get_class($this) . __METHOD__ . " - " . $dotype 
        );
}

/**
 * возвращает массив с именами полей зависимости с индексом $Index
 * 
 * @param string $Index - имя/номер-индекс объекта в контейнере
 * 
 * @return array - имя суб-объекта и поля в суб-объекте главного объекта, 
 * по которому установлена связь с ID зависимости под индексом $Index
 */
public function getDependenceField($Index)
{
    return isset($this->DependenciesFieldsArray[$Index]) ? $this->DependenciesFieldsArray[$Index] : null;
}
/**
 * 
 * @return array(TRMIdDataObjectInterface) - возвращает массив 
 * со всеми зависимосяти для главного объекта из контейнера
 */
public function getDependenciesObjectsArray()
{
    return $this->DependenciesObjectsArray;
}

/**
 * возвращает объект зависимости с индексом $Index из контейнера объектов
 * 
 * @param string $dotype - тип объекта-зависимости в контейнере
 * 
 * @return TRMIdDataObjectInterface - коллекция с объектами данных, сохраненная в контейнере
 */
public function getDependenceObject($dotype)
{
    if( !key_exists($dotype, $this->DependenciesObjectsArray) )
    {
        throw new TRMDataObjectsContainerWrongIndexException( get_class($this) . " - " . __METHOD__ . " - " . $dotype );
    }
    return $this->DependenciesObjectsArray[$dotype];
}

/**
 * 
 * @param string $Index - индекс объекта в контейнере
 * @return bool - если объект в контейнере под этим индексом зафиксирован как зависимый от главного,
 * например, список характеристик для товара, то вернется true, если зависимость не утсанвлена, то - false
 */
public function isDependence($Index)
{
    return key_exists($Index, $this->DependenciesFieldsArray);
}

/**
 * @return array - массив массивов с зависимостями вида:
 * array("ObjectName" => array( "RelationSubObjectName" => type, "RelationFieldName" =>fieldname ), ... )
 */
public function getDependenciesFieldsArray()
{
    return $this->DependenciesFieldsArray;
}

/**
 * очищает массив с доп. объектами данных,
 * так же у этих объектов обнуляет ссылку на этот родительский контейнер
 */
public function clearDependencies()
{
    $this->DependenciesFieldsArray = array();
    $this->DependenciesObjectsArray = array();
}


/**
 * 
 * @param TRMTypedCollectionInterface $Collection - коллекция, 
 * для каждого объекта которой нужно установить родителем данный объект контейнера
 */
public function setParentFor(TRMTypedCollectionInterface $Collection, TRMIdDataObjectInterface $Parent)
{
    foreach( $Collection as $Object )
    {
        $Object->setParentDataObject($Parent);
    }
}

/**
 * помещает коллекцию дочерних объект данных в массив под номером $Index, 
 * сохраняется только ссылка, объекты не клонируются!!!
 * 
 * @param string $Index - номер-индекс, под которым будет сохранен объект в контейнере
 * @param TRMParentedCollectionInterface $Collection - добавляемый объект-коллекция
 */
public function setChildCollection($Index, TRMParentedCollectionInterface $Collection) // был TRMParentedDataObject, но позже сделал для все объектов данных
{
    $this->ChildCollectionsArray[$Index] = $Collection;
    $this->setParentFor($Collection, $this);
}

/**
 * возвращает объект из контейнера под номером $Index
 * 
 * @param integer $Index - номер объекта в контейнере
 * 
 * @return TRMParentedCollectionInterface - коллекция из контейнера
 */
public function getChildCollection($Index)
{
    if( isset($this->ChildCollectionsArray[$Index]) ) { return $this->ChildCollectionsArray[$Index]; }
    return null;
}

/**
 * @return array - возвращает массив объектов данных, дополняющих основной объект
 */
public function getChildCollectionsArray()
{
    return $this->ChildCollectionsArray;
}

/**
 * очищает массив с доп. объектами данных,
 * так же у этих объектов обнуляет ссылку на этот родительский контейнер
 */
public function clearChildCollectionsArray()
{
    // так как в массиве хранятся ссылки на реальные объекты, то они не удаляются при опустошении массива,
    // поэтому вручную устанавливаем для каждого объекта данных родителя в null, 
    // чтобы они не ссылались на контейнер из которого они удалены
    foreach( $this->ChildCollectionsArray as $Collection )
    {
        $this->setParentFor( $Collection, null );
    }
    $this->ChildCollectionsArray = array();
}

/**
 * @return array - вернет массив из двух элементов вида :
 * array(
 * "Main" => данные главного объекта,
 * "Childrens" => array(
 *      "NameOfChild1" => TypedCollection1,
 *      "NameOfChild2" => TypedCollection1,
 *      "NameOfChild3" => TypedCollection1,
 *      ...
 *      )
 * "Dependencies" => array(
 *      "NameOfDependence1" => IdDataObject1,
 *      ... 
 *     )
 * )
 */
public function jsonSerialize()
{
    $arr = array();
    // для "Main" части устанавливается ссылка на главный объект
    // У него рекурсивно будет вызвана jsonSerialize
    $arr[static::MAIN_INDEX] = $this->MainDataObject;
    
    if( count($this->ChildCollectionsArray) )
    {
        $arr[static::CHILDRENS_INDEX] = array();
        foreach ($this->ChildCollectionsArray as $Name => $ChildCollection)
        {
            // У $ChildCollection рекурсивно будет вызвана jsonSerialize
            $arr[static::CHILDRENS_INDEX][$Name] = $ChildCollection;
        }
    }
    if( count($this->DependenciesObjectsArray) )
    {
        $arr[static::DEPENDENCIES_INDEX] = array();
        foreach( $this->getDependenciesFieldsArray() as $Index => $DependenceField )
        {
            // тип объекта зависимоти
            $obtype = $DependenceField[2];
            $arr[static::DEPENDENCIES_INDEX][$Index] = $this->DependenciesObjectsArray[$obtype];
        }
//        foreach ($this->DependenciesObjectsArray as $Name => $Dependence)
//        {
//            // У $Dependence рекурсивно будет вызвана jsonSerialize
//            $arr[static::DEPENDENCIES_INDEX][$Name] = $Dependence;
//        }
    }

    return $arr;
}

/**
 * Инициализирует все части контейнера из массива данных соответсвующей структуры,
 * инициализируем главный объект из части массива Main,
 * каждая дочерняя коллекция будет проинициализирована своими данными
 * из части Childrens общего массива, перед инициализацией коллекции очищаются,
 * если какой-то чатси не будет, то коллекция останется пустой
 * инициализируется каждая зависимость и части Dependencies общего массива,
 * если каких-то данных не будет, то зависимость останется не тронутой!
 * 
 * @param array $Array - массив с данными вида
 * array(
 * "Main" => данные главного объекта,
 * "Childrens" => array(
 *      "NameOfChild1" => TypedCollection1,
 *      "NameOfChild2" => TypedCollection1,
 *      "NameOfChild3" => TypedCollection1,
 *      ...
 *      )
 * "Dependencies" => array(
 *      "NameOfDependence1" => IdDataObject1,
 *      ... 
 *     )
 * ) 
 * @throws TRMDataObjectContainerNoMainException
 */
public function initializeFromArray(array $Array)
{
    if( !isset( $Array[static::MAIN_INDEX] ) )
    {
        throw new TRMDataObjectContainerNoMainException( " Отсутствуют данные для главного объекта " . get_class($this) );
    }
    // инициализируем главный объект из части массива Main
    $this->MainDataObject->initializeFromArray($Array[static::MAIN_INDEX]);
    
    // каждая дочерняя коллекция будет проинициализирована своими данными
    // из части Childrens общего массива, перед инициализацией коллекции очищаются,
    // если какой-то чатси не будет, то коллекция останется пустой
    foreach( $this->ChildCollectionsArray as $Index => $ChildCollection )
    {
        $ChildCollection->clearCollection();
        $ChildCollection->setParentDataObject($this);
        if( !isset($Array[static::CHILDRENS_INDEX][$Index]) )
        {
            continue;
        }
        $ChildCollection->initializeFromArray( $Array[static::CHILDRENS_INDEX][$Index] );
        
    }
    
    // инициализируется каждая зависимость и части Dependencies общего массива,
    // если каких-то данных не будет, то создается новый объект типа очередной зависимости !
    foreach( $this->getDependenciesFieldsArray() as $Index => $DependenceField )
    {
        // если данных для зависимости нет, то объект не создается
        if( !isset($Array[static::DEPENDENCIES_INDEX][$Index]) )
        {
            continue;
        }
        // тип объекта зависимоти
        $obtype = $DependenceField[2];
        if( !isset( $this->DependenciesObjectsArray[$obtype] ) )
        {
            $this->DependenciesObjectsArray[$obtype] = new $obtype;
        }
        $this->DependenciesObjectsArray[$obtype]->initializeFromArray($Array[static::DEPENDENCIES_INDEX][$Index]);
    }
    // инициализируется каждая зависимость и части Dependencies общего массива,
    // если каких-то данных не будет, то зависимость останется не тронутой!
//    foreach( $this->DependenciesObjectsArray as $Index => $Dependence )
//    {
//        if( !isset($Array[static::DEPENDENCIES_INDEX][$Index]) )
//        {
//            continue;
//        }
//        $Dependence->initializeFromArray($Array[static::DEPENDENCIES_INDEX][$Index]);
//    }

}


/**
 * возвращает данные только для основного-главного объекта!!!
 */
public function getDataArray()
{
    return $this->MainDataObject->getDataArray();
}

/**
 * Устанавливает данные только в основном объекте
 * @param array $data
 */
public function setDataArray(array $data)
{
    $this->MainDataObject->setDataArray($data);
}
/**
 * возвращает данные только для основного-главного объекта!!!
 * 
 * @param string $objectname - имя объекта , для которого получаются данные
 * @param string $fieldname - имя поля (столбца), из которого производим чтение значения
 *
 * @retrun mixed|null - если нет записи с таким номером строки или нет поля с таким именем вернется null, если есть, то вернет значение
 */
public function getData($objectname, $fieldname)
{
    return $this->MainDataObject->getData($objectname, $fieldname);
}
/**
 * Устанавливает данные только в основном объекте
 * 
 * @param string $objectname - имя объекта, для которого устанавливаются данные
 * @param string $fieldname - имя поля (столбца), в которое производим запись значения
 * @param mixed $value - само записываемое значение
 */
public function setData($objectname, $fieldname, $value)
{
    $this->MainDataObject->setData($objectname, $fieldname, $value);
}

/**
 * присоединяет массив к данным основного объекта!!!
 * для дочерних нужно обращаться к каждому объекту коллекуии отдельно
 * @param array $data
 */
public function mergeDataArray(array $data)
{
    $this->MainDataObject->mergeDataArray($data);
}

/**
 * проверяет наличие данных только в основном объекте!!!
 * 
 * @param string $objectname - имя sub-объекта в главном объекте данных, для которого проверяется наобор данных
 * @param array $fieldname - массив с именами полей sub-объекта $objectname, в которых проверяется наличие данных
 */
public function presentDataIn($objectname, array &$fieldname)
{
    $this->MainDataObject->presentDataIn($objectname, $fieldname);
}


/****************************************************************************
 * реализация интерфейса TRMIdDataObjectInterface
 ****************************************************************************/
public function getId()
{
    return $this->MainDataObject->getId();
}
public function setId($id)
{
    $this->MainDataObject->setId($id);
}
public function resetId()
{
    $this->MainDataObject->resetId();
}

static public function getIdFieldName()
{
    $type = static::getMainDataObjectType();
    return $type::getIdFieldName();
}
static public function setIdFieldName(array $IdFieldName)
{
    $type = static::getMainDataObjectType();
    $type::setIdFieldName($IdFieldName);
}
static public function getMainDataObjectType()
{
    return static::$MainDataObjectType;
}

/**
 * реализация интерфейса Countable,
 * возвращает количество объектов в коллекции дочерних объектов данных
 */
public function count()
{
    return count($this->ChildCollectionsArray);
}


/**
 * реализация интерфейса Iterator,
 * возвращает текущий объект из массива-коллекции с дочерними объектами
 */
public function current()
{
    return current($this->ChildCollectionsArray);
}

/**
 * 
 * @return mixed - возвращает значение-имя текущего индекса (ключа) для коллекции с дочерними объектами данных,
 * можкт быть строковым или численным
 */
public function key()
{
    return key($this->ChildCollectionsArray);
}

/**
 * переставляет внутренний указатель-счетчик на следующий элемент массива с дочерними объектами
 */
public function next()
{
    next($this->ChildCollectionsArray);
    ++$this->Position;
}

/**
 * Устанавливает внутренний счетчик массива в начало - реализация интерфейса Iterator
 */
public function rewind()
{
    reset($this->ChildCollectionsArray);
    $this->Position = 0;
}

/**
 * если счетчик превышает или равен размеру массива, значит в этом элеменет уже ничего нет,
 * $this->Position всегда должна быть < count($this->ChildCollectionsArray)
 * 
 * @return boolean
 */
public function valid()
{
    return ($this->Position < count($this->ChildCollectionsArray));
}

public function addRow(array $Data)
{
    $this->MainDataObject->addRow($Data);
}

public function clear()
{
    $this->MainDataObject->clear();
    $this->clearChildCollectionsArray();
    $this->clearDependencies();
}

public function fieldExists($objectname, $fieldname)
{
    $this->MainDataObject->fieldExists($objectname, $fieldname);
}

public function getRow($Index)
{
    $this->MainDataObject->getRow($Index);
}

public function setRow($Index, $value)
{
    $this->MainDataObject->setRow($Index, $value);
}

public function removeRow($Index)
{
    $this->ChildCollectionsArray->removeDataObject($Index);
}


public function keyExists($Index)
{
    $this->MainDataObject->keyExists($Index);
}

public function offsetExists($offset)
{
    return array_key_exists($offset, $this->ChildCollectionsArray);
}

public function offsetGet($offset)
{
    return $this->ChildCollectionsArray[$offset];
}

public function offsetSet($offset, $value)
{
    if(is_null($offset) )
    {
        $this->ChildCollectionsArray[] = $value;
    }
    else
    {
        $this->ChildCollectionsArray[$offset] = $value;
    }
}

public function offsetUnset($offset)
{
    unset($this->ChildCollectionsArray[$offset]);
}

/**
 * 
 * @return array - ключи для массива ChildCollectionsArray
 */
public function getArrayKeys()
{
    return array_keys($this->ChildCollectionsArray);
}

public function inArray($Data, $CheckTypeFlag = true)
{
    return in_array($Data, $this->ChildCollectionsArray, $CheckTypeFlag);
}

public function mergeDataArrayObject(\TRMEngine\DataArray\Interfaces\TRMDataArrayInterface $DataArrayObject)
{
    array_merge($this->ChildCollectionsArray, $DataArrayObject);
}

public function pop()
{
    return array_pop($this->ChildCollectionsArray);
}

public function push($Data)
{
    array_push($this->ChildCollectionsArray, $Data);
}


} // TRMDataObjectsContainer