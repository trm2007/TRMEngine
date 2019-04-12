<?php

namespace TRMEngine\DataArray\Interfaces;

interface TRMDataArrayInterface extends \Countable, \ArrayAccess
{
/**
 * ���������� ���� ������ � �������, �������� ��������,
 * ��� ��� ������ ���������� �� �������� ( ������ PHP 5.3 ) !!!
 *
 * @return array
 */
public function getDataArray();

/**
 * ������ ������ ��� ����� ������� DataArray, ������ ������ ���������.
 * ������������ ������ ���������� ������� ���������,
 * ��� ��� ������������ ������ ��������� ������, ���� ��������� �� ����� ������!!!
 *
 * @param array $data - ������ � �������, � ������� ���������� �������� �������, 
 * ��� ��� ������ ���������� �� �������� ( ������ PHP 5.3 ) !!! 
 */
public function setDataArray( array $data );

/**
 * "���������" ��� ������� � �������, �������� �� ������������ �� ����������,
 * ��� ������������� ����� ������ ����� ���� ���������� � ������������ ��������, 
 * �� ������ ���� ��������� � ������ ������-������ ������ ����� ��������� ������
 *
 * @param array $data - ������ ��� ����������
 */
public function mergeDataArray( array $data );

/**
 * ��������� ������� ����� (���� � ������ fieldname) � ������ � ������� rownum
 * 
 * @param string $Index - ����������� ������ �������
 * 
 * @return boolean - ���� ������, ���������� true, ���� ���� ����������� - false
 */
public function keyExists( $Index );

/**
 * ���������� ������ � ���������� ������
 *
 * @param string $Index - ������ ������ � ������� (�������) ������� � 0
 * @param mixed $value - ��������-������ ���� 
 */
public function setRow( $Index, $value );

/**
 * �������� ������ �� ���������� ������
 *
 * @param string $Index - ������ ������ � ������� (�������) ������� � 0
 *
 * @retrun mixed|null - ���� ��� ������ � ����� ��������, �� �������� null, ���� ����, �� ������ ��������
 */
public function getRow( $Index );

/**
 * ��������� ������ ������ �� ������� $row
 *
 * @param array $Data - ������ ��� ����������
 */
public function addRow( array $Data );

/**
 * ������� ������� ������
 */
public function clear();


} // TRMCommonDataInterface
