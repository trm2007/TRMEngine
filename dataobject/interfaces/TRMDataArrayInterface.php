<?php

namespace TRMEngine\DataArray\Interfaces;

interface InitializibleFromArray
{
/**
 * перебирает массив $Array,
 * на основе каждого его элемента создает новый объет хранимого типа,
 * и вызывает у него так же функцию initializeFromArray,
 * добавляет вновь созданный объект в коллекцию
 * 
 * @param array $Array - массив с данными для инициализации элементов коллекции
 */
public function initializeFromArray( array $Array );

}

interface TRMDataArrayInterface extends InitializibleFromArray, \Countable, \Iterator, \ArrayAccess, \JsonSerializable
{
/**
 * возвращает весь массив с данными, вернется дубликат,
 * так как массив передается по значению ( версия PHP 5.3 ) !!!
 *
 * @return array
 */
public function getDataArray();

/**
 * задает данные для всего массива DataArray, старые данные стираются.
 * пользоваться прямым присвоение следует осторожно,
 * так как передаваться должен двумерный массив, даже состоящий из одной строки!!!
 *
 * @param array $data - массив с данными, в объекте сохранится дубликат массива, 
 * так как массив передается по значению ( версия PHP 5.3 ) !!! 
 */
public function setDataArray( array $data );

/**
 * "склеивает" два массива с данными, проверка на уникальность не проводится,
 * при использовании этого метода нужно быть осторожным с передаваемым массивом, 
 * он должен быть двумерным и каждая запись-строка должна иметь численный индекс
 *
 * @param array $data - массив для склеивания
 */
public function mergeDataArray( array $data );

/**
 * "склеивает" два массива с данными, из текущего объекти и из объекта $DataArrayObject,
 * проверка на уникальность не проводится,
 * если в новом массиве встерится уже существующий строковый индекс, 
 * старые данные перезапишутся
 *
 * @param TRMDataArrayInterface $DataArrayObject - объект TRMDataArray  для склеивания
 */
public function mergeDataArrayObject( TRMDataArrayInterface $DataArrayObject );

/**
 * проверяет наличие ключа в текущем массиве
 * 
 * @param string $Index - проверяемый индекс-ключ массива
 * 
 * @return boolean - если найден, возвращает true, если ключ отсутствует - false
 */
public function keyExists( $Index );

/**
 * проверяет наличие данных в массиве
 * 
 * @param mixed $Data - данные для проверки
 * @param boolean $CheckTypeFlag - если устанлвлен (по умолчанию), то 
 * проверятся так же соответсвие типов
 * 
 * @return boolean - если найдены, возвращает true, если отсутствуют - false
 */
public function inArray( $Data, $CheckTypeFlag = true );

/**
 * Добавляет $Data в конец массива
 * 
 * @param mixed $Data
 */
public function push($Data);

/**
 * @return mixed - возвращает последний элемент из массива, 
 * удаляя его из массива, внутренний указатель обнуляется
 */
public function pop();

/**
 * записывает данные в конкретную ячейку
 *
 * @param string $Index - индекс строки в массиве (таблице) начиная с 0
 * @param mixed $value - значение-данные поля 
 */
public function setRow( $Index, $value );

/**
 * получает данные из конкретной ячейки
 *
 * @param string $Index - индекс строки в массиве (таблице) начиная с 0
 *
 * @retrun mixed|null - если нет записи с таким индексом, то вернется null, если есть, то вернет значение
 */
public function getRow( $Index );

/**
 * добавляет строку данных из массива $row
 *
 * @param array $Data - данные для добавления
 */
public function addRow( array $Data );

/**
 * убирает данные с индексом $Index из массива DataArray,
 * если они установлены
 *
 * @param string $Index - индекс, который нужно исключить
 */
public function removeRow( $Index );

/**
 * @return array - массив с именами ключей в массиве DataArray
 */
public function getArrayKeys();

/**
 * очистка массива данных
 */
public function clear();


} // TRMCommonDataInterface
