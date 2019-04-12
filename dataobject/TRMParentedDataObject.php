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
function getParentDataObject()
{
    return $this->ParentDataObject;
}

/**
 * @param TRMIdDataObjectInterface $ParentDataObject - устанавливает объект родителя, 
 * при этом меняются все родительские Id в коллекции
 */
function setParentDataObject(TRMIdDataObjectInterface $ParentDataObject)
{
    $this->ParentDataObject = $ParentDataObject;
    $this->changeParentIdForCurrentParent();
}

/**
 * вспомогательная функция, меняет все значения поля родительского ID для коллекции 
 * на значение ID из родительского объекта, 
 * используется только в функциях копирования, установки всей коллекции из другого объекта, и смены родителя.
 * если родительский объект еще не установлен, то все значения родительскиго Id будут установлены в null
 */
private function changeParentIdForCurrentParent()
{
    if( $this->ParentDataObject )
    {
        $this->changeAllValuesFor( $this->ParentIdFieldName[0], $this->ParentIdFieldName[1], $this->ParentDataObject->getId() );
    }
    else
    {
        $this->changeAllValuesFor( $this->ParentIdFieldName[0], $this->ParentIdFieldName[1], null );
    }
}


} // TRMParentedCollectionDataObject