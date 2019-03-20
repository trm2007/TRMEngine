<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;

/**
 * ����� ��������� �������� ������, ������������ ��� �������� ��������,
 * ��������, ��� ���������� �������� � ������������� - ������, �������������, ������� ��������� � �.�.
 */
abstract class TRMRelationDataObjectsContainer extends TRMDataObjectsContainer implements TRMIdDataObjectInterface
{
/**
 * @var array - ������ ������������, ������ ������� ������� - ��� ������������� ������� � �����������
 * (..., "ObjectName" => array( "TypeName" => type, "RelationFieldName" =>fieldname ), ... )
 */
protected $DependenciesArray = array();


/**
 * �������� ������ ������ � ������ ��� ������� $Index, ����������� ������ ������, ������ �� �����������!!!
 * ���� �� ������� ������� ���� ����� ����������� �����������,
 * � ��������������� ������ ����� ��� �������� �� ���������� � �����������, 
 * �� ��� ���������� ���� ����������...
 * 
 * @param string $Index - �����-������, ��� ������� ����� �������� ������ � ����������
 * @param TRMDataObjectInterface $do - ����������� ������
 */
/*
public function setDataObject($Index, TRMDataObjectInterface $do)
{
    $ClassName = get_class($do);
    if( isset($this->DependenciesArray[$Index]["TypeName"]) && $this->DependenciesArray[$Index]["TypeName"] !== $ClassName)
    {
        $this->DependenciesArray[$Index]["TypeName"] = $ClassName;
        $this->DependenciesArray[$Index]["RelationFieldName"] = null;
    }
    parent::setDataObject($Index, $do);
}
 * 
 */

/**
 * �������� ������ ������ � ������ $Index � ������-��������� ������������, ����������� ������ ������, ������ �� �����������!!!
 * 
 * @param string $Index - ���/�����-������, ��� ������� ����� �������� ������ � ����������
 * @param TRMIdDataObjectInterface $do - ����������� ������
 * @param string $FieldName - ��� ���� ��������� �������, �� �������� ����������� �����������
 */
public function setDependence($Index, TRMIdDataObjectInterface $do, $FieldName )
{
    $this->DependenciesArray[$Index] = strval($FieldName); 
    /*array(
        "TypeName"=> get_class($do),
        "RelationFieldName" => strval($FieldName),
    );
    parent::setDataObject($Index, $do);
     * 
     */
    
    
    $this->setDataObject($Index, $do);
}

/**
 * ���������� ������ � ������ $Index �� �������-���������� ������������
 * 
 * @param string $Index - ���/�����-������ ������� � ����������
 * 
 * @return TRIdMDataObject - ��� ���� � ������� �������, �� �������� ������ ������������ ��� �������� $Index
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

/**
 * ������� � ���������� ������ ������� 
 * � ������ �������������� � ������� ������������ $DependenciesArray,
 * ��� ���� ������ �� ���� ������ ������ ����� ����������� ��� ����������!!!
 * ������ ������� ����� ����������, ��������, 
 * ��� ������ ������ � ������������� ������� setOwnData �� �������� ���������
 */
/*
public function initEmptyContainer()
{
    foreach ($this->DependenciesArray as $ObjectName => $ObjectConfig )
    {
        if( !isset($this->ObjectsArray[$ObjectName]) )
        {
            $this->setDataObject($ObjectName, new $ObjectConfig["TypeName"]);
        }
    }
}
 * 
 */


/****************************************************************************
 * ���������� ���������� TRMIdDataObjectInterface
 ****************************************************************************/
public function getId()
{
    return $this->MainDataObject->getId();
}

public function getIdFieldName()
{
    return $this->MainDataObject->getIdFieldName();
}

public function resetId()
{
    $this->MainDataObject->resetId();
}

public function setId($id)
{
    $this->MainDataObject->setId($id);
}

public function setIdFieldName($IdFieldName)
{
    $this->MainDataObject->setIdFieldName($IdFieldName);
}


} // TRMRelationDataObjectsContainer