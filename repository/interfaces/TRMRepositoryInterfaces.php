<?php

namespace TRMEngine\Repository\Interfaces;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;

/**
 * ��������� ��� �������� �����������, ������������ � ������� TRMEngine
 */
interface TRMRepositoryInterface
{
/**
 * ��������� ������ � ����������� � ������� � �������
 * 
 * @param TRMDataObjectInterface $object - ������ ������ � �������, 
 * ������ ������������� ����� getDataObject(), 
 * ������� ���������� ������ ���� TRMDataObject, ��� ����������� �� ����
 */
public function setObject(TRMDataObjectInterface $object);
/**
 * ���������� ������ �� ������� ������, � ������� �������� Repository
 * 
 * @return TRMDataObjectInterface
 */
public function getObject();
/**
 * �������� ��������� �� ������ ������, ��� ������ �� �����������, 
 * �������� ������ ����� � ������������!!!
 */
public function unlinkObject();
/**
 * ���������� ������� �������, ��������������� ��������� ��������� ��� ���������� ����
 * 
 * @param string $fieldname - ����. � ������� ���������� ��������
 * @param mixed $value - �������� ��� ��������� � ������
 * @param string $operator - =, > , < , != , LIKE, IN � �.�.
 * 
 * @return TRMDataObjectInterface - ������, ����������� ������� �� ���������
 */
function getBy($fieldname, $value, $operator = "=");
/**
 * ��������� ������ � ��������� ������
 * 
 * @param TRMDataObjectInterface $object - ������, ������ �������� ����� ��������� � �����������
 */
function save(TRMDataObjectInterface $object = null);
/**
 * ��������� ��� ��������� (���� � ������� �� ����������� �������� � ���������� ���� ��� � ���� ���������� �����) ������ ������� � ���������
 */
function update();

/**
 * ��������� ������ ������� � ���������, 
 * ��� ������ ������������ INSERT ... ON DUPLICATE KEY UPDATE,
 * ����� �������� ����������
 */
//function insert();
/**
 * ������� ��� ������ �� ������� �� ���������
 */
function delete();

} // TRMRepositoryInterface
