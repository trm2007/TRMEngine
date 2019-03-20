<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;

/**
 * ����� ��� ������ � ��������� ������, 
 * ���������� ������ ������������ �������� � ���� ���������� �������
 *
 * @author TRM
 */
class TRMDataObject implements TRMDataObjectInterface
{
/**
 * @var array - ��������� ������ ������
 */
protected $DataArray = array();

/**
 * @var integer - ������� ������� ���������, ��� ���������� ���������� ���������
 */
private $Position;

/**
 * ���������� ������, ����������� ������ ��� ������� ����������
 */
public function getOwnData()
{
    return $this->DataArray; 
}
/**
 * ������������� ������, ����������� ������ ��� ������� ����������, 
 * ������ �������� ��� ���������
 * 
 * @param array $data - ������ � �������, � ������� ���������� �������� ������� 
 */
public function setOwnData( array $data )
{
    $this->clear();
    $this->DataArray = $data;
}

/**
 * ���������� ��������� �� ������ = ���� ��������� ������
 * @return $this
 */
public function getDataObject()
{
    return $this;
}

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
    $this->clear();
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
 * @param integer $rownum - ����� ������
 * @param string $fieldname - ��� �������� ����
 * @return boolean - ���� ������, ���������� true, ���� ���� ����������� - false
 */
public function keyExists( $rownum, $fieldname )
{
    // ����� ������ ���
    if( !isset($this->DataArray[$rownum]) ) { return false; }
    // ������ ���� ���
    if( !array_key_exists($fieldname, $this->DataArray[$rownum]) ) { return false; }

    // ������� !
    return true;
}

/**
 * ���������� ������ � ���������� ������
 *
 * @param integer $rownum - ����� ������ � ������� (�������) ������� � 0
 * @param string $fieldname - ��� ���� (�������), � ������� ���������� ������ ��������
 * @param mixed $value - ���� ������������ ��������
 */
public function setData( $rownum, $fieldname, $value )
{
    // ��������� ������� ������ � ����� �������, ���� ���, �� ������� ��� ������ ������ � ����� ���������� ��������
    if( !isset($this->DataArray[$rownum]) )
    {
            $this->DataArray[$rownum] = array();
    }

    $this->DataArray[$rownum][$fieldname] = $value;
}

/**
 * �������� ������ �� ���������� ������
 *
 * @param integer $rownum - ����� ������ � ������� (�������) ������� � 0
 * @param string $fieldname - ��� ���� (�������), �� �������� ���������� ������ ��������
 *
 * @retrun mixed|null - ���� ��� ������ � ����� ������� ������ ��� ��� ���� � ����� ������ �������� null, ���� ����, �� ������ ��������
 */
public function getData( $rownum, $fieldname )
{
    // ���� ����� ������� �� �����������, �� ������������ 
    if( !$this->keyExists($rownum, $fieldname) ) { return null; }
    return $this->DataArray[$rownum][$fieldname];
}

/**
 * ���������� ������ ������ ��� ��������� ������ � ������� $rownum �� ������ �������
 * 
 * @parm integer $rownum - ����� ������ � ������� (�������) ������� � 0
 *
 * @return array|null - ������(������) ������ � ������������� �������, ��� null, ���� ������ ������ ���
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
 * ������������� ������ ��� ������ � ������� $rownum �� ������� $row
 * 
 * @parm integer $rownum - ����� ������ � ������� (�������) ������� � 0
 * @param array $row - ������-������ � �������
 *
 */
public function setRow( $rownum, array $row )
{
    $this->DataArray[$rownum] = $row;
}

/**
 * ��������� ������ ������ �� ������� $row
 *
 * @param array $row - ������-������ � ������� ��� ����������
 */
public function addRow( array $row )
{
	$this->DataArray[] = $row;
}

/**
 * ������� ������ � ������ � ������� $rownum � ������ �� ������� $row
 * ���� � ���������� ������� ���������� ����, ������� ��� ���� � ����� ������� ������,
 * �� ��� ���������� �� ����� ��������, 
 * ���� ����� ����� ���, �� ��� ��������� � �������
 *
 * @param integer $rownum - ����� ������ � ������� (�������) ������� � 0
 * @param array $row - ������-������ � ������� ��� ����������
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
 * �������� ����� ������ �� ���������� ������� DataArray ��� ���� �������� ������������ ��������
 *
 * @param array $looking - ������ �������� ��� ������ array( FieldName1 => Value1, FieldName2 => Value2, ... )
 *
 * @return integer|null - ���������� ����� ������-������ �� ������ ������� ��� null
 */
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

/**
 * �������� �������� ������ �� ���������� ������� DataArray ��� ���� �������� ������������ ��������
 *
 * @param array $looking - ������ �������� ��� ������ array( FieldName1 => Value1, FieldName2 => Value2, ... )
 *
 * @return array|null - ���������� ������-������� ������ �� ������ ������� ���� �������, ��� null � ��������� ������
 */
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

/**
 * ��������� ������� ����� � ��������� ������� � ������ ������ � ������� $rownum, 
 * �������� � ���� ���� �� �����, ������� ���������� �����
 *
 * @param integer $rownum - ����� ������, � ������� ���������� ��������, �� ���������� ������ ������, ������ � 0
 * @param &array $fieldnames - ������ �� ������ � ������� ����������� �����
 *
 * @return boolean - ���� ������� ��� ����, �� ������������ true, ���� ���� �� ���� �� �������, �� false
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
 * ��������� ������� ������ � ����� � ������� �� ������ $fieldnames � ������ � ������� $rownum
 *
 * @param integer $rownum - ����� ������, � ������� ���������� ��������, �� ���������� ������ ������, ������ � 0
 * @param &array $fieldnames - ������ �� ������ � ������� ����������� �����
 *
 * @return boolean - ���� ������� ���� � ����������� ��������, �� ������������ true, ����� false
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
 * ������ �� ���� ������� �������� ���� $FieldName �� ����� �������� $FieldValue, ���� ��������� ������
 *
 * @param string $FieldName - ��� ����-�������
 * @param mixed $FieldValue - ����� ��������
 */
public function changeAllValuesFor($FieldName, $FieldValue)
{
	foreach( $this->DataArray as &$row )
	{
		$row[$FieldName] = $FieldValue;
	}
}

/**
 * ������� �� ������� ������, � ������� ���� $FieldName ������������� �������� $FieldValue
 *
 * @param string $FieldName - ��� ����
 * @param mixed $FieldValue - ������� ��������
 * @param integer $count - ���������� ������� ��� ������/��������, ����������� 0 - ��� ���������
 *
 * @return integer - ���������� �������� ������� �� ��������� ���������
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
	// ���� ������� 0 � ������ ���������, �� count ����� �������������, � �� ������ ����� ���-�� ����������� �������� 0 - (-|count|) == 0+ |count| = |count|
	return ($start - $count);
}


/**
 * ������� ������ � ��������� ��������� ��� ��������� � ������
 */
public function clear()
{
    $this->DataArray = array();
    $this->Position = 0;
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
 * ������������� ���������� ������� ��������� � ������ - ���������� ���������� Iterator
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