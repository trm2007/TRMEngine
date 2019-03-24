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
 * @var string - ��� �������� ��� �������������� �������, ������ ��������� � ������ ID-���� �� ��
 */
protected $IdFieldName;

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
 * @return string - ���������� ��� �������� ��� �������������� �������, ������ ��������� � ������ ID-���� �� ��
 */
public function getIdFieldName()
{
    return $this->IdFieldName;
}

/**
 * @param string $IdFieldName -  * ������������� ��� �������� ��� �������������� �������, ������ ��������� � ������ ID-���� �� ��
 */
public function setIdFieldName($IdFieldName) 
{
    $this->IdFieldName = strval($IdFieldName);
}

/**
 * ���������� �������� ���� �� �������[$name] ��� �������� ������� $val = $obj->name;
 *
 * @param string $name - ��� �������� = ��� ���� � ������� ��
 * @return mixed - ��������, ��� ������� �� ��������
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
 * ������������� �������� ���� � �������[$name] ��� �������� ������� $val = $obj->name;
 *
 * @param string $name - ��� �������� = ��� ���� � ������� ��
 * @param mixed $val - �������� ��������-����
 */
public function __set($name, $val)
{
    if( !isset($this->{$name}) )
    {
        $this->setData( 0, $name, $val );
    }
}

/**
 * ���������� ��� ������� �������� ���� ������� ���������� �����!!!
 * ��� ����� ��������� ���� ������ ��� ����� � getIdFieldName()
 *
 * @return int|null - ID-�������
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
 * ������������� ��� ������� �������� ���� ������� ���������� �����!!!
 * ��� ����� ��������� ���� ������ ��� ����� � getIdFieldName()
 *
 * @param mixed - ID-�������
 */
public function setId($id)
{
    $this->setData( 0, $this->IdFieldName, $id );
}

/**
 * �������� ID-�������
 * ������������ setId(null);
 */
public function resetId()
{
    $this->setData( 0, $this->IdFieldName, null );
}

/**
 * ���������� �������� ���������� � ���� $fieldname
 * 
 * @param string $fieldname - ��� ����
 * @return mixed|null - ���� ���� �������� � ���� $fieldname, �� �������� ��� ��������, ���� null,
 */
public function getFieldValue( $fieldname )
{
    return $this->getData(0, $fieldname);
}

/**
 * ������������� �������� ���� $fieldname, ������ �������� ����� ��������,
 * ���� ���� � ����� ������ �� ���� � ������� ������, �� ��� �����������
 * 
 * @param type $fieldname - ��� ����, �������� �������� ����� ����������/��������
 * @param type $value - ����� ��������
 */
public function setFieldValue( $fieldname, $value )
{
    $this->setData(0, $fieldname, $value);
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