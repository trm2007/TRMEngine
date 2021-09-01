<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMParentedCollectionInterface;

/**
 * Класс коллекции, у которой есть родительский объект,
 * например коллекция изображений для книги
 *
 * @version 2019-04-20
 */
abstract class TRMParentedCollection extends TRMTypedCollection implements TRMParentedCollectionInterface
{
  /**
   * @var array - массив = (имя объекта, имя свойства) содержащего Id родителя в коллекции,
   * должен определяться в каждом дочернем классе со своими именами
   */
  static protected $ParentIdFieldName;
  /**
   * @var TRMIdDataObjectInterface - ссылка на объект родителя для набора из текущей коллекции...
   */
  protected $ParentDataObject = null;

  /**
   * 
   * @param string $ObjectsType - имя типа объектов в коллекции
   * @param \TRMEngine\DataObject\TRMIdDataObjectInterface $ParentDataObject - родительский объект
   */
  public function __construct($ObjectsType, TRMIdDataObjectInterface $ParentDataObject)
  {
    parent::__construct($ObjectsType);
    static::setParentIdFieldName($ObjectsType::getParentIdFieldName());

    $this->setParentDataObject($ParentDataObject);
  }

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
    foreach ($this->DataArray as $Object) {
      $Object->setParentDataObject($ParentDataObject);
    }
  }

  /**
   * устанавливает объект $DataObject в коллекцию под индексом $Index,
   * если такой индекс еще не существует, то создаст,
   * при этом значение родительского поля будет установлено в Id текущего родителя для коллекции
   * 
   * @param string $Index - индекс объекта в коллекции
   * @param TRMDataObjectInterface $DataObject - устанавливаемый объект
   */
  public function setDataObject($Index, TRMDataObjectInterface $DataObject)
  {
    $DataObject->setParentDataObject($this->ParentDataObject);
    parent::setDataObject($Index, $DataObject);
  }

  /**
   * добавдяет объект $DataObject в коллекцию,
   * при этом значение родительского поля будет установлено в Id текущего родителя для коллекции
   * 
   * @param TRMDataObjectInterface $DataObject - добавит это объект в коллекцию
   * @param bool $AddDuplicateFlag - если этот флаг установден в false, то в коллекцию не добавятся дубликаты объектов,
   * если утсановить в TRUE, то объект добавится как новый,
   * даже если он дублирует уже присутсвующий,
   * по умолчанию - false (дубли не добавляются)
   * 
   * @return boolean - если объект добавлен в коллекцию, то вернется TRUE, иначе FALSE
   */
  public function addDataObject(TRMDataObjectInterface $DataObject, $AddDuplicateFlag = false)
  {
    $DataObject->setParentDataObject($this->ParentDataObject);
    return parent::addDataObject($DataObject, $AddDuplicateFlag);
  }
}
