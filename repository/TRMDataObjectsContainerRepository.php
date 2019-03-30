<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DataObject\TRMDataObjectsContainer;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\Repository\Exeptions\TRMRepositoryNoDataObjectException;
use TRMEngine\Repository\Exeptions\TRMRepositoryUnknowDataObjectClassException;
use TRMEngine\Repository\Interfaces\TRMIdDataObjectRepositoryInterface;

/**
 * ����� ����� ��� ����������� ���������� ��������
 */
class TRMDataObjectsContainerRepository implements TRMIdDataObjectRepositoryInterface
{
/**
 * @var TRMDataObjectsCollectionInterface - ��������� �������� , 
 * ���������� ��� ��������� ������ ������ �� ������� getBy,
 * getOne - ���� �������� ���������, �� ������ ����� ��������!
 */
protected $GetCollection;
/**
 * @var TRMDataObjectsCollectionInterface - ��������� �������� , 
 * ����������� � �����������, ������� ����� �������� ��� �������� � ���������� ��������� DataSource
 */
protected $CollectionToUpdate;
/**
 * @var TRMDataObjectsCollectionInterface - ��������� �������� , 
 * ����������� � �����������, ������� ����� �������� ��� �������� � ���������� ��������� DataSource
 */
protected $CollectionToInsert;
/**
 * @var TRMDataObjectsCollectionInterface - ��������� �������� , 
 * ������� ������������ � �������� �� ����������� ��������� DataSource
 */
protected $CollectionToDelete;
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
    if( !is_a($objectclassname, TRMDataObjectsContainer::class) )
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
    // ���� ������� ������ ��� ���������, 
    // ��������� ��� �� ������������ ����
    if( $DataObject && $this->validateContainerObject($DataObject) )
    {
        $Container = $DataObject;
    }
    else
    {
        // ������� ����� ������ ���������� ������ � �������� � ���
        $Container = new $this->ObjectTypeName;
    }

    // �������� ������ ��� ������� ����� ���������� �������, 
    // ��� �������� ������� ��� ������ ���������� ������, 
    // ������� ��������, ��� �� ������� getOneBy,
    if( !$this->getMainRepository()->getOneBy( $objectname, $fieldname, $value, $Container->getMainDataObject() ) )
    {
        throw new TRMRepositoryNoDataObjectException( "������ ��� �������� ������� �������� �� ������� - "  . get_class($this) );
    }

    // ���� �� ���� �������� � ����������
    foreach( $Container as $Index => $DataObject )
    {
        $DependIndex = $Container->getDependence($Index);
        // ���� ������������ ������� ��� � ������������ ��� �������� ������� � ����������, 
        // �� ��� �������� ���������,
        // ��� ��� �������� getByParent 
        if( !$DependIndex )
        {
            // getByParent ���������� ��������� TRMDataObjectsCollection
//            $Container->setChildObject(
//                    $Index,
//                    TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor( $DataObject )
//                            ->getByParent( $Container->getMainDataObject() )
//                    );
            $DataObject = TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor( $DataObject )
                            ->getByParent( $Container->getMainDataObject(), $DataObject );

        }
        // ���� ��� ������-����������� ��� ��������, ��
        // ��� ���� �������� getById
        else
        {

            // ���������� ��������� TRMDataObjectsCollection
//            $Container->setDependence(
//                    $Index,
//                    TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor( $DataObject )
//                            ->getById( $Container->getMainDataObject()
//                                            ->getFieldValue( $DependIndex[0], $DependIndex[1] )
//                                    ),
//                    $DependIndex[0],
//                    $DependIndex[1] 
//                    );
            TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor( $DataObject )->getById(
                            $Container->getMainDataObject()
                                ->getFieldValue( $DependIndex[0], $DependIndex[1] ),
                            $DataObject
                            );
        }
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

    // ���� �� ���� �������� � ����������
    foreach( $Container as $Index => $DataObjectsCollection )
    {
        // ���� ������������ ������� ��� � ������������ ��� �������� ������� � ����������, 
        // �� ��� �������� ���������, ��� ��� �������� updateCollection
        // ���� ��� ������-����������� ��� �������� ������� ����������, 
        // �� ����������� �� �������, ��� - ���������� ������, ����������� � ��������� ����������!
        if( !$Container->isDependence($Index) )
        {
            // ��������� ��������� $DataObject � ��������������� ��� ���������� � �����������
            TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor( $DataObjectsCollection )
                   ->updateCollection( $DataObjectsCollection );
        }
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

    // ���� �� ���� �������� � ����������
    foreach( $Container as $Index => $DataObjectsCollection )
    {
        // ���� ������������ ������� ��� � ������������ ��� �������� ������� � ����������, 
        // �� ��� �������� ���������, ��� ��� �������� deleteCollection
        // ���� ��� ������-����������� ��� ��������, 
        // �� ��� �� �������, ��� ���������� ������ � �����������, � ��������� ����������!
        if( !$Container->isDependence($Index) )
        {
            // ��������� ��������� $DataObject � ��������������� ��� �������� � �����������
            TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor( $DataObjectsCollection )
                   ->deleteCollection( $DataObjectsCollection );
        }
    }

    $this->getMainRepository()->delete( $Container->getMainDataObject() );
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
}

    public function deleteCollection(\TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface $Collection) {
        
    }

    public function doDelete() {
        
    }

    public function doInsert() {
        
    }

    public function doUpdate() {
        
    }

    public function getAll(\TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface $Collection = null) {
        
    }

    public function getBy($objectname, $fieldname, $value, \TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface $Collection = null) {
        
    }

    public function getById($id, TRMDataObjectInterface $DataObject = null) {
        
    }

    public function getIdFieldName() {
        
    }

    public function getOne(TRMDataObjectInterface $DataObject = null) {
        
    }

    public function insert(TRMDataObjectInterface $DataObject) {
        
    }

    public function insertCollection(\TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface $Collection) {
        
    }

    public function setIdFieldName(array $IdFieldName) {
        
    }

    public function updateCollection(\TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface $Collection) {
        
    }

} // TRMRepositoiesContainer