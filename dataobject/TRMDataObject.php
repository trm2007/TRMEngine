<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;

/**
 * класс для работы с объектами данных, 
 * фактически данные представлены таблицей в виде двумерного массива
 *
 * @author TRM
 */
class TRMDataObject implements TRMDataObjectInterface
{
/**
 * @var array - двумерный массив данных
 */
protected $DataArray = array();

/**
 * @var integer - текущая позиция указателя, для реализации интерфейса итератора
 */
private $Position;

/**
 * возвращает данные, характерные только для данного экземпляра
 */
public function getOwnData()
{
    return $this->DataArray; 
}
/**
 * устанавливает данные, характерные только для данного экземпляра, 
 * старые значения все удаляются
 * 
 * @param array $data - массив с данными, в объекте сохранится дубликат массива 
 */
public function setOwnData( array $data )
{
    $this->clear();
    $this->DataArray = $data;
}

/**
 * возвращает указатель на объект = свой экземпляр класса
 * @return $this
 */
public function getDataObject()
{
    return $this;
}

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
    $this->clear();
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
 * @param integer $rownum - номер строки
 * @param string $fieldname - имя искомого поля
 * @return boolean - если найден, возвращает true, если ключ отсутствует - false
 */
public function keyExists( $rownum, $fieldname )
{
    // Такой строки нет
    if( !isset($this->DataArray[$rownum]) ) { return false; }
    // Такого поля нет
    if( !array_key_exists($fieldname, $this->DataArray[$rownum]) ) { return false; }

    // найдено !
    return true;
}

/**
 * записывает данные в конкретную ячейку
 *
 * @param integer $rownum - номер строки в массиве (таблице) начиная с 0
 * @param string $fieldname - имя поля (столбца), в которое производим запись значения
 * @param mixed $value - само записываемое значение
 */
public function setData( $rownum, $fieldname, $value )
{
    // проверяем наличие строки с таким номером, если нет, то создаем как пустой массив и затем записываем значение
    if( !isset($this->DataArray[$rownum]) )
    {
            $this->DataArray[$rownum] = array();
    }

    $this->DataArray[$rownum][$fieldname] = $value;
}

/**
 * получает данные из конкретной ячейки
 *
 * @param integer $rownum - номер строки в массиве (таблице) начиная с 0
 * @param string $fieldname - имя поля (столбца), из которого производим чтение значения
 *
 * @retrun mixed|null - если нет записи с таким номером строки или нет поля с таким именем вернется null, если есть, то вернет значение
 */
public function getData( $rownum, $fieldname )
{
    // если таике индексы не установлены, то возвращается 
    if( !$this->keyExists($rownum, $fieldname) ) { return null; }
    return $this->DataArray[$rownum][$fieldname];
}

/**
 * возвращает массив данных для указанной строки с номером $rownum из общего массива
 * 
 * @parm integer $rownum - номер строки в массиве (таблице) начиная с 0
 *
 * @return array|null - строка(массив) данных с запрашиваемым номером, или null, если такого номера нет
 */
public function getRow( $rownum )
{
    if( !isset($this->DataArray[$rownum]) )
    {
            return null;
    }
    return $this->DataArray[$rownum];
}

/**
 * устанавливает данные для строки с номером $rownum из массива $row
 * 
 * @parm integer $rownum - номер строки в массиве (таблице) начиная с 0
 * @param array $row - строка-массив с данными
 *
 */
public function setRow( $rownum, array $row )
{
    $this->DataArray[$rownum] = $row;
}

/**
 * добавляет строку данных из массива $row
 *
 * @param array $row - строка-массив с данными для добавления
 */
public function addRow( array $row )
{
	$this->DataArray[] = $row;
}

/**
 * склейка данных в строке с номером $rownum и данных из массива $row
 * если в переданном массиве содержатся поля, которые уже есть в общем массиве данных,
 * то они заменяются на новые значения, 
 * если таких полей нет, то они добавятся с данными
 *
 * @param integer $rownum - номер строки в массиве (таблице) начиная с 0
 * @param array $row - массив-строка с данными для соединения
 */
public function mergeRow( $rownum, array $row )
{
	if( isset($this->DataArray[$rownum]) )
	{
		$this->DataArray[$rownum] = array_merge($this->DataArray[$rownum], $row);
	}
	else
	{
		$this->DataArray[$rownum] = $row;
	}
}

/**
 * получаем номер строки из локального массива DataArray где поля содержат передаваемые значения
 *
 * @param array $looking - массив значений для поиска array( FieldName1 => Value1, FieldName2 => Value2, ... )
 *
 * @return integer|null - возвращает номер строки-записи из общего массива или null
 */
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

/**
 * получаем значение строки из локального массива DataArray где поля содержат передаваемые значения
 *
 * @param array $looking - массив значений для поиска array( FieldName1 => Value1, FieldName2 => Value2, ... )
 *
 * @return array|null - возвращает массив-строчку записи из общего массива если найдена, или null в противном случае
 */
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

/**
 * проверяет наличие полей с заданными именами в строке данных с номером $rownum, 
 * значение в этом поле не важно, главное присутсвие ключа
 *
 * @param integer $rownum - номер строки, в которой происходит проверка, из локального набора данных, отсчет с 0
 * @param &array $fieldnames - ссылка на массив с именами проверяемых полей
 *
 * @return boolean - если найдены все поля, то возвращается true, если хотя бы одно не найдено, то false
 */
public function presentFieldNamesIn( $rownum, array &$fieldnames )
{
	if( !is_array($this->DataArray[$rownum]) ) { return false; }
	foreach( $fieldnames as $field )
	{
		if( !array_key_exists( $field, $this->DataArray[$rownum] ) ) { return false; }
	}
	return true;
}

/**
 * проверяет наличие данных в полях с именами из набора $fieldnames в строке с номером $rownum
 *
 * @param integer $rownum - номер строки, в которой происходит проверка, из локального набора данных, отсчет с 0
 * @param &array $fieldnames - ссылка на массив с именами проверяемых полей
 *
 * @return boolean - если найдены поля и установлены значения, то возвращается true, иначе false
 */
public function presentDataIn( $rownum, array &$fieldnames )
{
    if( !isset( $this->DataArray[$rownum] ) ) { return false; }
//	if( !is_array($this->DataArray[$rownum]) ) { return false; }
    foreach( $fieldnames as $field )
    {
            if( !array_key_exists($field, $this->DataArray[$rownum]) || empty( $this->DataArray[$rownum][$field] ) ) { return false; }
    }
    return true;
}

/**
 * меняет во всех записях значение поля $FieldName на новое значение $FieldValue, если разрешена запись
 *
 * @param string $FieldName - имя поля-колонки
 * @param mixed $FieldValue - новое значение
 */
public function changeAllValuesFor($FieldName, $FieldValue)
{
	foreach( $this->DataArray as &$row )
	{
		$row[$FieldName] = $FieldValue;
	}
}

/**
 * удаляет из массива записи, в которых поле $FieldName удовлетворяет значению $FieldValue
 *
 * @param string $FieldName - имя поля
 * @param mixed $FieldValue - искомое значение
 * @param integer $count - количество записей для поиска/удаления, поумолчанию 0 - все найденные
 *
 * @return integer - количество убранных записей из локальной коллекции
 */
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


/**
 * очистка данных и установка указателя для итератора в начало
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


/**
 * Устанавливает внутренний счетчик итератора в начало - реализация интерфейса Iterator
 */
public function rewind()
{
    $this->Position = 0;
}

public function current()
{
    return $this->DataArray[$this->Position]; //  $this->DataArray[$this->Position];
}

public function key()
{
    return $this->Position;
}

public function next()
{
    ++$this->Position;
}

public function valid()
{
    return isset($this->DataArray[$this->Position]);
}


} // TRMDataObject