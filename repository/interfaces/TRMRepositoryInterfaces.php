<?php

namespace TRMEngine\Repository\Interfaces;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;

/**
 * ��������� ��� �������� �����������, ������������ � ������� TRMEngine
 */
interface TRMRepositoryInterface
{
/**
 * ���������� ������� ����� ������, 
 * ���� ����� ��� $this->DataSource ���� ����������� �����-�� �������, �� ��� ����� ������������ ��� �������,
 * �������� ��������� �������, ���������� ���������� �������, ��� ������� WHERE
 * 
 * @param TRMDataObjectInterface $DataObject - ���� ����� ������, �� ����� ����������� �� �����,
 * ����� ����������� �������� ����� �������
 * 
 * @return TRMDataObjectInterface - ������, ����������� ������� �� ���������
 */
public function getOne(TRMDataObjectInterface $DataObject = null);
/**
 * ���������� ������� ����� ������, 
 * ��������������� ���������� �������� ��� ���������� ����.
 * ���� � ���������� ��������� (��) ���� ��������� �������, ������������� �������,
 * �� ���-����� �������� ������ ���� ������.
 * ��� ������������� ����� ������� ����� ������� � ����������������,
 * ������� �� DataSource ������ ��� ������ ������� (����),
 * ���� ����� ������� �� ���������� �������� ����� ������� getOne();
 * 
 * @param string $objectname - ��� ������� ��� ������ �� �������� ����
 * @param string $fieldname - ��� ����, � ������� ���������� ��������
 * @param mixed $value - �������� ��� ��������� � ������
 * @param TRMDataObjectInterface $DataObject - ���� ����� ������, �� ����� ����������� �� �����,
 * ����� ����������� �������� ����� �������
 * 
 * @return TRMDataObjectInterface - ������, ����������� ������� �� ���������
 */
public function getOneBy($objectname, $fieldname, $value, TRMDataObjectInterface $DataObject = null);
/**
 * ���������� ������� ���� �������,
 * ���� ����� ��� $this->DataSource ���� ����������� �����-�� �������, �� ��� ����� ������������ ��� �������,
 * �������� ��������� �������, ���������� ���������� �������, ��� ������� WHERE
 * 
 * @param TRMDataObjectsCollectionInterface $Collection - ���� ����� ������, �� ����� ����������� �� �����,
 * ����� ����������� �������� ����� �������
 * 
 * @return TRMDataObjectsCollectionInterface - ��������� � ���������, ������������ ������� �� ����������� ���������, 
 * ��������� ����� ���� ������, ���� �� �� �������� ������ ������, ��� ���� ������� ������ �� ���������
 */
public function getAll(TRMDataObjectsCollectionInterface $Collection = null);

/**
 * ���������� ������� �������, ��������������� ���������� �������� ������ ����,
 * ������������� ���������, ���� ����� ������� ������� �� ������ ���� 
 * ��� ������� WHERE ��������
 * 
 * @param string $objectname - ��� ������� ��� ������ �� �������� ����
 * @param string $fieldname - ��� ����, � ������� ���������� ��������
 * @param mixed $value - �������� ��� ��������� � ������
 * @param TRMDataObjectsCollectionInterface $Collection - ���� ����� ������, �� ����� ����������� �� �����,
 * ����� ����������� �������� ����� �������
 * 
 * @return TRMDataObjectInterface - ������, ����������� ������� �� ���������
 */
public function getBy($objectname, $fieldname, $value, TRMDataObjectsCollectionInterface $Collection = null);
/**
 * ��������� ������ � ��������� ������
 * 
 * @param TRMDataObjectInterface $DataObject - ������, ������ �������� ����� ��������� � �����������
 */
function save(TRMDataObjectInterface $DataObject);
/**
 * ��������� ��� ��������� (���� � ������� �� ����������� �������� � ���������� ���� ��� � ���� ���������� �����) ������ ������� � ���������
 * @param TRMDataObjectInterface $DataObject - ������, ������� ����� �������� � ��������� �����������
 */
function update(TRMDataObjectInterface $DataObject);
/**
 * @param TRMDataObjectsCollectionInterface $Collection - ���������, ������� ������� ����� �������� � ��������� �����������
 */
public function updateCollection(TRMDataObjectsCollectionInterface $Collection );
/**
 * ���������� ��������� ������� �� ���������������� ���������,
 * � ������ ������ � �� ���������� SQL-������� UPDATE-������
 */
public function doUpdate();

/**
 * ��������� ������ � ���������������� ��������� ��� ���������� ������� � DataSource
 * 
 * @param TRMDataObjectInterface $DataObject - ������, ������� ����� �������� � ��������� �����������
 */
public function insert( TRMDataObjectInterface $DataObject );
/**
 * @param TRMDataObjectsCollectionInterface $Collection - ���������, ������� ������� ����� �������� � ��������� �����������
 */
public function insertCollection( TRMDataObjectsCollectionInterface $Collection );
/**
 * ���������� ����������� ����� ������ ����������� ������ � ���������� ��������� DataSource
 */
public function doInsert();

/**
 * ��������� ������ ������� � ���������, 
 * ��� ������ ������������ INSERT ... ON DUPLICATE KEY UPDATE,
 * ����� �������� ����������
 */
//function insert();
/**
 * ������� ��� ������ �� ������� �� ���������
 * @param TRMDataObjectInterface $DataObject - ������, ������� ����� �������� � ��������� �����������
 */
function delete(TRMDataObjectInterface $DataObject);
/**
 * @param TRMDataObjectsCollectionInterface $Collection - ���������, ������� ������� ����� �������� � ��������� ���������
 */
public function deleteCollection( TRMDataObjectsCollectionInterface $Collection );
/**
 * ���������� ����������� �������� ������ ������� ��������� �� ����������� ��������� DataSource
 */
public function doDelete();


} // TRMRepositoryInterface


/**
 * ��������� ��� �������� �����������, ������������ � ������� TRMEngine
 */
interface TRMIdDataObjectRepositoryInterface extends TRMRepositoryInterface
{
/**
 * �������� ������ ������� �� ��������� �� ID,
 * ������� ������� ����� ������� �� ID �� ����������� � ���������!
 * 
 * @param scalar $id - ������������� (Id) �������
 * @param TRMDataObjectInterface $DataObject - ���� ����� ������, �� ����� ����������� �� �����,
 * ����� ����������� �������� ����� �������
 * 
 * @return TRMDataObjectInterface - ������, ����������� ������� �� ���������
 */
public function getById($id, TRMDataObjectInterface $DataObject = null);

/**
 * @return array - array(��� ���-�������, ��� ����) ��� ID � �������������� ������ ������������ ��������
 */
public function getIdFieldName();

/**
 * @param array $IdFieldName - array(��� ���-�������, ��� ����) 
 * ��� ID � �������������� ������ ������������ ��������
 */
//public function setIdFieldName( array $IdFieldName );


} // TRMIdDataObjectRepositoryInterface