<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DiContainer\TRMDIContainer;

/**
 * ����������� ��� �������-���������� ������,
 * � ������� ���� ������� ������ � �����������,
 * � �������� ������� ������ ������ ����� �� ID.
 * ����������� ������ ���������� ������ � ������� ��������,
 * ���� ��� ��� �� ��������.
 * ��������� � ����������� ���� ������������ ��� �� �����, 
 * ��� ��� �������� ������������! � ������ ��� ������ ��������������...
 */
abstract class TRMRelationRepositories extends TRMDataObjectsContainerRepository 
{

/**
 * ���������� ������� �������� �������, ���������������� ���������� �������� ��� ���������� ����,
 * � ���������� �������� ����� getBy ��� ������������ ���� ��������� ��������,
 * ��������� ������ � getBy ����� getDependence().
 * ��������� ������� ������ ���� ������������ TRMIdDataObjectInterface
 * 
 * @param string $objectname - ��� ������� ��� ������ �� �������� ����
 * @param string $fieldname - ����, � ������� ���������� ��������
 * @param mixed $value - �������� ��� ��������� � ������
 * @param string $operator - =, > , < , != , LIKE, IN � �.�., ����������� "="
 * 
 * @return TRMDataObjectsContainerInterface - ������-���������, ����������� ������� �� ���������
 */
public function getBy( $objectname, $fieldname, $value, $operator = "=" )
{
    // � ������������ parent::getBy ���������� ������ �� ��������� ��� �������� ����� ���������� �������
    if( !parent::getBy( $objectname, $fieldname, $value, $operator ) )
    {
        return null;
    }
    // � ����� ���������� ��� ����������� ��� �������� �������, 
    // ������� ������� � ���� � ��������� (������� ������������)
    foreach( $this->DataObjectsContainer as $Index => $DataObject )
    {
        $DependIndex = $this->DataObjectsContainer->getDependence($Index);
        
        TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor( $DataObject )
                        ->getById( $this->DataObjectsContainer->getMainDataObject()
                                        ->getFieldValue( $DependIndex[0], $DependIndex[1] )
                                );
    }

    return $this->DataObjectsContainer;
}

/**
 * ��������� �������� ������, ��� ������������!!!
 * ����������� - ��� ��������� ����������� ��������, ����������� ��������,
 * ���� ������ �������������� ����������� TRMEventRepositories � ��������� �� �������
 * 
 * @return boolean
 */
public function update()
{
    if( !$this->getMainRepository()->update() ) { return false; }
    
    return true;
}

/**
 * ������� �������� ������, ��� ������������!!!
 * ����������� - ��������� ����������� ��������, ��������� ��������,
 * ���� ������ �������������� ����������� TRMEventRepositories � ��������� �� �������
 * 
 * @return boolean
 */
public function delete()
{
    return $this->getMainRepository()->delete();
}


} // TRMRelationRepositories