<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMParentedDataObjectInterface;

/**
 * класс для работы с коллекцией объектов данных, у которых есть ID-родителя
 * фактически данные представлены таблицей в виде двумерного массива
 *
 * @author TRM
 */
abstract class TRMParentedDataObject extends TRMDataObject implements TRMParentedDataObjectInterface
{
/**
 * @var array - массив = (имя объекта, имя свойства) содержащего Id родителя в коллекции,
 * должен определяться в каждом дочернем классе со своими именами
 */
// static protected $ParentIdFieldName;
/**
 * @var TRMIdDataObjectInterface - ссылка на объект родителя для набора из текущей коллекции...
 */
protected $ParentDataObject = null;


/**
 * @return array - имя свойства содержащего Id родителя в коллекции
 */
static public function getParentIdFieldName()
{
    return static::$ParentIdFieldName;
}
/**
 * @param array $ParentIdFieldName - имя свойства содержащего Id родителя в коллекции
 */
static public function setParentIdFieldName(array $ParentIdFieldName)
{
    static::$ParentIdFieldName[0] = reset($ParentIdFieldName);
    static::$ParentIdFieldName[1] = next($ParentIdFieldName);
    reset($ParentIdFieldName);
}

/**
 * @return TRMIdDataObjectInterface - возвращает объект родителя
 */
public function getParentDataObject()
{
    return $this->ParentDataObject;
}

/**
 * @param TRMIdDataObjectInterface $ParentDataObject - устанавливает объект родителя, 
 * при этом меняются все родительские Id в коллекции
 */
public function setParentDataObject(TRMIdDataObjectInterface $ParentDataObject)
{
    $this->ParentDataObject = $ParentDataObject;

    $ParentIdFieldName = static::getParentIdFieldName();
    // устанавливаем значение родительского поля в Id нового родителя
    $this->setData( $ParentIdFieldName[0], $ParentIdFieldName[1], $ParentDataObject->getId() );
}


} // TRMParentedCollectionDataObject