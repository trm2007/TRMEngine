<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\TRMDataObject;

/**
 * класс для работы с коллекцией объектов данных, 
 * фактически данные представлены таблицей в виде двумерного массива
 *
 * @author TRM
 */
class TRMCollectionDataObject extends TRMDataObject implements \ArrayAccess // IteratorAggregate
{

/**
 * Присваивает значение заданному смещению - реализация интерфейса ArrayAccess
 * 
 * @param int $offset
 * @param array $value
 */
public function offsetSet($offset, $value)
{
    if (is_null($offset)) {
        $this->DataArray[] = $value;
    } else {
        $this->DataArray[$offset] = $value;
    }
}

/**
 * Определяет, существует ли заданное смещение (ключ) - реализация интерфейса ArrayAccess
 * 
 * @param int $offset
 * @return array
 */
public function offsetExists($offset)
{
    return isset($this->DataArray[$offset]);
}

/**
 * Удаляет смещение, объект по заданному смещению - реализация интерфейса ArrayAccess
 * 
 * @param int $offset
 */
public function offsetUnset($offset)
{
    unset($this->DataArray[$offset]);
}

/**
 * Возвращает заданное смещение (ключ) - реализация интерфейса ArrayAccess
 * 
 * @param int $offset
 * @return array
 */
public function offsetGet($offset)
{
    return isset($this->DataArray[$offset]) ? $this->DataArray[$offset] : null;
}


} // TRMCollectionDataObject