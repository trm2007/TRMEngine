<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Exceptions\TRMDataObjectsCollectionWrongIndexException;
use TRMEngine\DataObject\Exceptions\TRMDataObjectsCollectionWrongTypeException;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataObject\Interfaces\TRMTypedCollectionInterface;
use TRMEngine\Repository\Exceptions\TRMRepositoryUnknowDataObjectClassException;

/**
 * класс для работы с коллекциями однотипных объектов DataObject
 * 
 * @version 2019-03-29
 */
class TRMTypedCollection extends TRMDataObjectsCollection implements TRMTypedCollectionInterface
{
  /**
   * @var string - тип сохраняемых объектов в данной коллекции
   */
  protected $ObjectsType;


  public function __construct($ObjectsType)
  {
    if (!class_exists($ObjectsType)) {
      throw new TRMRepositoryUnknowDataObjectClassException($ObjectsType);
    }
    $this->ObjectsType = $ObjectsType;
  }

  /**
   * @return string - тип сохраняемых объектов в данной коллекции
   */
  public function getObjectsType()
  {
    return $this->ObjectsType;
  }

  /**
   * проверяет соответствие типа объекта установленному для коллекции
   * 
   * @param TRMDataObjectInterface $DataObject - проверяемый объект
   * 
   * @throws TRMDataObjectsCollectionWrongTypeException
   */
  public function validateObject(TRMDataObjectInterface $DataObject)
  {
    if (get_class($DataObject) !== $this->ObjectsType) {
      throw new TRMDataObjectsCollectionWrongTypeException(get_class($this) . "-" . get_class($DataObject));
    }
  }

  /**
   * @param int $Index - целочисленный индекс объекта в коллекции объектов
   * @param TRMDataObjectInterface $DataObject - объект для установки в коллекции
   * 
   * @throws TRMDataObjectsCollectionWrongIndexException
   */
  public function setDataObject($Index, TRMDataObjectInterface $DataObject)
  {
    $this->validateObject($DataObject);
    parent::setDataObject($Index, $DataObject);
  }

  /**
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
    $this->validateObject($DataObject);
    return parent::addDataObject($DataObject, $AddDuplicateFlag);
  }

  /**
   * добавляет в коллекцию содержимое другой коллекции,
   * если только такого объект еще нет в своем массиве,
   * точнее не самого объекта, а ссылки на этот же самы йобъект
   * 
   * @param TRMDataObjectsCollection $Collection
   * @param bool $AddDuplicateFlag - если этот флаг установден в false, то в коллекцию не добавятся дубликаты объектов,
   * если утсановить в TRUE, то новая коллекция добавистя как есть к существующей, со всеми элементами,
   * даже если они дублируют уже присутсвующие, по умолчанию - false (дубли не добавляются)
   */
  public function mergeCollection(TRMDataObjectsCollectionInterface $Collection, $AddDuplicateFlag = false)
  {
    if ($Collection->ObjectsType !== $this->ObjectsType) {
      throw new TRMDataObjectsCollectionWrongTypeException(get_class($this) . "-" . get_class($Collection->ObjectsType));
    }
    parent::mergeCollection($Collection, $AddDuplicateFlag);
  }

  /**
   * перебирает массив $Array,
   * на основе каждого его элемента создает новый объет хранимого типа,
   * и вызывает у него так же функцию initializeFromArray,
   * добавляет вновь созданный объект в коллекцию
   * 
   * @param array $Array - массив с данными для инициализации элементов коллекции
   */
  public function initializeFromArray(array $Array)
  {
    if (empty($Array)) {
      $this->clearCollection();
      return;
    }
    foreach ($Array as $Index => $Data) {
      if (empty($Data)) {
        continue;
      }
      $DataObject = new $this->ObjectsType;
      $DataObject->initializeFromArray($Data);
      $this->setDataObject($Index, $DataObject);
    }
  }
} // TRMTypedCollection
