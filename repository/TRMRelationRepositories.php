<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DiContainer\TRMDIContainer;

/**
 * ��������� ������������ ��� ��������-����������� ������,
 */
abstract class TRMRelationRepositories extends TRMDataObjectsContainerRepository 
{
/**
 * @var array - ������ � ���������� ����� ��������, ������� ����� �������� � ���������� ������,
 * �� ��������� � ��������� ������� ����� �������� ������ ��������� ����������-������������,
 * �� ������ ������ ��� ������ �������� � ��������-������
 */
protected $ObjectTypesArray = array();


/**
 * ���������� ������� �������� �������, ���������������� ���������� �������� ��� ���������� ����,
 * � ��������� ���� �����������, ��� ������� ����� ������, 
 * ��������� ������ �� ���� ����� ����������� ������� TRMCommonEvent
 * 
 * @param string $fieldname - ����, � ������� ���������� ��������
 * @param mixed $value - �������� ��� ��������� � ������
 * @param string $operator - =, > , < , != , LIKE, IN � �.�., ����������� "="
 * 
 * @return TRMDataObjectsContainerInterface - ������-���������, ����������� ������� �� ���������
 */
public function getBy($fieldname, $value, $operator = "=")
{
    // � ������������ parent::getBy ���������� ������ �� ��������� ��� �������� ����� ���������� �������
    if( !parent::getBy($fieldname, $value, $operator) )
    {
        return null;
    }
    
    foreach( $this->DataObjectsContainer as $Index => $DataObject )
    {
        TRMDIContainer::getStatic("TRMRepositoryManager")->getRepositoryFor( $DataObject )
                        ->getById( 
                                    $this->DataObjectsContainer->getMainDataObject()
                                        ->getFieldValue( $this->DataObjectsContainer->getDependence($Index) )
                                );
    }
/*    
    $ObjectsArray = $this->DataObjectsContainer->getDependenciesArray();
    // ����������� ��� ������� � ����������, ��� ������� ������� ������ �� ���������,
    // � �������� � ������� ���� ����������� 
    foreach( $ObjectsArray as $Index => $ObjectInfo )
    {
        // �������� ������ �� �����������, ������ ���� ������ ��������� ���� - ��� ��� ���� � ������� �������, 
        // �������� �������� ����� ������� � ID �������� (���������������) �������
        if( isset($ObjectInfo["RelationFieldName"]) && isset($ObjectInfo["TypeName"]) )
        {
            // ������������� ������ � ������-�������� - $Index, ���������� �� ��������������� ����������� � ��������� ������
            $this->DataObjectsContainer->setDataObject(
                    $Index, 
                    TRMDIContainer::getStatic("TRMRepositoryManager")->getRepository( $ObjectInfo["TypeName"] )
                        ->getById( $this->DataObjectsContainer->getMainDataObject()->getFieldValue($ObjectInfo["RelationFieldName"]) )
                );
        }
    }
 * 
 */

    return $this->DataObjectsContainer;
}

/**
 * ��������� �������� ������ � ��� �����������, 
 * 
 * @return boolean
 */
public function update()
{
    if( !$this->getMainRepository()->update() ) { return false; }
/*    
    $ObjectsArray = $this->DataObjectsContainer->getObjectsArray();
    
    // ��� ������� �������, ������������ � ������� ObjectsArray ��������� �������-���������� ������
    // �������� ������ �� ���� ������ �����������
    // � ��������� ��� ���� Update, ��� ����� ��� ������� ������� ����� ������� ���� ����������� � ������ ������,
    // � ���������� ����� ��������� ������� ������ ����!!!
    // ���������� ������ ����������� �� �����, ���� ����� ���������� ����������� ��� ������� ������� !!!!
    foreach( $ObjectsArray as $Object )
    {
        $rep = TRMDIContainer::getStatic("TRMRepositoryManager")->getRepositoryFor( $Object );
        $rep->update();
    }
 * 
 */
    
    return true;
}

/**
 * ������� �������� ������ � ��� ����������� �� ����������,
 * 
 * @return boolean
 */
public function delete()
{
/*
    $ObjectsArray = $this->DataObjectsContainer->getObjectsArray();
    
    // ��� ������� �������, ������������ � ������� ObjectsArray ��������� �������-���������� ������
    // �������� ������ �� ���� ������ �����������
    // � ��������� ��� ���� Update, ��� ����� ��� ������� ������� ����� ������� ���� ����������� � ������ ������,
    // � ���������� ����� ��������� ������� ������ ����!!!
    // ���������� ������ ����������� �� �����, ���� ����� ���������� ����������� ��� ������� ������� !!!!
    foreach( $ObjectsArray as $Object )
    {
        $rep = TRMDIContainer::getStatic("TRMRepositoryManager")->getRepositoryFor( $Object );
        $rep->delete();
    }
 * 
 */

    return $this->getMainRepository()->delete();
}


} // TRMRelationRepositories