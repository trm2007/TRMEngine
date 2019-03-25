<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Exceptions\TRMDataObjectContainerNoMainException;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;

/**
 * класс контейнер объектов данных, используется для составных объектов,
 * например, для составного продукта со всеми дополнительными коллекциями и объектами
 * (характеристики, комплект, доп.изображениями и т.д.)
 */
abstract class TRMDataObjectsContainer implements TRMDataObjectsContainerInterface // extends TRMIdDataObject
{
/**
 * @var TRMIdDataObjectInterface - основной объект
 */
protected $MainDataObject;
/**
 * @var array(TRMDataObjectInterface) - массив объектов данных, дополняющих основной объект, 
 * например коллекция характеристик, доп.изображения, комплекты, скидки и т.д.
 */
protected $ObjectsArray = array();
/**
 * @var integer - текущая позиция указателя, для реализации интерфейса итератора - Iterator
 */
private $Position = 0;


/**
 * магический метод,
 * позволяет получать объекты из контейнера обращаясь к ним как к свойствам класса
 * TRMDataObjectsContainer->ObjectName
 * 
 * @param string $name - имя свойства-объекта
 * @return TRMDataObjectInterface
 */
public function __get($name)
{
    return $this->getDataObject($name);
}
/**
 * магический метод,
 * позволяет добавлять объекты в контейнер, обращаясь к ним как к свойствам класса
 * TRMDataObjectsContainer->ObjectName = $value;
 * 
 * @param string $name
 * @param TRMDataObjectInterface $value
 */
/*
public function __set($name, $value)
{
    $this->setDataObject($name, $value);
}
*/

/**
 * @return TRMDataObjectInterface - возвращает главный (сохраненный под 0-м номером в массиве) объект данных
 */
public function getMainDataObject()
{
    return $this->MainDataObject;
}

/**
 * устанавливает главный объект данных,
 * 
 * @param TRMDataObjectInterface $do - главный объект данных
 */
public function setMainDataObject(TRMDataObjectInterface $do)
{
    $this->MainDataObject = $do;
}

/**
 * помещает объект данных в массив под номером $Index, сохраняется только ссылка, объект не клонируется!!!
 * 
 * @param string $Index - номер-индекс, под которым будет сохранен объект в контейнере
 * @param TRMDataObjectInterface $do - добавляемый объект
 */
public function setDataObject($Index, TRMDataObjectInterface $do) // был TRMParentedDataObject, но позже сделал для все объектов данных
{
    if( method_exists($do, "setParentDataObject") )
    {
        $do->setParentDataObject($this);
    }

    $this->ObjectsArray[$Index] = $do;
}

/**
 * возвращает объект из контейнера под номером $Index
 * 
 * @param integer $Index - номер объекта в контейнере
 * 
 * @return TRMDataObjectInterface - объект из контейнера
 */
public function getDataObject($Index)
{
    if( isset($this->ObjectsArray[$Index]) ) { return $this->ObjectsArray[$Index]; }
    return null;
}

/**
 * @return array - возвращает массив объектов данных, дополняющих основной объект
 */
public function getObjectsArray()
{
    return $this->ObjectsArray;
}

/**
 * очищает массив с доп. объектами данных
 */
public function clearObjectsArray()
{
    // так как в массиве хранятся ссылки на реальные объекты, то они не удаляются при опустошении массива,
    // поэтому вручную устанавливае для каждого объекта данных родителя в null, чтобы они не ссылалис на контейнер из которого они удалены
    foreach( $this->ObjectsArray as $object )
    {
        if( method_exists($object, "setParentDataObject") )
        {
            $object->setParentDataObject(null);
        }
    }
    $this->ObjectsArray = array();
}

/**
 * @return array - вернет массив из двух элементов вида :
 * array(
 * "Main" => данные главного объекта,
 * "Children" => array(
 *      "NameOfChild1" => данные первого дочернего объекта,
 *      "NameOfChild2" => данные второго дочернего объекта,
 *      "NameOfChild3" => данные третьего дочернего объекта,
 * ...
 *      )
 * )
 */
public function getOwnData()
{
    $arr = array( 
        "Main" => $this->MainDataObject->getOwnData(), 
        "Children" => array() );
    
    foreach ($this->ObjectsArray as $Name => $Child)
    {
        if( $Child->count() )
        {
            $arr["Children"][$Name] = $Child->getOwnData();
        }
    }

    return $arr;
}

/**
 * 
 * @param array $data  - массив из двух элементов вида :
 * array(
 * "Main" => данные главного объекта,
 * "Children" => array(
 *      "NameOfChild1" => данные первого дочернего объекта,
 *      "NameOfChild2" => данные второго дочернего объекта,
 *      "NameOfChild3" => данные третьего дочернего объекта,
 * ...
 *      )
 * ), при этом в массиве $this->ObjectsArray - уже должны быть проинициализированны объекты, 
 * соответсвующих типов, что бы принять данные, и объект $this->MainDataObject тоже должен быть создан
 * 
 * @throws TRMDataObjectContainerNoMainException - в текущей версии если не установлены данные в главной части контейнера - Main, тогда выбрасывается исключение
 * // если какой-то из частей не будет в массиве $data, то выбрасывается исключение
 */
public function setOwnData(array $data)
{
    // основная часть объекта должна быть установлена всегда
    if( !isset($data["Main"]) )
    {
        throw new TRMDataObjectContainerNoMainException( __METHOD__ );
    }
    // все остальные могут быть пустыми
    /*
    if( !isset($data["Children"]) )
    {
        throw new Exception( __METHOD__ . " Неверный формат данных! Отсутсвует ключ Children!");
    }
     */
    $this->MainDataObject->setOwnData($data["Main"]);

    foreach( $this->ObjectsArray as $Name => $Child )
    {
        if( !isset($data["Children"][$Name]) )
        {
            // если часть данных не заполнена, то пропускаем
            continue;
            // throw new Exception( __METHOD__ . " Неверный формат данных! Отсутсвует часть объекта - {$Name} в разделе Children!");
        }
        $Child->setOwnData( $data["Children"][$Name] );
    }
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
 * @parm integer $rownum - номер строки в массиве (таблице) начиная с 0
 * @param string $objectname - имя объекта в строке с номером $rownum, для которого получаются данные
 * @param string $fieldname - имя поля (столбца), из которого производим чтение значения
 *
 * @retrun mixed|null - если нет записи с таким номером строки или нет поля с таким именем вернется null, если есть, то вернет значение
 */
public function getData($rownum, $objectname, $fieldname)
{
    return $this->MainDataObject->getData($rownum, $objectname, $fieldname);
}
/**
 * Устанавливает данные только в основном объекте
 * @param integer $rownum - номер строки в массиве (таблице) начиная с 0
 * @param string $objectname - имя объекта в строке с номером $rownum, для которого устанавливаются данные
 * @param string $fieldname - имя поля (столбца), в которое производим запись значения
 * @param mixed $value - само записываемое значение
 */
public function setData($rownum, $objectname, $fieldname, $value)
{
    $this->MainDataObject->setData($rownum, $objectname, $fieldname, $value);
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
 * @param integer  $rownum
 * @param string $objectname - имя объекта в строке с номером $rownum, для которого проверяется набор данных
 * @param array $fieldnames
 */
public function presentDataIn($rownum, $objectname, array &$fieldnames)
{
    $this->MainDataObject->presentDataIn($rownum, $fieldnames);
}
public function getId() {
    $this->MainDataObject->getId();
}

public function setId($id) {
    $this->MainDataObject->setId($id);
}

public function resetId() {
    $this->MainDataObject->resetId();
}

public function getIdFieldName() {
    $this->MainDataObject->getIdFieldName();
}

public function setIdFieldName(array $IdFieldName) {
    $this->MainDataObject->setIdFieldName($IdFieldName);
}

/**
 * возврашает значение хранящееся в поле $fieldname объекта $objectname
 * 
 * @param string $objectname - имя объекта, для которого получаются данные
 * @param string $fieldname - имя поля
 * @return mixed|null - если есть значение в поле $fieldname, то вернется его значение, либо null,
 */
public function getFieldValue($objectname, $fieldname)
{
    $this->MainDataObject->getData(0, $objectname, $fieldname);
}
/**
 * устанавливает значение в поле $fieldname объекта $objectname
 * 
 * @param string $objectname - имя объекта, для которого получаются данные
 * @param string $fieldname - имя поля
 * @param mixed -  значение, которое должено быть установлено в поле $fieldname объекта $objectname
 */
public function setFieldValue($objectname, $fieldname, $value)
{
    $this->MainDataObject->setData(0, $objectname, $fieldname, $value);
}

/**
 * реализация интерфейса Countable,
 * возвращает количество объектов в коллекции дочерних объектов данных
 */
public function count()
{
    return count($this->ObjectsArray);
}


/**
 * реализация интерфейса Iterator,
 * возвращает текущий объект из массива-коллекции с дочерними объектами
 */
public function current()
{
    return current($this->ObjectsArray);
}

/**
 * 
 * @return mixed - возвращает значение-имя текущего индекса (ключа) для коллекции с дочерними объектами данных,
 * можкт быть строковым или численным
 */
public function key()
{
    return key($this->ObjectsArray);
}

/**
 * переставляет внутренний указатель-счетчик на следующий элемент массива с дочерними объектами
 */
public function next()
{
    next($this->ObjectsArray);
    ++$this->Position;
}

/**
 * Устанавливает внутренний счетчик массива в начало - реализация интерфейса Iterator
 */
public function rewind()
{
    reset($this->ObjectsArray);
    $this->Position = 0;
}

/**
 * если счетчик превышает или равен размеру массива, значит в этом элеменет уже ничего нет,
 * $this->Position всегда должна быть < count($this->ObjectsArray)
 * 
 * @return boolean
 */
public function valid()
{
    return ($this->Position < count($this->ObjectsArray));
}


} // TRMDataObjectsContainer