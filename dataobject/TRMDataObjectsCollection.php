<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Exceptions\TRMDataObjectsCollectionWrongIndexException;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;

/**
 * класс для работы с коллекциями однотипных объектов DataObject
 * 
 * @version 2019-03-29
 */
class TRMDataObjectsCollection implements TRMDataObjectsCollectionInterface
{
/**
 * @var int - текущая позиция указателя в массиве для реализации интерфейса Iterator
 */
private $Position = 0;
/**
 * @var array(TRMDataObjectInterface) - массив-коллекция с объектами данных TRMDataObject
 */
protected $DataObjectsArray = array();

/**
 * @param int $Index - индекс запрашиваемого объекта в массиве-коллекции
 * 
 * @return TRMDataObjectInterface - объект данных
 * @throws TRMDataObjectsCollectionWrongIndexException
 */
public function getDataObject($Index)
{
    if( !key_exists($Index, $this->DataObjectsArray) )
    {
        throw new TRMDataObjectsCollectionWrongIndexException();
    }
    return $this->DataObjectsArray[$Index];
}

/**
 * @param int $Index - целочисленный индекс объекта в коллекции объектов
 * @param TRMDataObjectInterface $DataObject - объект для установки в коллекции
 * 
 * @throws TRMDataObjectsCollectionWrongIndexException
 */
public function setDataObject($Index, TRMDataObjectInterface $DataObject)
{
    if( !key_exists($Index, $this->DataObjectsArray) )
    {
        throw new TRMDataObjectsCollectionWrongIndexException();
    }
    $this->DataObjectsArray[$Index] = $DataObject;
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
    if( !$AddDuplicateFlag && $this->hasDataObject($DataObject) )
    {
        return false;
    }
    $this->DataObjectsArray[] = $DataObject;
    return true;
}

/**
 * проверяет, есть ли в коллекции объект,
 * точнее ссылка на этот объект
 * 
 * @param TRMDataObjectInterface $Object
 * @return boolean
 */
public function hasDataObject( TRMDataObjectInterface $Object )
{
    foreach( $this->DataObjectsArray as $Item )
    {
        if( $Item === $Object ) { return true; }
    }
    return false;
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
    foreach( $Collection as $Item )
    {
        $this->addDataObject($Item, $AddDuplicateFlag);
    }
}

/**
 * очищает массив-коллекцию с объектами данных,
 * так как в массиве хранятся только ссылки, 
 * то сами объекты остаются в памяти, если их кто-то использует
 */
public function clearCollection()
{
    $this->DataObjectsArray = array();
    $this->Position = 0;
}

/**
 * меняет во всех записях значение поля $FieldName на новое значение $FieldValue, если разрешена запись
 *
 * @param string $ObjectName - имя объекта, в котором меняется значение 
 * @param string $FieldName - имя поля в объектах данных
 * @param mixed $FieldValue - новое значение
 */
public function changeAllValuesFor($ObjectName, $FieldName, $FieldValue)
{
    foreach( $this->DataObjectsArray as $Object )
    {
        $Object->setData( $ObjectName, $FieldName, $FieldValue );
    }
}

// ********************    **************************************************

public function count()
{
    return count($this->DataObjectsArray);
}

// ********************    **************************************************

public function current()
{
    return $this->DataObjectsArray[$this->Position];
    // return current($this->DataObjectsArray);
}

public function key()
{
    return $this->Position;
    // return key($this->DataObjectsArray);
}

public function next()
{
    $this->Position++;
    // next($this->DataObjectsArray);
}

public function rewind()
{
    $this->Position = 0;
    //rewind($this->DataObjectsArray);
}

public function valid()
{
    if(key_exists($this->Position, $this->DataObjectsArray) )
    {
        return true;
    }
    return false;
}

// ********************    **************************************************

public function offsetExists($offset)
{
    if(key_exists($offset, $this->DataObjectsArray) )
    {
        return true;
    }
    return false;
}

public function offsetGet($offset)
{
    return $this->DataObjectsArray[$offset];
}

public function offsetSet($offset, $value)
{
    $this->DataObjectsArray[$offset] = $value;
}

public function offsetUnset($offset)
{
    unset($this->DataObjectsArray[$offset]);
}


} // TRMDataObjectsCollection
