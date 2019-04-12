<?php

namespace TRMEngine\DataArray;

use TRMEngine\DataArray\Interfaces\TRMDataArrayInterface;

/**
 * ����� ��� ������ � �������� ������, 
 *
 * @author TRM
 */
class TRMDataArray implements TRMDataArrayInterface
{
/**
 * @var array - ������ ������
 */
protected $DataArray = array();

/**
 * ���������� ���� ������ � �������, �������� ��������,
 * ��� ��� ������ ���������� �� �������� ( ������ PHP 5.3 ) !!!
 *
 * @return array
 */
public function getDataArray()
{
    return $this->DataArray;
}

/**
 * ������ ������ ��� ����� ������� DataArray, ������ ������ ���������.
 * ������������ ������ ���������� ������� ���������,
 * ��� ��� ������������ ������ ��������� ������, ���� ��������� �� ����� ������!!!
 *
 * @param array $data - ������ � �������, � ������� ���������� �������� �������, 
 * ��� ��� ������ ���������� �� �������� ( ������ PHP 5.3 ) !!! 
 */
public function setDataArray( array $data )
{
    $this->DataArray = $data;
}

/**
 * "���������" ��� ������� � �������, �������� �� ������������ �� ����������,
 * ��� ������������� ����� ������ ����� ���� ���������� � ������������ ��������, 
 * �� ������ ���� ��������� � ������ ������-������ ������ ����� ��������� ������
 *
 * @param array $data - ������ ��� ����������
 */
public function mergeDataArray( array $data )
{
    $this->DataArray = array_merge( $this->DataArray, $data );
}

/**
 * ��������� ������� ����� (���� � ������ fieldname) � ������ � ������� rownum
 * 
 * @param string $Index - ����������� ������ �������
 * 
 * @return boolean - ���� ������, ���������� true, ���� ���� ����������� - false
 */
public function keyExists( $Index )
{
    // ������ ���� ���
    if( !array_key_exists($Index, $this->DataArray) ) { return false; }

    // ������� !
    return true;
}

/**
 * ���������� ������ � ���������� ������
 *
 * @param string $Index - ������ ������ � ������� (�������) ������� � 0
 * @param mixed $value - ��������-������ ���� 
 */
public function setRow( $Index, $value )
{
    $this->DataArray[$Index] = $value;
}

/**
 * �������� ������ �� ���������� ������
 *
 * @param string $Index - ������ ������ � ������� (�������) ������� � 0
 *
 * @retrun mixed|null - ���� ��� ������ � ����� ��������, �� �������� null, ���� ����, �� ������ ��������
 */
public function getRow( $Index )
{
    // ���� ����� ������ �� ����������, �� ������������ null
    if( !$this->keyExists($Index) ) { return null; }
    return $this->DataArray[$Index];
}

/**
 * ��������� ������ ������ �� ������� $row
 *
 * @param array $Data - ������ ��� ����������
 */
public function addRow( array $Data )
{
    $this->DataArray[] = $Data;
}

/**
 * ������� ������� ������
 */
public function clear()
{
    $this->DataArray = array();
}


/**
 * ���������� ������ ���������� Countable
 *
 * @return integer - ���������� ��������� � ������� DataArray
 */
public function count()
{
    return count($this->DataArray);
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
    $this->DataArray[$offset] = $value;
}

public function offsetUnset($offset)
{
    unset( $this->DataArray[$offset] );
}


} // TRMDataArray