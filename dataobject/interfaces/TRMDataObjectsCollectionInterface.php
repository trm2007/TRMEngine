<?php

namespace TRMEngine\DataObject\Interfaces;

use TRMEngine\DataArray\Interfaces\InitializibleFromArray;
use TRMEngine\DataObject\Exceptions\TRMDataObjectsCollectionWrongIndexException;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;

/**
 * класс для работы с коллекциями однотипных объектов DataObject
 * 
 * @version 2019-03-29
 */
interface TRMDataObjectsCollectionInterface extends \ArrayAccess, \Iterator, \Countable, \JsonSerializable
{
/**
 * @param int $Index - индекс запрашиваемого объекта в массиве-коллекции
 * 
 * @return TRMDataObjectInterface - объект данных
 * @throws TRMDataObjectsCollectionWrongIndexException
 */
public function getDataObject($Index);

/**
 * @param int $Index - целочисленный индекс объекта в коллекции объектов
 * @param TRMDataObjectInterface $DataObject - объект для установки в коллекции
 */
public function setDataObject($Index, TRMDataObjectInterface $DataObject);

/**
 * @param TRMDataObjectInterface $DataObject - добавит это объект в коллекцию
 * @param bool $AddDuplicateFlag - если этот флаг установден в false, то в коллекцию не добавятся дубликаты объектов,
 * если утсановить в TRUE, то объект добавится как новый,
 * даже если он дублирует уже присутсвующий,
 * по умолчанию - false (дубли не добавляются)
 * 
 * @return boolean - если объект добавлен в коллекцию, то вернется TRUE, иначе FALSE
 */
public function addDataObject( TRMDataObjectInterface $DataObject, $AddDuplicateFlag = false );

/**
 * проверяет, есть ли в коллекции объект,
 * точнее ссылка на этот объект
 * 
 * @param TRMDataObjectInterface $Object
 * @return boolean
 */
public function hasDataObject( TRMDataObjectInterface $Object );

/**
 * добавляет в коллекцию содержимое другой коллекции,
 * если только такого объект еще нет в своем массиве,
 * точнее не самого объекта, а ссылки на этот же самы йобъект
 * 
 * @param TRMDataObjectsCollectionInterface $Collection
 * @param bool $AddDuplicateFlag - если этот флаг установден в false, то в коллекцию не добавятся дубликаты объектов,
 * если утсановить в TRUE, то новая коллекция добавистя как есть к существующей, со всеми элементами,
 * даже если они дублируют уже присутсвующие, по умолчанию - false (дубли не добавляются)
 */
public function mergeCollection(TRMDataObjectsCollectionInterface $Collection, $AddDuplicateFlag = false );

/**
 * очищает массив-коллекцию с объектами данных,
 * так как в массиве хранятся только ссылки, 
 * то сами объекты остаются в памяти, если их кто-то использует
 */
public function clearCollection();

/**
 * меняет во всех объектах коллекции значение 
 * поля $FieldName sub-объекта $ObjectName на новое значение $FieldValue
 *
 * @param string $ObjectName - имя объекта, в котором меняется значение 
 * @param string $FieldName - имя поля в объектах данных
 * @param mixed $FieldValue - новое значение
 */
public function changeAllValuesFor($ObjectName, $FieldName, $FieldValue);


} // TRMDataObjectsCollectionInterface


interface TRMTypedCollectionInterface extends TRMDataObjectsCollectionInterface, InitializibleFromArray
{
/**
 * @return string - тип сохраняемых объектов в коллекциях данного типа
 */
public function getObjectsType();

/**
 * проверяет соответствие типа объекта установленному для коллекции
 * 
 * @param TRMDataObjectInterface $DataObject - проверяемый объект
 * 
 * @throws TRMDataObjectSCollectionWrongTypeException
 */
public function validateObject(TRMDataObjectInterface $DataObject);


} // TRMTypedCollectionInterface


interface TRMParentedCollectionInterface extends TRMTypedCollectionInterface, TRMParentedDataObjectInterface
{
    
} // TRMParentedCollectionInterface