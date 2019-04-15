<?php

namespace TRMEngine\DataArray;

use TRMEngine\DataArray\Interfaces\TRMDataArrayInterface;

/**
 * класс для работы с массивом данных, 
 *
 * @author TRM
 */
class TRMDataArray implements TRMDataArrayInterface, \JsonSerializable
{
/**
 * @var array - массив данных
 */
protected $DataArray = array();

/**
 * возвращает весь массив с данными, вернется дубликат,
 * так как массив передается по значению ( версия PHP 5.3 ) !!!
 *
 * @return array
 */
public function getDataArray()
{
    return $this->DataArray;
}

/**
 * задает данные для всего массива DataArray, старые данные стираются.
 * пользоваться прямым присвоение следует осторожно,
 * так как передаваться должен двумерный массив, даже состоящий из одной строки!!!
 *
 * @param array $data - массив с данными, в объекте сохранится дубликат массива, 
 * так как массив передается по значению ( версия PHP 5.3 ) !!! 
 */
public function setDataArray( array $data )
{
    $this->DataArray = $data;
}

/**
 * "склеивает" два массива с данными, проверка на уникальность не проводится,
 * при использовании этого метода нужно быть осторожным с передаваемым массивом, 
 * он должен быть двумерным и каждая запись-строка должна иметь численный индекс
 *
 * @param array $data - массив для склеивания
 */
public function mergeDataArray( array $data )
{
    $this->DataArray = array_merge( $this->DataArray, $data );
}

/**
 * проверяет наличие ключа (поля с именем fieldname) у строки с номером rownum
 * 
 * @param string $Index - проверяемый индекс массива
 * 
 * @return boolean - если найден, возвращает true, если ключ отсутствует - false
 */
public function keyExists( $Index )
{
    // Такого поля нет
    if( !array_key_exists($Index, $this->DataArray) ) { return false; }

    // найдено !
    return true;
}

/**
 * записывает данные в конкретную ячейку
 *
 * @param string $Index - индекс строки в массиве (таблице) начиная с 0
 * @param mixed $value - значение-данные поля 
 */
public function setRow( $Index, $value )
{
    $this->DataArray[$Index] = $value;
}

/**
 * получает данные из конкретной ячейки
 *
 * @param string $Index - индекс строки в массиве (таблице) начиная с 0
 *
 * @retrun mixed|null - если нет записи с таким индексом, то вернется null, если есть, то вернет значение
 */
public function getRow( $Index )
{
    // если такой индекс не установлен, то возвращается null
    if( !$this->keyExists($Index) ) { return null; }
    return $this->DataArray[$Index];
}

/**
 * добавляет строку данных из массива $row
 *
 * @param array $Data - данные для добавления
 */
public function addRow( array $Data )
{
    $this->DataArray[] = $Data;
}

/**
 * очистка массива данных
 */
public function clear()
{
    $this->DataArray = array();
}


/**
 * реализация метода интерфейса Countable
 *
 * @return integer - количество элементов в массиве DataArray
 */
public function count()
{
    return count($this->DataArray);
}


/**
 * *********** Interface ArrayAccess **************
 */
public function offsetExists($offset)
{
    return $this->keyExists($offset);
}

public function offsetGet($offset)
{
    return $this->DataArray[$offset];
}

public function offsetSet($offset, $value)
{
    $this->DataArray[$offset] = $value;
}

public function offsetUnset($offset)
{
    unset( $this->DataArray[$offset] );
}

public function jsonSerialize()
{
    return $this->getDataArray();
}

} // TRMDataArray