<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataArray\TRMDataArray;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;

/**
 * класс для работы с объектами данных, 
 * фактически данные представлены таблицей в виде двумерного массива
 *
 * @author TRM
 */
class TRMDataObject extends TRMDataArray implements TRMDataObjectInterface
{

/**
 * проверяет наличие поля с именем fieldname в sub-объекте $objectname
 * 
 * @param string $objectname - имя sub-объекта, для которого проверяется наличие поля $fieldname
 * @param string $fieldname - имя искомого поля
 * 
 * @return boolean - если найден, возвращает true, если ключ отсутствует - false
 */
public function fieldExists( $objectname, $fieldname )
{
    // Такого объекта нет
    if( !isset($this->DataArray[$objectname]) ) { return false; }
    // Такого поля нет
    if( !array_key_exists($fieldname, $this->DataArray[$objectname]) ) { return false; }

    // найдено !
    return true;
}

/**
 * записывает данные в конкретную ячейку
 *
 * @param string $objectname - имя sub-объекта, для которого устанавливаются данные
 * @param string $fieldname - имя поля (столбца), в которое производим запись значения
 * @param mixed $value - значение-данные поля 
 */
public function setData( $objectname, $fieldname, $value )
{
    if( !isset($this->DataArray[$objectname]) )
    {
        $this->DataArray[$objectname] = array( $fieldname => $value );
    }
    else
    {
        $this->DataArray[$objectname][$fieldname] = $value;
    }
}

/**
 * получает данные из конкретной ячейки
 *
 * @param string $objectname - имя sub-объекта, для которого получаются данные
 * @param string $fieldname - имя поля (столбца), из которого производим чтение значения
 *
 * @retrun mixed|null - если нет записи с таким номером строки или нет поля с таким именем вернется null, если есть, то вернет значение
 */
public function getData( $objectname, $fieldname )
{
    if( !$this->fieldExists($objectname, $fieldname) ) { return null; }
    
    return $this->DataArray[$objectname][$fieldname];
}

/**
 * проверяет наличие данных в полях с именами из набора $fieldnames 
 * для объекта $objectname в строке с номером $rownum
 *
 * @param string $objectname - имя объекта в строке с номером $rownum, для которого проверяется набор данных
 * @param &array $fieldnames - ссылка на массив с именами проверяемых полей
 *
 * @return boolean - если найдены поля и установлены значения, то возвращается true, иначе false
 */
public function presentDataIn( $objectname, array &$fieldnames )
{
    if( empty( $this->DataArray ) ) { return false; }
    if( !isset( $this->DataArray[$objectname] ) ) { return false; }

    foreach( $fieldnames as $field )
    {
        if( !array_key_exists($field, $this->DataArray[$objectname]) ||
            empty( $this->DataArray[$objectname][$field] ) )
        {
            return false;
        }
    }
    return true;
}

/**
 * получаем номер строки из локального массива DataArray где поля содержат передаваемые значения
 *
 * @param array $looking - массив значений для поиска array( FieldName1 => Value1, FieldName2 => Value2, ... )
 *
 * @return integer|null - возвращает номер строки-записи из общего массива или null
 */
/*
public function findBy( array $looking )
{
	if( empty($looking) ) { return false; }
	// перебираем весь массив с полученными записями
	foreach( $this->DataArray as $current => $row )
	{
		$flag = true;
		foreach( $looking as $field => $val ) // проверяем каждое запрашиваемое поле
		{
			// если такое поле не установлено в общем массиве, или значения не совпадают,
			// значит эта запись не подходит, прерываем цикл и переходим к следующей записи
			if( !isset( $row[$field] ) || $row[$field] != $val ) { $flag = false; break; }
		}
		if( $flag ) { return $current; }
	}
	return null;
}
 * 
 */

/**
 * получаем значение строки из локального массива DataArray где поля содержат передаваемые значения
 *
 * @param array $looking - массив значений для поиска array( FieldName1 => Value1, FieldName2 => Value2, ... )
 *
 * @return array|null - возвращает массив-строчку записи из общего массива если найдена, или null в противном случае
 */
/*
public function getBy( array $looking )
{
	if( empty($looking) ) { return null; }
	// перебираем весь массив с полученными записями
	foreach( $this->DataArray as $row )
	{
		$flag = true;
		foreach( $looking as $field => $val ) // проверяем каждое запрашиваемое поле
		{
			// если такое поле не установлено в общем массиве, или значения не совпадают,
			// значит эта запись не подходит, прерываем цикл и переходим к следующей записи
			if( !isset( $row[$field] ) || $row[$field] != $val ) { $flag = false; break; }
		}
		if( $flag ) { return $row; }
	}
	return null;
}
 * 
 */

/**
 * проверяет наличие полей с заданными именами в строке данных с номером $rownum, 
 * значение в этом поле не важно, главное присутсвие ключа
 *
 * @param integer $rownum - номер строки, в которой происходит проверка, из локального набора данных, отсчет с 0
 * @param &array $fieldnames - ссылка на массив с именами проверяемых полей
 *
 * @return boolean - если найдены все поля, то возвращается true, если хотя бы одно не найдено, то false
 */
/*
public function presentFieldNamesIn( $rownum, array &$fieldnames )
{
	if( !is_array($this->DataArray[$rownum]) ) { return false; }
	foreach( $fieldnames as $field )
	{
		if( !array_key_exists( $field, $this->DataArray[$rownum] ) ) { return false; }
	}
	return true;
}
 * 
 */

/**
 * меняет во всех записях значение поля $FieldName на новое значение $FieldValue
 *
 * @param string $ObjectName - имя объекта, в котором меняется значение 
 * @param string $FieldName - имя поля-колонки
 * @param mixed $FieldValue - новое значение
 */
/*
public function changeAllValuesFor($ObjectName, $FieldName, $FieldValue)
{
    foreach( $this->DataArray as $i => &$row)
    {
        if( key_exists($ObjectName, $this->DataArray[$i]) && 
            key_exists($FieldName, $this->DataArray[$i][$ObjectName]) )
        {
            $row[$ObjectName][$FieldName] = $FieldValue;
        }
    }
}
 * 
 */

/**
 * удаляет из массива записи, в которых поле $FieldName удовлетворяет значению $FieldValue
 *
 * @param string $FieldName - имя поля
 * @param mixed $FieldValue - искомое значение
 * @param integer $count - количество записей для поиска/удаления, поумолчанию 0 - все найденные
 *
 * @return integer - количество убранных записей из локальной коллекции
 */
/*
public function removeBy( $FieldName, $FieldValue, $count = 0 )
{
	$start = $count;
	foreach( $this->DataArray as $index => $row )
	{
		if( isset($row[$FieldName]) && $row[$FieldName] == $FieldValue )
		{
			unset( $this->DataArray[$index] );
			$count--;
			if( $count == 0 ) { break; }
		}
	}
	$this->DataArray = array_values($this->DataArray);
	// если передан 0 и записи удалялись, то count будет отрицательным, а по модулю равен кол-ву совершенных удалений 0 - (-|count|) == 0+ |count| = |count|
	return ($start - $count);
}
 * 
 */



} // TRMDataObject