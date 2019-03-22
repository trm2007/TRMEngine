<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\TRMDataObject;

/**
 * ����� ��� ������ � ���������� �������� ������, 
 * ���������� ������ ������������ �������� � ���� ���������� �������
 *
 * @author TRM
 */
class TRMCollectionDataObject extends TRMDataObject implements \ArrayAccess // IteratorAggregate
{

/**
 * ����������� �������� ��������� �������� - ���������� ���������� ArrayAccess
 * 
 * @param int $offset
 * @param array $value
 */
public function offsetSet($offset, $value)
{
    if (is_null($offset)) {
        $this->DataArray[] = $value;
    } else {
        $this->DataArray[$offset] = $value;
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
    return isset($this->DataArray[$offset]);
}

/**
 * ������� ��������, ������ �� ��������� �������� - ���������� ���������� ArrayAccess
 * 
 * @param int $offset
 */
public function offsetUnset($offset)
{
    unset($this->DataArray[$offset]);
}

/**
 * ���������� �������� �������� (����) - ���������� ���������� ArrayAccess
 * 
 * @param int $offset
 * @return array
 */
public function offsetGet($offset)
{
    return isset($this->DataArray[$offset]) ? $this->DataArray[$offset] : null;
}


} // TRMCollectionDataObject