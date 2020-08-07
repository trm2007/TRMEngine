<?php

namespace TRMEngine\DataArray;

use TRMEngine\DataArray\Interfaces\TRMDataArrayInterface;

/**
 * класс для работы с массивом данных, 
 *
 * @author TRM
 */
class TRMDataArray implements TRMDataArrayInterface
{
/**
 * @var int - текущая позиция указателя в массиве для реализации интерфейса Iterator
 */
private $Position = 0;
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
 * если в новом массиве встерится уже существующий строковый индекс, 
 * старые данные перезапишутся
 *
 * @param array $Array - массив для склеивания
 */
public function mergeDataArray( array $Array )
{
    $this->DataArray = array_merge( $this->DataArray, $Array );
}

/**
 * "склеивает" два массива с данными, из текущего объекти и из объекта $DataArrayObject,
 * проверка на уникальность не проводится,
 * если в новом массиве встерится уже существующий строковый индекс, 
 * старые данные перезапишутся
 *
 * @param TRMDataArrayInterface $DataArrayObject - объект TRMDataArray  для склеивания
 */
public function mergeDataArrayObject( TRMDataArrayInterface $DataArrayObject )
{
    $this->DataArray = array_merge( $this->DataArray, $DataArrayObject->DataArray );
}

/**
 * проверяет наличие ключа в текущем массиве
 * 
 * @param string $Index - проверяемый индекс-ключ массива
 * 
 * @return boolean - если найден, возвращает true, если ключ отсутствует - false
 */
public function keyExists( $Index )
{
    return array_key_exists($Index, $this->DataArray);
}

/**
 * проверяет наличие данных в массиве
 * 
 * @param mixed $Data - данные для проверки
 * @param boolean $CheckTypeFlag - если устанлвлен (по умолчанию), то 
 * проверятся так же соответсвие типов
 * 
 * @return boolean - если найдены, возвращает true, если отсутствуют - false
 */
public function inArray( $Data, $CheckTypeFlag = true )
{
    return in_array($Data, $this->DataArray, $CheckTypeFlag);
}

/**
 * Добавляет $Data в конец массива
 * 
 * @param mixed $Data
 */
public function push($Data)
{
    $this->DataArray[] = $Data;
}

/**
 * @return mixed - возвращает последний элемент из массива, 
 * удаляя его из массива, внутренний указатель обнуляется
 */
public function pop()
{
    return array_pop($this->DataArray) ; 
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
 * убирает данные с индексом $Index из массива DataArray,
 * если они установлены
 *
 * @param string $Index - индекс, который нужно исключить
 */
public function removeRow( $Index )
{
    if( isset($this->DataArray[$Index]) )
    {
        unset($this->DataArray[$Index]);
    }
}

/**
 * @return array - массив с именами ключей в массиве DataArray
 */
public function getArrayKeys()
{
    return array_keys($this->DataArray);
}

/**
 * очистка массива данных
 */
public function clear()
{
    $this->DataArray = array();
    $this->Position = 0;
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

// ******************** Iterator   **************************************************
/**
 * Устанавливает внутренний счетчик массива в начало - реализация интерфейса Iterator
 */
public function rewind()
{
    reset($this->DataArray);
    $this->Position = 0;
}

public function current()
{
    return current($this->DataArray);
}

public function key()
{
    return key($this->DataArray);
}

public function next()
{
    next($this->DataArray);
    ++$this->Position;
}
/**
 * если счетчик превышает или равен размеру массива, значит в этом элеменет уже ничего нет,
 * $this->Position всегда должна быть < count($this->DataArray)
 * 
 * @return boolean
 */
public function valid()
{
    return ($this->Position < count($this->DataArray));
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
    if( is_null($offset) )
    {
        $this->DataArray[] = $value;
    }
    else { $this->DataArray[$offset] = $value; }
}

public function offsetUnset($offset)
{
    unset( $this->DataArray[$offset] );
}

/**
 * реализация интерфейса JsonSerializable,
 * возвращает данные, 
 * которые будут обрабатываться при вызове json_encode для этого объекта
 * 
 * @return array
 */
public function jsonSerialize()
{
    return $this->getDataArray();
}

/**
 * @param array $Array - массив, который будет скопирован в данные без изменения
 */
public function initializeFromArray(array $Array)
{
    $this->DataArray = $Array;
}


} // TRMDataArray