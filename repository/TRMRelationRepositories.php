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
        TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor( $DataObject )
                        ->getById( 
                                    $this->DataObjectsContainer->getMainDataObject()
                                        ->getFieldValue( $this->DataObjectsContainer->getDependence($Index) )
                                );
    }

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
    
    return true;
}

/**
 * ������� �������� ������ � ��� ����������� �� ����������,
 * 
 * @return boolean
 */
public function delete()
{
    return $this->getMainRepository()->delete();
}


} // TRMRelationRepositories