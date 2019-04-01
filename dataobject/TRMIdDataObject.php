<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;

/**
 * ����������� ����� ��� ������� ������, ������� ����� ���� ��������� � �����������-���������,
 * ��� ��� ��� ������ ��� ����� ������, �� � ������ ������� ����� ���� ID-�������������,
 * � ��� �� �������� ���������� � ��������� ������� ����� $object->properties (����������� ������ __get � __set)
 *
 * @author TRM

 */
abstract class TRMIdDataObject extends TRMDataObject implements \ArrayAccess, TRMIdDataObjectInterface
{
/**
 * @var array - ��� �������� ��� �������������� �������, ������ ��������� � ������ ID-���� �� ��,
 * ������ ���� �������� � ������ �������� ������!!!
 */
// static protected $IdFieldName;

/**
 * ���������� ������ � ������� (������������ ������ ���� - 1-� ������), 
 * �������� ��������, ��� ��� ������ ���������� �� �������� ( ������ PHP 5.3 ) !!!
 *
 * @return array
 */
public function getOwnData()
{
    if( !count($this->DataArray) ) { return null; }
    return $this->DataArray[0];
}

/**
 * ������ ������ ��� ����� ������ ������� DataArray - 1-� ������, ������ ������ ���������.
 * ������������ ������ ���������� ������� ���������,
 * ��� ��� ������������ ������ ��������� ������, ���� ��������� �� ����� ������!!!
 *
 * @param array $data - ������ � �������, � ������� ���������� �������� �������, 
 * ��� ��� ������ ���������� �� �������� ( ������ PHP 5.3 ) !!! 
 */
public function setOwnData( array $data )
{
    $this->clear();
    $this->DataArray[0] = $data;
}

/**
 * @return array - ���������� ��� �������� ��� �������������� �������, ������ ��������� � ������ ID-���� �� ��,
 * ������������ ������ IdFieldName = array( ��� �������, ��� ID-���� � ������� )
 */
static public function getIdFieldName()
{
    return static::$IdFieldName;
}

/**
 * @param array $IdFieldName - ������������� ��� �������� ��� �������������� �������, 
 * ������ ��������� � ������ ID-���� �� ��,
 * ���������� ������ IdFieldName = array( ��� �������, ��� ID-���� � ������� )
 */
static public function setIdFieldName( array $IdFieldName ) 
{
    static::$IdFieldName[0] = reset($IdFieldName);
    static::$IdFieldName[1] = next($IdFieldName);
    reset($IdFieldName);
}

/**
 * ���������� �������� ���� �� �������[$name] ��� �������� ������� $val = $obj->name;
 *
 * @param string $objectname - ��� �������, ��� �������� ����� �������� ������ �� ���������� �����
 * @return array - ������ �� ���������� ����� ������ �������� ��������-����
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
 * ������������� �������� ���� � �������[$name] ��� �������� ������� $val = $obj->name;
 *
 * @param string $objectname - ��� �������, ��� �������� ����� ���������� ������ �� ���������� �����
 * @param array $val - ������ �� ���������� ����� ������ �������� ��������-����
 */
public function __set($objectname, array $val)
{
    if( !isset($this->{$objectname}) )
    {
        $this->DataArray[0][$objectname] = $val;
    }
}

/**
 * ���������� �������� ���� ���������� �����, 
 * ������� �������������� � ������ ���� �����������!!!
 * ��� ����� ��������� ���� ������ ��� ����� � getIdFieldName()
 *
 * @return mixed|null - ID-�������
 */
public function getId()
{
    // � 24.03.2019
    // IdFieldName - ��� ������ ���������� array( ��� �������, ��� ���� )
    if( !isset($this->IdFieldName[0]) || !isset($this->IdFieldName[1]) )
    {
        throw new TRMException( __METHOD__ . " - �� ���������� IdFieldName!");
    }

    if( !isset($this->DataArray[0]) ) { return null; }
    if( !isset($this->DataArray[0][$this->IdFieldName[0]]) ) { return null; }
    if( !isset($this->DataArray[0][$this->IdFieldName[0]][$this->IdFieldName[1]]) ) { return null; }

    $data = $this->DataArray[0][$this->IdFieldName[0]][$this->IdFieldName[1]];
    
    // ��������� �� ��������� null, ��� ��� ����� ���������� null � int ������ 0 
    // ������ ID ��� ���� ������ �������������...
    if( false === $data || "" === $data || null === $data ) { return null; }

    return $data;
}

/**
 * ������������� ��� ���� ����������� �������� ����� ����� ������������ � IdFieldName!!!
 * ��� ����� ��������� ���� ������ ��� ����� � getIdFieldName()
 *
 * @param mixed - ID-�������
 */
public function setId($id)
{
    if( !isset($this->IdFieldName[0]) || !isset($this->IdFieldName[1]) )
    {
        throw new TRMException( __METHOD__ . " - �� ���������� IdFieldName!");
    }
    $this->DataArray[0][$this->IdFieldName[0]][$this->IdFieldName[1]] = $id;
}

/**
 * �������� ID-�������
 * ������������ setId(null);
 */
public function resetId()
{
    $this->setData( 0, $this->IdFieldName[0], $this->IdFieldName[1], null );
}

/**
 * ���������� �������� ���������� � ���� $fieldname
 * 
 * @param string $objectname - ��� �������, ��� �������� ���������� ������
 * @param string $fieldname - ��� ����
 * @return mixed|null - ���� ���� �������� � ���� $fieldname, �� �������� ��� ��������, ���� null,
 */
public function getFieldValue( $objectname, $fieldname )
{
    return $this->getData(0, $objectname, $fieldname);
}

/**
 * ������������� �������� ���� $fieldname, ������ �������� ����� ��������,
 * ���� ���� � ����� ������ �� ���� � ������� ������, �� ��� �����������
 * 
 * @param string $objectname - ��� �������, ��� �������� ���������� ������
 * @param string $fieldname - ��� ����, �������� �������� ����� ����������/��������
 * @param mixed $value - ����� ��������
 */
public function setFieldValue( $objectname, $fieldname, $value )
{
    $this->setData(0, $objectname, $fieldname, $value);
}

/**
 * ���� ���������� ��������� ArrayAccess,
 * ��� ��� ������ ��������� ��� ������ � ����� �������, 
 * �� ��� ������ ���������� ������ � 0-� ������� ������
 */

/**
 * ����������� �������� ��������� �������� - ���������� ���������� ArrayAccess
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
 * ����������, ���������� �� �������� �������� (����) - ���������� ���������� ArrayAccess
 * 
 * @param int $offset
 * @return array
 */
public function offsetExists($offset)
{
    return isset($this->DataArray[0][$offset]);
}

/**
 * ������� ��������, ������ �� ��������� �������� - ���������� ���������� ArrayAccess
 * 
 * @param int $offset
 */
public function offsetUnset($offset)
{
    unset($this->DataArray[0][$offset]);
}

/**
 * ���������� �������� �������� (����) - ���������� ���������� ArrayAccess
 * 
 * @param int $offset
 * @return array
 */
public function offsetGet($offset)
{
    return isset($this->DataArray[0][$offset]) ? $this->DataArray[0][$offset] : null;
}


} // TRMIdDataObject