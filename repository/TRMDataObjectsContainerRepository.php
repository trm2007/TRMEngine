<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DataObject\TRMDataObjectsCollection;
use TRMEngine\DataObject\TRMTypedCollection;
use TRMEngine\DataSource\TRMSqlDataSource;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\Repository\Exceptions\TRMRepositoryNoDataObjectException;
use TRMEngine\Repository\Exceptions\TRMRepositoryUnknowDataObjectClassException;
use TRMEngine\Repository\Interfaces\TRMIdDataObjectRepositoryInterface;
use TRMEngine\Repository\Interfaces\TRMRepositoryInterface;

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
 * @var TRMRepositoryInterface - ��������� �� ����������� 
 * ��� �������� ���� �������� ������� � ����������
 */
protected $MainDataObjectRepository = null;
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
 * @param string $ObjectTypeName - ��� ��������, � �������� �������� ���� Repository
 */
public function __construct( $ObjectTypeName )
{
    if( !class_exists($ObjectTypeName) )
    {
        throw new TRMRepositoryUnknowDataObjectClassException( $ObjectTypeName . " �� ��������������� � ������� - " . get_class($this) );
    }
    if( !is_subclass_of($ObjectTypeName, TRMDataObjectsContainerInterface::class) )
    {
        throw new TRMRepositoryUnknowDataObjectClassException( $ObjectTypeName . " �� �������� ����������� - " . get_class($this) );
    }

    $this->ObjectTypeName = $ObjectTypeName;
    // ����� ������� ����������� ��� �������� �������
    $type = $this->ObjectTypeName;
    $MainObjectType = $type::getMainDataObjectType();
    $this->MainDataObjectRepository = TRMDIContainer::getStatic(TRMRepositoryManager::class)
                                        ->getRepository( $MainObjectType );

    $this->CollectionToInsert = new TRMDataObjectsCollection();
    $this->CollectionToUpdate = new TRMDataObjectsCollection();
    $this->CollectionToDelete = new TRMDataObjectsCollection();
}

/**
 * {@inheritDoc}
 */
public function getIdFieldName()
{
    $type = $this->ObjectTypeName;
    return $type::getIdFieldName();
}

/**
 * @param TRMDataObjectsContainerInterface $Container - ��������� �������� � ���������, 
 * ��� �������� ������� ����� ���������� ����� �������� �����������
 * 
 * @return TRMIdDataObjectRepository - ���������� ������ (������ ������) �� ����������� ��� �������� �������
 */
public function getMainRepositoryFor( TRMDataObjectsContainerInterface $Container )
{
    return TRMDIContainer::getStatic(TRMRepositoryManager::class)
            ->getRepositoryFor( $Container->getMainDataObject() );
}

/**
 * ������������� ������� ��� WHERE ������ SQL-������� ��� ������� �� ��,
 * 
 * @param string $objectname - ��� �������, ���������� ���� ��� ���������
 * @param string $fieldname - ��� ���� ��� ���������
 * @param string|numeric|boolean $data - ������ ��� ���������
 * @param string $operator - �������� ��������� (=, !=, >, < � �.�.), ����������� =
 * @param string $andor - ��� ������� ����� ���� �������� OR ��� AND ? �� ��������� AND
 * @param integer $quote - ����� �� ����� � ��������� ����� �����, �� ��������� ����� - TRMSqlDataSource::TRM_AR_QUOTE
 * @param string $alias - ����� ��� ������� �� ������� ������������ ����
 * @param integer $dataquote - ���� ����� �������� ������������ ��������� ��� �������, 
 * �� ���� �������� ������� ���� - TRMSqlDataSource::NOQUOTE
 * 
 * @return self - ���������� ��������� �� ����, ��� ���� ����������� ������ ����� ���������:
 * $this->setWhereCondition(...)->setWhereCondition(...)->setWhereCondition(...)...
 */
public function addCondition(
        $objectname, 
        $fieldname, 
        $data, 
        $operator = "=", 
        $andor = "AND", 
        $quote = TRMSqlDataSource::NEED_QUOTE, 
        $alias = null, 
        $dataquote = TRMSqlDataSource::NEED_QUOTE )
{
    $this->MainDataObjectRepository->addCondition(
        $objectname, 
        $fieldname, 
        $data, 
        $operator, 
        $andor, 
        $quote, 
        $alias, 
        $dataquote );
    return $this;
}

/**
 * ������� ������� ��� ������� (� SQL-�������� ������ WHERE)
 */
public function clearCondition()
{
    $this->MainDataObjectRepository->clearCondition();
}

/**
 * @param int $Count - ���������� ���������� ��������� ��� ��������� �������� �������!
 * @param int $StartPosition - �������, � ������� ���������� �������, null - � ������ (�� ���������)
 */
public function setLimit($Count, $StartPosition = null)
{
    $this->MainDataObjectRepository->setLimit($Count, $StartPosition);
}

/**
 * 
 * @param TRMDataObjectsContainerInterface $Container - ������ ����������, 
 * � ������� ����������� �� ��������������� ������������ �������� ���������
 */
