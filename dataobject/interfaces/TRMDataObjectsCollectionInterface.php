<?php

namespace TRMEngine\DataObject\Interfaces;

use TRMEngine\DataObject\Exceptions\TRMDataObjectsCollectionWrongIndexException;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;

/**
 * ����� ��� ������ � ����������� ���������� �������� DataObject
 * 
 * @version 2019-03-29
 */
interface TRMDataObjectsCollectionInterface extends \ArrayAccess, \Iterator, \Countable
{
/**
 * @param int $Index - ������ �������������� ������� � �������-���������
 * 
 * @return TRMDataObjectInterface - ������ ������
 * @throws TRMDataObjectsCollectionWrongIndexException
 */
public function getDataObject($Index);

/**
 * @param int $Index - ������������� ������ ������� � ��������� ��������
 * @param TRMDataObjectInterface $DataObject - ������ ��� ��������� � ���������
 * 
 * @throws TRMDataObjectsCollectionWrongIndexException
 */
public function setDataObject($Index, TRMDataObjectInterface $DataObject);

/**
 * @param TRMDataObjectInterface $DataObject - ������� ��� ������ � ���������
 * @param bool $AddDuplicateFlag - ���� ���� ���� ���������� � false, �� � ��������� �� ��������� ��������� ��������,
 * ���� ���������� � TRUE, �� ������ ��������� ��� �����,
 * ���� ���� �� ��������� ��� �������������,
 * �� ��������� - false (����� �� �����������)
 * 
 * @return boolean - ���� ������ �������� � ���������, �� �������� TRUE, ����� FALSE
 */
public function addDataObject( TRMDataObjectInterface $DataObject, $AddDuplicateFlag = false );

/**
 * ���������, ���� �� � ��������� ������,
 * ������ ������ �� ���� ������
 * 
 * @param TRMDataObjectInterface $Object
 * @return boolean
 */
public function hasDataObject( TRMDataObjectInterface $Object );

/**
 * ��������� � ��������� ���������� ������ ���������,
 * ���� ������ ������ ������ ��� ��� � ����� �������,
 * ������ �� ������ �������, � ������ �� ���� �� ���� �������
 * 
 * @param TRMDataObjectsCollection $Collection
 * @param bool $AddDuplicateFlag - ���� ���� ���� ���������� � false, �� � ��������� �� ��������� ��������� ��������,
 * ���� ���������� � TRUE, �� ����� ��������� ��������� ��� ���� � ������������, �� ����� ����������,
 * ���� ���� ��� ��������� ��� �������������, �� ��������� - false (����� �� �����������)
 */
public function mergeCollection(TRMDataObjectsCollectionInterface $Collection, $AddDuplicateFlag = false );

/**
 * ������� ������-��������� � ��������� ������,
 * ��� ��� � ������� �������� ������ ������, 
 * �� ���� ������� �������� � ������, ���� �� ���-�� ����������
 */
public function clearCollection();

} // TRMDataObjectsCollectionInterface


interface TRMTypedCollection extends TRMDataObjectsCollectionInterface
{
/**
 * @return string - ��� ����������� �������� � ���������� ������� ����
 */
public function getObjectsType();

/**
 * ��������� ������������ ���� ������� �������������� ��� ���������
 * 
 * @param TRMDataObjectInterface $DataObject - ����������� ������
 * 
 * @throws TRMDataObjectSCollectionWrongTypeException
 */
public function validateObject(TRMDataObjectInterface $DataObject);

/**
 * ������ �� ���� ������� �������� ���� $FieldName �� ����� �������� $FieldValue, ���� ��������� ������
 *
 * @param string $ObjectName - ��� �������, � ������� �������� �������� 
 * @param string $FieldName - ��� ���� � �������� ������
 * @param mixed $FieldValue - ����� ��������
 */
public function changeAllValuesFor($ObjectName, $FieldName, $FieldValue);


} // TRMTypedCollection