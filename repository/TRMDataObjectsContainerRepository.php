<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DataObject\TRMDataObjectsContainer;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\Repository\Interfaces\TRMRepositoryInterface;

/**
 * ����� ����� ��� ���������� ������������ ���������� � ��������� � ���������� ������,
 *
 * @author TRM
 */
abstract class TRMDataObjectsContainerRepository implements TRMRepositoryInterface
{
/**
 * @var string - ��� ���� ������, � �������� �������� ������ ��������� ������ Repository
 */
protected $ObjectTypeName = TRMDataObjectsContainer::class;
/**`
 * @var TRMDataObjectsContainerInterface - ��������� �������� ������
 */
protected $DataObjectsContainer;


/**
 * @param string $objectclassname - ��� ������ ��� ��������, �� ������� �������� ���� ��������� ������������
 */
public function __construct($objectclassname)
{
    $this->ObjectTypeName = (string)$objectclassname;
}

/**
 * 
 * @return TRMIdDataObjectRepository - ���������� ������ (������ ������) �� ����������� ��� �������� �������
 */
public function getMainRepository()
{
    return TRMDIContainer::getStatic(TRMRepositoryManager::class)
            ->getRepositoryFor( $this->DataObjectsContainer->getMainDataObject() );
}

/**
 * ���������� ������ �� ������� ��������� ��������, � ������� �������� Repository
 * 
 * @return TRMDataObjectsContainerInterface
 */
public function getObject()
{
    return $this->DataObjectsContainer;
}

/**
 * ������ ������� ������, � ������� ����� �������� �����������, 
 * ������ ������, ������ �� ���������� � ��� ���������, ���� ���������� ������ ������� �� ��, ����� � �������� �������,
 * ��� ���� ���� ������ ���. ������������ ��������������� ��� ���.������� ������ ��������� �������!
 * 
 * @param TRMDataObjectInterface $DataObjectsContainer - ������� ������, � ������� ����� �������� �����������, ������ ���� ���� - TRMDataObjectsContainerInterface
 */
public function setObject(TRMDataObjectInterface $DataObjectsContainer)
{
    $this->DataObjectsContainer = $DataObjectsContainer;
}

/**
 * �������� ����������� �� ������ ������, ��� ������ �� �����������, ������ ������ ����� � ������������!!!
 */
public function unlinkObject()
{
    $this->DataObjectsContainer = null;
}

/**
 * ���������� ������� �������� �������, ���������������� ���������� �������� ��� ���������� ����,
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
    // ���� ������ ���������� ������ ��� �� ������������ � ���� ������������,
    // �� ������� ����� � �������� � ���
    if( !$this->DataObjectsContainer )
    {
        $this->setObject(new $this->ObjectTypeName);
    }

    // �������� �������� ������ ��� ������� ����� ���������� �������
    // ��� �������� ������� ��� ������ ���������� ������, ������� ��������, 
    // ��� �� ������� ������������ getBy,
    // ��� �� ���������� ����� setObject, ������� ��������� ��� �����������
    $this->getMainRepository()->getBy( $objectname, $fieldname, $value, $operator );

    return $this->DataObjectsContainer;
}

/**
 * ��������� ��������� ������ � ������� �������� � ���������������� � ���� ���������
 * 
 * @param TRMDataObjectInterface $object - ����������� ������, �� ����� ���� ������ ���� ��� TRMDataObjectsContainerInterface
 * ����� ���������� ��� ������� ������ �������������������������
 */
public function save(TRMDataObjectInterface $object = null)
{
    if( null !== $object )
    {
        $this->setObject($object);
    }
    if( null === $this->DataObjectsContainer )
    {
        throw new Exception( "�� ���������� ������ � ������� � ����������� " . get_class($this) );
    }
    return $this->update();
}


} // TRMRepositoiesContainer