<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;

/**
 * абстрактный класс для объекта данных, которые могут быть сохранены в репозитории-хранилище,
 * так как это данные для одной записи, то у такого объекта может быть ID-идентификатор,
 * а так же возможно обращаться к свойствам объекта через $object->properties (реализованы методы __get и __set)
 *
 * @author TRM

 */
abstract class TRMIdDataObject extends TRMDataObject implements \ArrayAccess, TRMIdDataObjectInterface
{
/**
 * @var array - имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД,
 * должен быть объявлен в каждом дочернем классе!!!
 */
// static protected $IdFieldName;

/**
 * возвращает массив с данными (возвращается только одна - 1-я строка), 
 * вернется дубликат, так как массив передается по значению ( версия PHP 5.3 ) !!!
 *
 * @return array
 */
public function getOwnData()
{
    if( !count($this->DataArray) ) { return null; }
    return $this->DataArray[0];
}

/**
 * задает данные для одной строки массива DataArray - 1-я строка, старые данные стираются.
 * пользоваться прямым присвоение следует осторожно,
 * так как передаваться должен двумерный массив, даже состоящий из одной строки!!!
 *
 * @param array $data - массив с данными, в объекте сохранится дубликат массива, 
 * так как массив передается по значению ( версия PHP 5.3 ) !!! 
 */
public function setOwnData( array $data )
{
    $this->clear();
    $this->DataArray[0] = $data;
}

/**
 * @return array - возвращает имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД,
 * возвращается массив IdFieldName = array( имя объекта, имя ID-поле в объекте )
 */
static public function getIdFieldName()
{
    return static::$IdFieldName;
}

/**
 * @param array $IdFieldName - устанавливает имя свойства для идентификатора объекта, 
 * обычно совпадает с именем ID-поля из БД,
 * передается массив IdFieldName = array( имя объекта, имя ID-поле в объекте )
 */
static public function setIdFieldName( array $IdFieldName ) 
{
    static::$IdFieldName[0] = reset($IdFieldName);
    static::$IdFieldName[1] = next($IdFieldName);
    reset($IdFieldName);
}

/**
 * возвращает значение поля из массива[$name] как свойство объекта $val = $obj->name;
 *
 * @param string $objectname - имя объекта, для которого нужно получить массив со значениями полей
 * @return array - массив со значениями полей объект значение свойства-поля
 */
public function __get($objectname)
{
    if( !isset($this->{$objectname}) )
    {
        return $this->DataArray[0][$objectname];
    }

    return null;
}

/**
 * устанавливает значение поля в массиве[$name] как свойство объекта $val = $obj->name;
 *
 * @param string $objectname - имя объекта, для которого нужно установить массив со значениями полей
 * @param array $val - массив со значениями полей объект значение свойства-поля
 */
public function __set($objectname, array $val)
{
    if( !isset($this->{$objectname}) )
    {
        $this->DataArray[0][$objectname] = $val;
    }
}

/**
 * возвращает значение поля первичного ключа, 
 * первого встретивщегося в наборе всех подобъектов!!!
 * для этого первичный ключ должен быт задан в getIdFieldName()
 *
 * @return mixed|null - ID-объекта
 */
public function getId()
{
    // с 24.03.2019
    // IdFieldName - это массив содержащий array( имя объекта, имя поля )
    if( !isset($this->IdFieldName[0]) || !isset($this->IdFieldName[1]) )
    {
        throw new TRMException( __METHOD__ . " - не установлен IdFieldName!");
    }

    if( !isset($this->DataArray[0]) ) { return null; }
    if( !isset($this->DataArray[0][$this->IdFieldName[0]]) ) { return null; }
    if( !isset($this->DataArray[0][$this->IdFieldName[0]][$this->IdFieldName[1]]) ) { return null; }

    $data = $this->DataArray[0][$this->IdFieldName[0]][$this->IdFieldName[1]];
    
    // проверяем на равенство null, так как далее приведение null к int вернет 0 
    // раньше ID мог быть только целочисленным...
    if( false === $data || "" === $data || null === $data ) { return null; }

    return $data;
}

/**
 * устанавливает для всех подобъектов значения полей ключа совпадающего с IdFieldName!!!
 * для этого первичный ключ должен быт задан в getIdFieldName()
 *
 * @param mixed - ID-объекта
 */
public function setId($id)
{
    if( !isset($this->IdFieldName[0]) || !isset($this->IdFieldName[1]) )
    {
        throw new TRMException( __METHOD__ . " - не установлен IdFieldName!");
    }
    $this->DataArray[0][$this->IdFieldName[0]][$this->IdFieldName[1]] = $id;
}

/**
 * обнуляет ID-объекта
 * эквивалентен setId(null);
 */
public function resetId()
{
    $this->setData( 0, $this->IdFieldName[0], $this->IdFieldName[1], null );
}

/**
 * возврашает значение хранящееся в поле $fieldname
 * 
 * @param string $objectname - имя объекта, для которого получаются данные
 * @param string $fieldname - имя поля
 * @return mixed|null - если есть значение в поле $fieldname, то вернется его значение, либо null,
 */
public function getFieldValue( $objectname, $fieldname )
{
    return $this->getData(0, $objectname, $fieldname);
}

/**
 * устанавливает значение поля $fieldname, старое значение будет потеряно,
 * если поля с таким именем не было в объекте данных, то оно установится
 * 
 * @param string $objectname - имя объекта, для которого получаются данные
 * @param string $fieldname - имя поля, значение которого нужно установить/изменить
 * @param mixed $value - новое значение
 */
public function setFieldValue( $objectname, $fieldname, $value )
{
    $this->setData(0, $objectname, $fieldname, $value);
}

/**
 * ниже реализован интерфейс ArrayAccess,
 * так как объект создается для работы с одной записью, 
 * то вся работа происходит только с 0-й строкой данных
 */

/**
 * Присваивает значение заданному смещению - реализация интерфейса ArrayAccess
 * 
 * @param int $offset
 * @param array $value
 */
public function offsetSet($offset, $value)
{
    if (is_null($offset)) {
        $this->DataArray[0][] = $value;
    } else {
        $this->DataArray[0][$offset] = $value;
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
    return isset($this->DataArray[0][$offset]);
}

/**
 * Удаляет смещение, объект по заданному смещению - реализация интерфейса ArrayAccess
 * 
 * @param int $offset
 */
public function offsetUnset($offset)
{
    unset($this->DataArray[0][$offset]);
}

/**
 * Возвращает заданное смещение (ключ) - реализация интерфейса ArrayAccess
 * 
 * @param int $offset
 * @return array
 */
public function offsetGet($offset)
{
    return isset($this->DataArray[0][$offset]) ? $this->DataArray[0][$offset] : null;
}


} // TRMIdDataObject