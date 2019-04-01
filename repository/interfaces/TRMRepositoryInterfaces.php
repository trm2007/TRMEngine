<?php

namespace TRMEngine\Repository\Interfaces;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataSource\TRMSqlDataSource;

/**
 * ��������� ��� �������� �����������, ������������ � ������� TRMEngine
 */
interface TRMRepositoryInterface
{
/**
 * ������������� ������� ��� WHERE ������ SQL-������� ��� ������� �� ��,
 * 
 * @param string $objectname - ��� �������, ���������� ���� ��� ���������
 * @param string $fieldname - ��� ���� ��� ���������
 * @param string|numeric|boolean $data - ������ ��� ���������
 * @param string $operator - �������� ��������� (=, !=, >, < � �.�.), ����������� =
 * @param string $andor - ��� ������� ����� ���� �������� OR ��� AND ? �� ��������� AND
 * @param integer $quote - ����� �� ����� � ��������� ����� �����, �� ��������� ����� - TRMSqlDataSource::TRM_AR_QUOTE
 * @param string $alias - ����� ��� ������� �� ������� ������������ ����
 * @param integer $dataquote - ���� ����� �������� ������������ ��������� ��� �������, 
 * �� ���� �������� ������� ���� - TRMSqlDataSource::NOQUOTE
 * 
 * @return self - ���������� ��������� �� ����, ��� ���� ����������� ������ ����� ���������:
 * $this->setWhereCondition(...)->setWhereCondition(...)->setWhereCondition(...)...
 */
public function addCondition(
        $objectname, 
        $fieldname, 
        $data, 
        $operator = "=", 
        $andor = "AND", 
        $quote = TRMSqlDataSource::NEED_QUOTE, 
        $alias = null, 
        $dataquote = TRMSqlDataSource::NEED_QUOTE );
/**
 * ������� ������� ��� ������� (� SQL-�������� ������ WHERE)
 */
public function clearCondition();

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
 * 
 * @param bool $ClearCollectionFlag - ���� ����� ����� ���������� ��������� ��������� ����������� ��������, 
 * �� ���� ���� ������� ���������� � false, ��� ����� ������������ �������� �������,
 * �� ����� ����������� ��������� doUpdate ����� �������� ���������,
 * ��� �� �� ��������� ���������� � ������� 2 ����!
 */
public function doUpdate( $ClearCollectionFlag = true );

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
 * 
 * @param bool $ClearCollectionFlag - ���� ����� ����� ���������� ��������� ��������� ����������� ��������, 
 * �� ���� ���� ������� ���������� � false, ��� ����� ������������ �������� �������,
 * �� ����� ����������� ��������� doInsert ����� �������� ���������,
 * ��� �� �� ��������� ������� � ������� 2 ����!
 */
public function doInsert( $ClearCollectionFlag = true );

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
 * 
 * @param bool $ClearCollectionFlag - ���� ����� ����� �������� ��������� ��������� ��������� ��������, 
 * �� ���� ���� ������� ���������� � false, ��� ����� ������������ �������� �������,
 * �� ����� ����������� ��������� doDelete ����� �������� ���������,
 * ��� �� �� ��������� �������� � ������� 2 ����!
 */
public function doDelete( $ClearCollectionFlag = true );


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