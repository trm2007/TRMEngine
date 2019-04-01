<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Exceptions\TRMDataObjectSCollectionWrongIndexException;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;

/**
 * класс для работы с коллекциями однотипных объектов DataObject
 * 
 * @version 2019-03-29
 */
class TRMTypedCollection extends TRMDataObjectsCollection
{
/**
 *
 * @var string - тип сохраняемых объектов в данной коллекции
 */
protected $ObjectsType;


public function __construct($ObjectsType)
{
    if( !class_exists($ObjectsType) )
    {
        throw new TRMRepositoryUnknowDataObjectClassException( $objectclassname );
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
 * @throws TRMDataObjectSCollectionWrongTypeException
 */
public function validateObject(TRMDataObjectInterface $DataObject)
{
    if( get_class($DataObject) !== static::$ObjectsType )
    {
        throw new TRMDataObjectSCollectionWrongTypeException( get_class($this) . "-" . get_class($DataObject) );
    }
}

/**
 * @param int $Index - целочисленный индекс объекта в коллекции объектов
 * @param TRMDataObjectInterface $DataObject - объект для установки в коллекции
 * 
 * @throws TRMDataObjectSCollectionWrongIndexException
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
public function addDataObject( TRMDataObjectInterface $DataObject, $AddDuplicateFlag = false )
{
    $this->validateObject($DataObject);
    return parent::addDataObject($Index, $DataObject);
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
public function mergeCollection(TRMDataObjectsCollectionInterface $Collection, $AddDuplicateFlag = false )
{
    if( $Collection::$ObjectsType !== static::$ObjectsType )
    {
        throw new TRMDataObjectSCollectionWrongTypeException( get_class($this) . "-" . get_class($Collection::$ObjectsType) );
    }
    parent::mergeCollection($Collection, $AddDuplicateFlag);
}


} // TRMDataObjectsCollection
