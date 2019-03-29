<?php

namespace TRMEngine\Repository\Interfaces;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;

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
 * @return TRMDataObjectInterface - ������, ����������� ������� �� ���������
 */
public function getOne();
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
 * @param string $operator - =, > , < , != , LIKE, IN � �.�.
 * 
 * @return TRMDataObjectInterface - ������, ����������� ������� �� ���������
 */
public function getOneBy($objectname, $fieldname, $value, $operator = "=");
/**
 * ���������� ������� ���� �������,
 * ���� ����� ��� $this->DataSource ���� ����������� �����-�� �������, �� ��� ����� ������������ ��� �������,
 * �������� ��������� �������, ���������� ���������� �������, ��� ������� WHERE
 * 
 * @return TRMDataObjectsCollection - ��������� � ���������, ������������ ������� �� ����������� ���������, 
 * ��������� ����� ���� ������, ���� �� �� �������� ������ ������, ��� ���� ������� ������ �� ���������
 */
public function getAll();

/**
 * ���������� ������� �������, ��������������� ���������� �������� ������ ����,
 * ������������� ���������, ���� ����� ������� ������� �� ������ ���� 
 * ��� ������� WHERE ��������
 * 
 * @param string $objectname - ��� ������� ��� ������ �� �������� ����
 * @param string $fieldname - ��� ����, � ������� ���������� ��������
 * @param mixed $value - �������� ��� ��������� � ������
 * @param string $operator - =, > , < , != , LIKE, IN � �.�.
 * 
 * @return TRMDataObjectInterface - ������, ����������� ������� �� ���������
 */
public function getBy($objectname, $fieldname, $value, $operator = "=");
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

} // TRMRepositoryInterface

/**
 * ��������� ��� �������� �����������, ������������ � ������� TRMEngine
 */
interface TRMIdDataObjectRepositoryInterface
{
/**
 * �������� ������ ������� �� ��������� �� ID,
 * ������� ������� ����� ������� �� ID �� ����������� � ���������!
 * 
 * @param scalar $id - ������������� (Id) �������
 * 
 * @return TRMDataObjectInterface - ������, ����������� ������� �� ���������
 */
public function getById($id);

/**
 * @return array - array(��� ���-�������, ��� ����) ��� ID � �������������� ������ ������������ ��������
 */
public function getIdFieldName();

/**
 * @param array $IdFieldName - array(��� ���-�������, ��� ����) 
 * ��� ID � �������������� ������ ������������ ��������
 */
public function setIdFieldName( array $IdFieldName );

} // TRMIdDataObjectRepositoryInterface