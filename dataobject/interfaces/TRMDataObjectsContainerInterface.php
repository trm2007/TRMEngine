<?php

namespace TRMEngine\DataObject\Interfaces;

use TRMEngine\DataObject\TRMDataObjectsCollection;

/**
 * ��������� ��� ��������� ��������,
 * � ������� ���� ������� ������ ������, � ��������� ��������������� (��������)
 */
interface TRMDataObjectsContainerInterface extends TRMIdDataObjectInterface
{
/**
 * @return TRMIdDataObjectInterface - ���������� ������� (����������� ��� 0-� ������� � �������) ������ ������
 */
public function getMainDataObject();

/**
 * ������������� ������� ������ ������,
 * 
 * @param TRMIdDataObjectInterface $do - ������� ������ ������
 */
public function setMainDataObject(TRMIdDataObjectInterface $do);

/**
 * �������� ������ ������ � ������ $Index � ������-��������� ������������, 
 * ����������� ������ ������, ������ �� �����������!!!
 * 
 * @param string $Index - ���/�����-������, ��� ������� ����� �������� ������ � ����������
 * @param TRMIdDataObjectInterface $do - ����������� ���������, ��� ��������
 * @param string $ObjectName - ��� ���-������� � ������� �������, �� �������� ����������� �����������
 * @param string $FieldName - ��� ���� ��������� ���-������� � ������� �������, 
 * �� �������� ����������� ����� ������������
 */
public function setDependence($Index, TRMIdDataObjectInterface $do, $ObjectName, $FieldName );

/**
 * ���������� ������ � ������� ����� ����������� � �������� $Index
 * 
 * @param string $Index - ���/�����-������ ������� � ����������
 * 
 * @return array - ��� ���-������� � ���� � ���-������� �������� �������, 
 * �� �������� ����������� ����� � ID ����������� ��� �������� $Index
 */
public function getDependenceField($Index);

/**
 * 
 * @return array(TRMIdDataObjectInterface) - ���������� ������ 
 * �� ����� ������������ ��� �������� ������� �� ����������
 */
public function getDependenciesObjectsArray();

/**
 * ���������� ������ ����������� � �������� $Index �� ���������� ��������
 * 
 * @param string $Index - ���/�����-������ ������� � ����������
 * 
 * @return TRMIdDataObjectInterface - ��������� � ��������� ������, ����������� � ����������
 */
public function getDependenceObject($Index);

/**
 * 
 * @param string $Index - ������ ������� � ����������
 * @return bool - ���� ������ � ���������� ��� ���� �������� ������������ ��� ��������� �� ��������,
 * ��������, ������ ������������� ��� ������, �� �������� true, ���� ����������� �� ����������, �� - false
 */
public function isDependence($Index);

/**
 * @return array - ������ �������� � ������������� ����:
 * array("ObjectName" => array( "RelationSubObjectName" => type, "RelationFieldName" =>fieldname ), ... )
 */
public function getDependenciesFieldsArray();

/**
 * ������� ������ � ���. ��������� ������,
 * ��� �� � ���� �������� �������� ������ �� ���� ������������ ���������
 */
public function clearDependencies();

/**
 * 
 * @param TRMDataObjectsCollection $Collection - ���������, 
 * ��� ������� ������� ������� ����� ���������� ��������� ������ ������ ����������
 */
public function setParentFor( TRMDataObjectsCollection $Collection, TRMIdDataObjectInterface $Parent);

/**
 * �������� ��������� �������� ������ ������ � ������ ��� ������� $Index, 
 * ����������� ������ ������, ������� �� �����������!!!
 * 
 * @param string $Index - �����-������, ��� ������� ����� �������� ������ � ����������
 * @param TRMDataObjectsCollection $Collection - ����������� ������-���������
 */
public function setChildCollection($Index, TRMDataObjectsCollection $Collection);

/**
 * ���������� ������ �� ���������� ��� ������� $Index
 * 
 * @param integer $Index - ����� ������� � ����������
 * 
 * @return TRMDataObjectInterface - ������ �� ����������
 */
public function getChildCollection($Index);

/**
 * @return array - ���������� ������ �������� ������, ����������� �������� ������
 */
public function getChildCollectionsArray();

/**
 * ������� ������ � ���. ��������� ������,
 * ��� �� � ���� �������� �������� ������ �� ���� ������������ ���������
 */
public function clearChildCollectionsArray();


} // TRMDataObjectsContainerInterface


interface TRMRelationDataObjectsContainerInterface extends TRMDataObjectsContainerInterface
{

} // TRMRelationDataObjectsContainerInterface