protected function getAllChildCollectionForContainer( TRMDataObjectsContainerInterface $Container )
{
    foreach( $Container as $Collection )
    {
        // ��� �������� �������������� ���������,
        // � ������� ���������� ��������, ������� �������� � ����������, �������� getByParent, 
        // ��� ��� �������� ��������� ����� �������� ����� �� ����� ->getObjectsType()
        $Rep = TRMDIContainer::getStatic(TRMRepositoryManager::class)
                ->getRepository( $Collection->getObjectsType() );
        $Rep->getByParent( $Container->getMainDataObject(), $Collection );
    }
}
/**
 * 
 * @param TRMDataObjectsContainerInterface $Container - ������ ����������, 
 * � ������� ����������� �� ��������������� ������������ �������� ���������
 */
protected function getAllDependenciesObjectsForContainer( TRMDataObjectsContainerInterface $Container )
{
    foreach( $Container->getDependenciesObjectsArray() as $Index => $DataObject )
    {
        $DependIndex = $Container->getDependenceField($Index);
        // ���� ��� ������-����������� ��� ��������, ��
        // ��� ���� �������� getById
        TRMDIContainer::getStatic(TRMRepositoryManager::class)
                ->getRepositoryFor( $DataObject )
                ->getById(
                        $Container->getMainDataObject()->getData( $DependIndex[0], $DependIndex[1] ),
                        $DataObject
                        );
    }
}

/**
 * {@inheritDoc}
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
 * @param string $objectname - ��� ���-������� (������� � ��) ��� ������ �� ��������
 * @param string $fieldname - ����, � ������� ���������� ��������
 * @param mixed $value - �������� ��� ��������� � ������
 * @param TRMDataObjectInterface $Container - ���� ����� ������, �� ����� ����������� �� �����,
 * ����� ����������� �������� ����� �������
 * 
 * @return TRMDataObjectsContainerInterface - ������-���������, ����������� ������� �� ���������
 */
public function getOneBy( $objectname, $fieldname, $value, TRMDataObjectInterface $Container = null)
{
    $this->clearCondition();
    $this->addCondition($objectname, $fieldname, $value);
    return $this->getOne( $Container );
}

public function getOne(TRMDataObjectInterface $Container = null)
{
    if( !$Container )
    {
        $Container = new $this->ObjectTypeName;
    }
    else
    {
        // ���� ������� ������ ��� ���������, 
        // ��������� ��� �� ������������ ����,
        // ���� �� ������� �������� , validateContainerObject ����������� ����������
        $this->validateContainerObject($Container);
    }
    
    // �������� ������ ��� �������� ������� ����������, 
    // ��� ���� ��� ������ ���������� ������, 
    // ������� ��������, ��� �� ������� getOne,
    if( !$this->getMainRepositoryFor($Container)->getOne( $Container->getMainDataObject() ) )
    {
        throw new TRMRepositoryNoDataObjectException( "������ ��� �������� ������� �������� �� ������� - "  . get_class($this) );
    }
    // ���� �� ���� �������� ���������� � ����������
    $this->getAllChildCollectionForContainer($Container);
    // ���� �� ���� ��������-������������ � ����������
    $this->getAllDependenciesObjectsForContainer($Container);
    
    return $Container;
}

/**
 * {@inheritDoc}
 * 
 * @param TRMDataObjectsCollectionInterface $ContainerCollection - ��������� � ������������, ������� ����� ��������� �������
 * @throws TRMRepositoryNoDataObjectException
 */
public function getAll(TRMDataObjectsCollectionInterface $ContainerCollection = null)
{
    if( !$ContainerCollection )
    {
        $ContainerCollection = new TRMTypedCollection( $this->ObjectTypeName );
    }
    else
    {
        // ���� �������� ��������� ��� ���������, 
        // ��������� �� �� ������������ ����� �������� ������,
        // ���� �� ������� �������� , validateContainerCollection ����������� ����������
        $this->validateContainerCollection($ContainerCollection);
    }

    // �������� ��������� ������� ��������
    $MainDataObjectsCollection = $this->MainDataObjectRepository->getAll();
    if( !$MainDataObjectsCollection )
    {
        throw new TRMRepositoryNoDataObjectException( "������ ��� ������� �������� �������� �� ������� - "  . get_class($this) );
    }
    // ���������� ��� ������� �������, ���������� �� �������
    foreach( $MainDataObjectsCollection as $MainDataObject )
    {
        // ��� ������� �������� ������� ��������� ���� ���������
        $Container = new $this->ObjectTypeName;
        
        $Container->setMainDataObject($MainDataObject);
        // ���� �� ���� �������� ���������� � ��������� ����������
        $this->getAllChildCollectionForContainer($Container);
        // ���� �� ���� ��������-������������ � ��������� ����������
        $this->getAllDependenciesObjectsForContainer($Container);
        // ��������� ��������� ��������� � ������� �������� 
        // � ����������� ������������� � �������������� ��������
        $ContainerCollection->addDataObject($Container);
    }
    
    return $ContainerCollection;
}
/**
 * {@inheritDoc}
 */
