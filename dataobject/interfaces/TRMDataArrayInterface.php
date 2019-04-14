<?php

namespace TRMEngine\DataArray\Interfaces;

interface TRMDataArrayInterface extends \Countable, \ArrayAccess
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
 * проверяет наличие ключа (поля с именем fieldname) у строки с номером rownum
 * 
 * @param string $Index - проверяемый индекс массива
 * 
 * @return boolean - если найден, возвращает true, если ключ отсутствует - false
 */
public function keyExists( $Index );

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
 * очистка массива данных
 */
public function clear();


} // TRMCommonDataInterface
