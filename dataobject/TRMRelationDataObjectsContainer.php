<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;

/**
 * ����� ��������� �������� ������, ������������ ��� �������� ��������,
 * ��������, ��� ���������� �������� � ������������� - ������, �������������, ������� ��������� � �.�.
 * ���� ������� ������ � ��������� �������
 */
abstract class TRMRelationDataObjectsContainer extends TRMDataObjectsContainer implements TRMIdDataObjectInterface
{
/**
 * @var array - ������ ������������, ������ ������� ������� - ��� ������������� ������� � �����������
 * (..., "ObjectName" => array( "TypeName" => type, "RelationFieldName" =>fieldname ), ... )
 */
protected $DependenciesArray = array();


/**
 * �������� ������ ������ � ������ $Index � ������-��������� ������������, ����������� ������ ������, ������ �� �����������!!!
 * 
 * @param string $Index - ���/�����-������, ��� ������� ����� �������� ������ � ����������
 * @param TRMIdDataObjectInterface $do - ����������� ������
 * @param string $FieldName - ��� ���� ��������� �������, �� �������� ����������� �����������
 */
public function setDependence($Index, TRMIdDataObjectInterface $do, $ObjectName, $FieldName )
{
    $this->DependenciesArray[$Index] = array( strval($ObjectName), strval($FieldName) ); 
    
    $this->setDataObject($Index, $do);
}

/**
 * ���������� ������ � ������ $Index �� �������-���������� ������������
 * 
 * @param string $Index - ���/�����-������ ������� � ����������
 * 
 * @return array - ��� ���� � ������� �������, �� �������� ������ ������������ ��� �������� $Index
 */
public function getDependence($Index)
{
    return isset($this->DependenciesArray[$Index]) ? $this->DependenciesArray[$Index] : null;
}

/**
 * @return array - ������ � ������������� ���� - array(..., "ObjectName" => array( "TypeName" => type, "RelationFieldName" =>fieldname ), ... )
 */
public function getDependenciesArray()
{
    return $this->DependenciesArray;
}

/****************************************************************************
 * ���������� ���������� TRMIdDataObjectInterface
 ****************************************************************************/
public function getId()
{
    return $this->MainDataObject->getId();
}
public function setId($id)
{
    $this->MainDataObject->setId($id);
}
public function resetId()
{
    $this->MainDataObject->resetId();
}

public function getIdFieldName()
{
    return $this->MainDataObject->getIdFieldName();
}
public function setIdFieldName(array $IdFieldName)
{
    $this->MainDataObject->setIdFieldName($IdFieldName);
}


} // TRMRelationDataObjectsContainer