public function getBy($objectname, $fieldname, $value, TRMDataObjectsCollectionInterface $Collection = null)
{
    $this->clearCondition();
    $this->addCondition($objectname, $fieldname, $value);
    return $this->getAll($Collection);
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
    
    $this->getMainRepositoryFor($Container)->update( $Container->getMainDataObject() );

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

    $this->CollectionToUpdate->addDataObject($Container);
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
    
    $this->getMainRepositoryFor($Container)->insert( $Container->getMainDataObject() );

    // ���� �� ���� �������� ���������� � ����������
    foreach( $Container as $DataObjectsCollection )
    {
        TRMDIContainer::getStatic(TRMRepositoryManager::class)
                ->getRepository( $DataObjectsCollection->getObjectsType() )
                ->insertCollection( $DataObjectsCollection );
    }

    $this->CollectionToInsert->addDataObject($DataObject);
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

    $this->getMainRepositoryFor($Container)->delete( $Container->getMainDataObject() );
    
    $this->CollectionToDelete->addDataObject($Container);
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
 * 
 * @return boolean - � ������ ���������� ����� ������ true, ����� ������������� ����������
 * 
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
/**
 * ���������, ��� �� ��������� �������� � ��������� ���� �� ����, 
 * ��� � ������ ��������� �����������
 * 
 * @param TRMTypedCollection $ContainerCollection - �������� ��� ��������
 * 
 * @return boolean - � ������ ���������� ����� ������ true, ����� ������������� ����������
 * 
 * @throws TRMRepositoryUnknowDataObjectClassException
 */
public function validateContainerCollection(TRMTypedCollection $ContainerCollection)
{
    if( $ContainerCollection->getObjectsType() !== $this->ObjectTypeName )
    {
        throw new TRMRepositoryUnknowDataObjectClassException( get_class($ContainerCollection) . " ��� " . get_class($this) );
    }
    return true;
}

/**
 * ���������� ����������� �������� ��������� �� ����������� ��������� DataSource
 * 
 * @param bool $ClearCollectionFlag - ���� ����� ����� �������� ��������� ��������� ��������� ��������, 
 * �� ���� ���� ������� ���������� � false, ��� ����� ������������ �������� �������,
 * �� ����� ����������� ��������� doDelete ����� �������� ���������,
 * ��� �� �� ��������� �������� � ������� 2 ����!
 */
public function doDelete( $ClearCollectionFlag = true )
{
    if( !$this->CollectionToDelete->count() ) { return; }

    foreach( $this->CollectionToDelete as $Container )
    {
        foreach( $Container as $DataObjectsCollection )
        {
            // ��������� ��������� �������� $DataObjectsCollection 
            // � ��������������� ��� �������� � �����������
            TRMDIContainer::getStatic(TRMRepositoryManager::class)
                    ->getRepository( $DataObjectsCollection->getObjectsType() )
                    ->doDelete( $ClearCollectionFlag );
        }

        $this->getMainRepositoryFor($Container)->doDelete( $ClearCollectionFlag );
    }
    if( $ClearCollectionFlag ) { $this->CollectionToDelete->clearCollection(); }
}

/**
 * {@inheritDoc}
 * @param type $ClearCollectionFlag
 * @return void
 */
public function doInsert( $ClearCollectionFlag = true )
{
    if( !$this->CollectionToInsert->count() ) { return; }

    foreach( $this->CollectionToInsert as $Container )
    {
        foreach( $Container as $DataObjectsCollection )
        {
            // ��������� ��������� �������� $DataObjectsCollection 
            // � ��������������� ��� �������� � �����������
            TRMDIContainer::getStatic(TRMRepositoryManager::class)
                    ->getRepository( $DataObjectsCollection->getObjectsType() )
                    ->doInsert( $ClearCollectionFlag );
        }

        $this->getMainRepositoryFor($Container)->doInsert( $ClearCollectionFlag );
    }
    if( $ClearCollectionFlag ) { $this->CollectionToInsert->clearCollection(); }
}
/**
 * {@inheritDoc}
 * @param type $ClearCollectionFlag
 * @return void
 */
public function doUpdate( $ClearCollectionFlag = true )
{
    if( !$this->CollectionToUpdate->count() ) { return; }

    foreach( $this->CollectionToUpdate as $Container )
    {
        foreach( $Container as $DataObjectsCollection )
        {
            // ��������� ��������� �������� $DataObjectsCollection 
            // � ��������������� ��� �������� � �����������
            TRMDIContainer::getStatic(TRMRepositoryManager::class)
                    ->getRepository( $DataObjectsCollection->getObjectsType() )
                    ->doUpdate( $ClearCollectionFlag );
        }

        $this->getMainRepositoryFor($Container)->doUpdate( $ClearCollectionFlag );
    }
    if( $ClearCollectionFlag ) { $this->CollectionToUpdate->clearCollection(); }
}

public function clearQueryParams()
{
    $this->MainDataObjectRepository->clearQueryParams();
}

public function getKeepQueryParams()
{
    return $this->MainDataObjectRepository->getKeepQueryParams();
}

public function setKeepQueryParams($KeepQueryParams)
{
    $this->MainDataObjectRepository->setKeepQueryParams($KeepQueryParams);
}

} // TRMRepositoiesContainer