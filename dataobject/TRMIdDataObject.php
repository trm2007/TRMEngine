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
 * @var string - имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД
 */
protected $IdFieldName;

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
 * @return string - возвращает имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД
 */
public function getIdFieldName()
{
    return $this->IdFieldName;
}

/**
 * @param string $IdFieldName -  * устанавливает имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД
 */
public function setIdFieldName($IdFieldName) 
{
    $this->IdFieldName = strval($IdFieldName);
}

/**
 * возвращает значение поля из массива[$name] как свойство объекта $val = $obj->name;
 *
 * @param string $name - имя свойства = имя поля в таблице БД
 * @return mixed - значение, тип заранее не известен
 */
public function __get($name)
{
    if( !isset($this->{$name}) )
    {
        return $this->getData( 0, $name );
    }

    return null;
}

/**
 * устанавливает значение поля в массиве[$name] как свойство объекта $val = $obj->name;
 *
 * @param string $name - имя свойства = имя поля в таблице БД
 * @param mixed $val - значение свойства-поля
 */
public function __set($name, $val)
{
    if( !isset($this->{$name}) )
    {
        $this->setData( 0, $name, $val );
    }
}

/**
 * возвращает для объекта значение поля первого первичного ключа!!!
 * для этого первичный ключ должен быт задан в getIdFieldName()
 *
 * @return int|null - ID-объекта
 */
public function getId()
{
    $data = $this->getData( 0, $this->IdFieldName );
    if( $data === false || $data === null || $data === "" ) 
    {
        return null;
    }

    return $data;
}

/**
 * устанавливает для объекта значение поля первого первичного ключа!!!
 * для этого первичный ключ должен быт задан в getIdFieldName()
 *
 * @param mixed - ID-объекта
 */
public function setId($id)
{
    $this->setData( 0, $this->IdFieldName, $id );
}

/**
 * обнуляет ID-объекта
 * эквивалентен setId(null);
 */
public function resetId()
{
    $this->setData( 0, $this->IdFieldName, null );
}

/**
 * возврашает значение хранящееся в поле $fieldname
 * 
 * @param string $fieldname - имя поля
 * @return mixed|null - если есть значение в поле $fieldname, то вернется его значение, либо null,
 */
public function getFieldValue( $fieldname )
{
    return $this->getData(0, $fieldname);
}

/**
 * устанавливает значение поля $fieldname, старое значение будет потеряно,
 * если поля с таким именем не было в объекте данных, то оно установится
 * 
 * @param type $fieldname - имя поля, значение которого нужно установить/изменить
 * @param type $value - новое значение
 */
public function setFieldValue( $fieldname, $value )
{
    $this->setData(0, $fieldname, $value);
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