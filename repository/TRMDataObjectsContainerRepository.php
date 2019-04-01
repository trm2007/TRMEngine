<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DataObject\TRMDataObjectsContainer;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\Repository\Exceptions\TRMRepositoryNoDataObjectException;
use TRMEngine\Repository\Exceptions\TRMRepositoryUnknowDataObjectClassException;
use TRMEngine\Repository\Interfaces\TRMIdDataObjectRepositoryInterface;

/**
 * ����� ����� ��� ����������� ���������� ��������
 */
class TRMDataObjectsContainerRepository implements TRMIdDataObjectRepositoryInterface
{
/**
 * @var string - ��� ���� ������, � �������� �������� ������ ��������� ������ Repository
 */
protected $ObjectTypeName = "";


/**
 * ��� �������� ����������� ���������� ������� ������ �������� ����� �������, 
 * ������� ����� �������������� ��� ����������� 3-� ������� - ���������/����������/��������
 * 
 * @param string $objectclassname - ��� ������ ��� ��������, �� ������� �������� ���� Repository
 */
public function __construct( $objectclassname )
{
    if( !class_exists($objectclassname) )
    {
        throw new TRMRepositoryUnknowDataObjectClassException( $objectclassname . " �� ��������������� � ������� - " . get_class($this) );
    }
    if( !is_subclass_of($objectclassname, TRMDataObjectsContainer::class) )
    {
        throw new TRMRepositoryUnknowDataObjectClassException( $objectclassname . " �� �������� ����������� - " . get_class($this) );
    }

    $this->ObjectTypeName = $objectclassname;
}

/**
 * @param TRMDataObjectsContainerInterface $Container - ��������� �������� � ���������, 
 * ��� �������� ������� ����� ���������� ����� �������� �����������
 * 
 * @return TRMIdDataObjectRepository - ���������� ������ (������ ������) �� ����������� ��� �������� �������
 */
public function getMainRepository( TRMDataObjectsContainerInterface $Container )
{
    return TRMDIContainer::getStatic(TRMRepositoryManager::class)
            ->getRepositoryFor( $Container->getMainDataObject() );
}

/**
 * {inheritDoc}
 */
public function getById($id, TRMDataObjectInterface $DataObject = null)
{
    $IdFieldName = $this->getIdFieldName();
    $Container = $this->getOneBy($IdFieldName[0], $IdFieldName[1], $id, $DataObject);
    
    return $Container;
}

/**
 * ���������� ������� �������� �������, ���������������� ���������� �������� ��� ���������� ����,
 * ���������� �������� ����� getOneBy ��� ������������ ���� ��������-������������,
 * �� ������� ������� ������� ������ ����������, ��������� ������ � getBy ����� getDependence().
 * �������-����������� ������ ������������� TRMIdDataObjectInterface
 * � ��� ���� �������� ��������� �������� getByParent, 
 * 
 * @param string $objectname - ��� ������� ��� ������ �� ��������
 * @param string $fieldname - ����, � ������� ���������� ��������
 * @param mixed $value - �������� ��� ��������� � ������
 * @param TRMDataObjectInterface $DataObject - ���� ����� ������, �� ����� ����������� �� �����,
 * ����� ����������� �������� ����� �������
 * 
 * @return TRMDataObjectsContainerInterface - ������-���������, ����������� ������� �� ���������
 */
public function getOneBy( $objectname, $fieldname, $value, TRMDataObjectInterface $DataObject = null)
{
    if( $DataObject === null )
    {
        $Container = new $this->ObjectTypeName;
    }
    else
    {
        // ���� ������� ������ ��� ���������, 
        // ��������� ��� �� ������������ ����,
        // ���� �� ������� �������� , validateContainerObject ����������� ����������
        $this->validateContainerObject($DataObject);
        $Container = $DataObject;
    }
    
    // �������� ������ ��� �������� ������� ����������, 
    // ��� �������� ������� ��� ������ ���������� ������, 
    // ������� ��������, ��� �� ������� getOneBy,
    if( !$this->getMainRepository($Container)->getOneBy( 
            $objectname, 
            $fieldname, 
            $value, 
            $Container->getMainDataObject() ) )
    {
        throw new TRMRepositoryNoDataObjectException( "������ ��� �������� ������� �������� �� ������� - "  . get_class($this) );
    }
    // ���� �� ���� �������� ���������� � ����������
    foreach( $Container as $Index => $Collection )
    {
        // ��� �������� �������������� ���������,
        // � ������� ���������� ��������, ������� �������� � ����������, �������� getByParent, 
        // ��� ��� �������� ��������� ����� �������� ����� �� ����� ->getObjectsType()
        $Collection = TRMDIContainer::getStatic(TRMRepositoryManager::class)
                ->getRepository( $Collection->getObjectsType() )
                ->getByParent( $Container->getMainDataObject(), $Collection );
    }
    // ���� �� ���� ��������-������������ � ����������
    foreach( $Container->getDependenciesObjectsArray() as $Index => $DataObject )
    {
        $DependIndex = $Container->getDependenceField($Index);
        // ���� ��� ������-����������� ��� ��������, ��
        // ��� ���� �������� getById
        TRMDIContainer::getStatic(TRMRepositoryManager::class)
                ->getRepositoryFor( $DataObject )
                ->getById(
                        $Container->getMainDataObject()->getFieldValue( $DependIndex[0], $DependIndex[1] ),
                        $DataObject
                        );
    }

    return $Container;
}

/**
 * ��������� ������ ������
 * � ��� �������� ������� � ����������, 
 * ���� ��� ��������� �� ������� updateComplexProductDBEvent.
 * ���������� ���������� �� ���������� �������-�����������!!!
 * ����������� - ��� ��������� ����������� ��������, ����������� ��������,
 * ���� ������ �������������� ��������
 * 
 * @param TRMDataObjectInterface $Container - ����������� ������-���������, 
 * �� ����� ���� ������ ���� ��� TRMDataObjectsContainerInterface
 * 
 * @return boolean
 */
function update( TRMDataObjectInterface $Container )
{
    $this->validateContainerObject($Container);
    
    $this->getMainRepository()->update( $Container->getMainDataObject() );

    // ���� �� ���� �������� ���������� � ����������,
    // ���������� ��������� �� ����������� �����������
    foreach( $Container as $DataObjectsCollection )
    {
        // ��� �������� ���������, ��� ��� �������� updateCollection
        // ��������� ��������� �������� $DataObjectsCollection 
        // � ��������������� ��� ���������� � �����������
        TRMDIContainer::getStatic(TRMRepositoryManager::class)
                ->getRepository( $DataObjectsCollection->getObjectsType() )
                ->updateCollection( $DataObjectsCollection );
    }
}
/**
 * @param TRMDataObjectsCollectionInterface $Collection - ��������� ��������-�����������, 
 * ������� ����� �������� � ��������� �����������
 */
public function updateCollection(TRMDataObjectsCollectionInterface $Collection)
{
    foreach( $Collection as $Container )
    {
        $this->update($Container);
    }
}

public function insert(TRMDataObjectInterface $Container)
{
    $this->validateContainerObject($Container);
    
    $this->getMainRepository()->update( $Container->getMainDataObject() );

    // ���� �� ���� �������� ���������� � ����������
    foreach( $Container as $DataObjectsCollection )
    {
        TRMDIContainer::getStatic(TRMRepositoryManager::class)
                ->getRepository( $DataObjectsCollection->getObjectsType() )
                ->insertCollection( $DataObjectsCollection );
    }
}

public function insertCollection(TRMDataObjectsCollectionInterface $Collection)
{
    foreach( $Collection as $Container )
    {
        $this->insert($Container);
    }
}

/**
 * ������� �������� ������, ��� ������������!!!
 * �������-����������� �� ������� �� �������� ������� � ��������� ���������.
 * �������� ������� deleteComplexProductDBEvent,
 * �������� ��� �������� �������, ��� �������� ������,
 * ����� ���������� �������� �������� �������
 * 
 * @param TRMDataObjectInterface $Container - ��������� ������-���������, 
 * �� ����� ���� ������ ���� ��� TRMDataObjectsContainerInterface
 * 
 * @return boolean
 */
public function delete( TRMDataObjectInterface $Container )
{
    $this->validateContainerObject($Container);

    // ���� �� ���� �������� ���������� � ����������
    foreach( $Container as $DataObjectsCollection )
    {
        // ��������� ��������� �������� $DataObjectsCollection 
        // � ��������������� ��� �������� � �����������
        TRMDIContainer::getStatic(TRMRepositoryManager::class)
                ->getRepository( $DataObjectsCollection->getObjectsType() )
                ->deleteCollection( $DataObjectsCollection );
    }

    $this->getMainRepository()->delete( $Container->getMainDataObject() );
}
/**
 * @param TRMDataObjectsCollectionInterface $Collection - ��������� ��������-�����������, 
 * ������� ����� �������� � ��������� ���������
 */
public function deleteCollection(TRMDataObjectsCollectionInterface $Collection)
{
    foreach( $Collection as $Container )
    {
        $this->delete($Container);
    }
}

/**
 * ��������� ��������� ������ � ������� �������� � ���������������� � ���� ���������
 * 
 * @param TRMDataObjectInterface $Container - ����������� ������-���������, 
 * �� ����� ���� ������ ���� ��� TRMDataObjectsContainerInterface
 */
public function save(TRMDataObjectInterface $Container)
{
    return $this->update($Container);
}

/**
 * ���������, ��� ������ �������������� ������ ���� ������������
 * 
 * @param TRMDataObjectsContainerInterface $Container - ����������� ������
 * @throws TRMRepositoryUnknowDataObjectClassException
 */
public function validateContainerObject( TRMDataObjectsContainerInterface $Container )
{
    if( get_class($Container) !== $this->ObjectTypeName )
    {
        throw new TRMRepositoryUnknowDataObjectClassException( get_class($Container) . " ��� " . get_class($this) );
    }
    return true;
}


    public function doDelete() {
        
    }

    public function doInsert() {
        
    }

    public function doUpdate() {
        
    }

    public function getAll(TRMDataObjectsCollectionInterface $Collection = null) {
        
    }

    public function getBy($objectname, $fieldname, $value, TRMDataObjectsCollectionInterface $Collection = null) {
        
    }

    public function getOne(TRMDataObjectInterface $DataObject = null) {
        
    }


public function getIdFieldName()
{
    $type = $this->ObjectTypeName;
    return $type::getIdFieldName(); //$this->MainDataObject->getIdFieldName();
}

//public function setIdFieldName(array $IdFieldName)
//{
//    $type = $this->ObjectTypeName;
//    return $type::setIdFieldName($IdFieldName);
//
//}


} // TRMRepositoiesContainer