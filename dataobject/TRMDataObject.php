<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataArray\TRMDataArray;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;

/**
 * ����� ��� ������ � ��������� ������, 
 * ���������� ������ ������������ �������� � ���� ���������� �������
 *
 * @author TRM
 */
class TRMDataObject extends TRMDataArray implements TRMDataObjectInterface
{

/**
 * ��������� ������� ���� � ������ fieldname � sub-������� $objectname
 * 
 * @param string $objectname - ��� sub-�������, ��� �������� ����������� ������� ���� $fieldname
 * @param string $fieldname - ��� �������� ����
 * 
 * @return boolean - ���� ������, ���������� true, ���� ���� ����������� - false
 */
public function fieldExists( $objectname, $fieldname )
{
    // ������ ������� ���
    if( !isset($this->DataArray[$objectname]) ) { return false; }
    // ������ ���� ���
    if( !array_key_exists($fieldname, $this->DataArray[$objectname]) ) { return false; }

    // ������� !
    return true;
}

/**
 * ���������� ������ � ���������� ������
 *
 * @param string $objectname - ��� sub-�������, ��� �������� ��������������� ������
 * @param string $fieldname - ��� ���� (�������), � ������� ���������� ������ ��������
 * @param mixed $value - ��������-������ ���� 
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
 * �������� ������ �� ���������� ������
 *
 * @param string $objectname - ��� sub-�������, ��� �������� ���������� ������
 * @param string $fieldname - ��� ���� (�������), �� �������� ���������� ������ ��������
 *
 * @retrun mixed|null - ���� ��� ������ � ����� ������� ������ ��� ��� ���� � ����� ������ �������� null, ���� ����, �� ������ ��������
 */
public function getData( $objectname, $fieldname )
{
    if( !$this->fieldExists($objectname, $fieldname) ) { return null; }
    
    return $this->DataArray[$objectname][$fieldname];
}

/**
 * ��������� ������� ������ � ����� � ������� �� ������ $fieldnames 
 * ��� ������� $objectname � ������ � ������� $rownum
 *
 * @param string $objectname - ��� ������� � ������ � ������� $rownum, ��� �������� ����������� ����� ������
 * @param &array $fieldnames - ������ �� ������ � ������� ����������� �����
 *
 * @return boolean - ���� ������� ���� � ����������� ��������, �� ������������ true, ����� false
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
 * �������� ����� ������ �� ���������� ������� DataArray ��� ���� �������� ������������ ��������
 *
 * @param array $looking - ������ �������� ��� ������ array( FieldName1 => Value1, FieldName2 => Value2, ... )
 *
 * @return integer|null - ���������� ����� ������-������ �� ������ ������� ��� null
 */
/*
public function findBy( array $looking )
{
	if( empty($looking) ) { return false; }
	// ���������� ���� ������ � ����������� ��������
	foreach( $this->DataArray as $current => $row )
	{
		$flag = true;
		foreach( $looking as $field => $val ) // ��������� ������ ������������� ����
		{
			// ���� ����� ���� �� ����������� � ����� �������, ��� �������� �� ���������,
			// ������ ��� ������ �� ��������, ��������� ���� � ��������� � ��������� ������
			if( !isset( $row[$field] ) || $row[$field] != $val ) { $flag = false; break; }
		}
		if( $flag ) { return $current; }
	}
	return null;
}
 * 
 */

/**
 * �������� �������� ������ �� ���������� ������� DataArray ��� ���� �������� ������������ ��������
 *
 * @param array $looking - ������ �������� ��� ������ array( FieldName1 => Value1, FieldName2 => Value2, ... )
 *
 * @return array|null - ���������� ������-������� ������ �� ������ ������� ���� �������, ��� null � ��������� ������
 */
/*
public function getBy( array $looking )
{
	if( empty($looking) ) { return null; }
	// ���������� ���� ������ � ����������� ��������
	foreach( $this->DataArray as $row )
	{
		$flag = true;
		foreach( $looking as $field => $val ) // ��������� ������ ������������� ����
		{
			// ���� ����� ���� �� ����������� � ����� �������, ��� �������� �� ���������,
			// ������ ��� ������ �� ��������, ��������� ���� � ��������� � ��������� ������
			if( !isset( $row[$field] ) || $row[$field] != $val ) { $flag = false; break; }
		}
		if( $flag ) { return $row; }
	}
	return null;
}
 * 
 */

/**
 * ��������� ������� ����� � ��������� ������� � ������ ������ � ������� $rownum, 
 * �������� � ���� ���� �� �����, ������� ���������� �����
 *
 * @param integer $rownum - ����� ������, � ������� ���������� ��������, �� ���������� ������ ������, ������ � 0
 * @param &array $fieldnames - ������ �� ������ � ������� ����������� �����
 *
 * @return boolean - ���� ������� ��� ����, �� ������������ true, ���� ���� �� ���� �� �������, �� false
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
 * ������ �� ���� ������� �������� ���� $FieldName �� ����� �������� $FieldValue
 *
 * @param string $ObjectName - ��� �������, � ������� �������� �������� 
 * @param string $FieldName - ��� ����-�������
 * @param mixed $FieldValue - ����� ��������
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
 * ������� �� ������� ������, � ������� ���� $FieldName ������������� �������� $FieldValue
 *
 * @param string $FieldName - ��� ����
 * @param mixed $FieldValue - ������� ��������
 * @param integer $count - ���������� ������� ��� ������/��������, ����������� 0 - ��� ���������
 *
 * @return integer - ���������� �������� ������� �� ��������� ���������
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
	// ���� ������� 0 � ������ ���������, �� count ����� �������������, � �� ������ ����� ���-�� ����������� �������� 0 - (-|count|) == 0+ |count| = |count|
	return ($start - $count);
}
 * 
 */



} // TRMDataObject