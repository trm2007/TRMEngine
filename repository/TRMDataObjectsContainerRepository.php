<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DataObject\TRMDataObjectsContainer;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\EventObserver\TRMCommonEvent;
use TRMEngine\EventObserver\TRMEventManager;
use TRMEngine\Repository\Exeptions\TRMRepositoryNoDataObjectException;
use TRMEngine\Repository\Interfaces\TRMRepositoryInterface;

/**
 * ����� ����� ��� ����������� ���������� ��������
 */
abstract class TRMDataObjectsContainerRepository implements TRMRepositoryInterface
{
const TRM_GET_OBJECT_EVENT_INDEX = 1;
const TRM_UPDATE_OBJECT_EVENT_INDEX = 2;
const TRM_DELETE_OBJECT_EVENT_INDEX = 4;
/**
 * @var string - ��� ���� ������, � �������� �������� ������ ��������� ������ Repository
 */
protected $ObjectTypeName = TRMDataObjectsContainer::class;
/**`
 * @var TRMDataObjectsContainerInterface - ��������� �������� ������
 */
protected $DataObjectsContainer;
/**
 * @var string - ��� �������, ������� ������������ ������������ ��� ��������� �������
 */
protected $GetEventName = "";
/**
 * @var string - ��� �������, ������� ������������ ������������ ��� ���������� �������
 */
protected $UpdateEventName = "";
/**
 * @var string - ��� �������, ������� ������������ ������������ ��� �������� �������
 */
protected $DeleteEventName = "";


/**
 * ��� �������� ����������� ���������� ������� ������ �������� ����� �������, 
 * ������� ����� �������������� ��� ����������� 3-� ������� - ���������/����������/��������
 * 
 * @param string $objectclassname - ��� ������ ��� ��������, �� ������� �������� ���� Repository
 * @param string $GetEventName - ��� �������, ������� ������������ ������������ ��� ��������� �������
 * @param string $UpdateEventName - ��� �������, ������� ������������ ������������ ��� ���������� �������
 * @param string $DeleteEventName - ��� �������, ������� ������������ ������������ ��� �������� �������
 */
public function __construct( $objectclassname, $GetEventName="", $UpdateEventName="", $DeleteEventName="" )
{
    $this->ObjectTypeName = (string)$objectclassname;

    $this->GetEventName = $GetEventName;
    $this->UpdateEventName = $UpdateEventName;
    $this->DeleteEventName = $DeleteEventName;
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
 * ������ ������� ��������� ��������, � ������� ����� �������� �����������, 
 * ������ ������, ������ �� ���������� � ��� ���������, ���� ���������� ������ ������� �� ��, ����� � �������� �������,
 * 
 * @param TRMDataObjectInterface $DataObjectsContainer - ������� ������, � ������� ����� �������� �����������, ������ ���� ���� - TRMDataObjectsContainerInterface
 */
public function setObject(TRMDataObjectInterface $DataObjectsContainer)
{
    $this->DataObjectsContainer = $DataObjectsContainer;
    // ��� ������������� ������ ������ ���� ������� ��� ����������� ��� �������� ��������,
    // ��� ��� ��� ����� ������������ ���������, ������������ ������ ������������ � ��������, 
    // �������� ��� ���������� ����� ����������
    $this->setRepositoryArrayForContainer();
}

/**
 * ��� ��������� ������� ��������� ��� ����������� ��� �������� ����������,
 * ��� �� ��� ����� ������������ ������� �������� �������
 */
protected function setRepositoryArrayForContainer()
{
    $this->RepositoriesArray = array();

    foreach( $this->DataObjectsContainer as $DataObject )
    {
        // �������� ����������� ��� �������� �������...
        // ������������ ������������� ������� ������ ���
        TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor($DataObject);
    }
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
 * ���������� �������� ����� getBy ��� ������������ ���� ��������-������������,
 * �� ������� ������� ������� ������ ����������, ��������� ������ � getBy ����� getDependence().
 * �������-����������� ������ ������������� TRMIdDataObjectInterface
 * � ��������� ���� �����������-�����, ��� ������� ����� ������, 
 * ��������� ������ �� ���� ����� ����������� ������� TRMCommonEvent,
 * � ���� ������� ��� ������� ������������� ������ �� ������� ������ ���������� ��� ��������,
 * ������� ��� ��������� ����, �� �������� ������ ������� ��� ���!
 * 
 * @param string $objectname - ��� ������� ��� ������ �� ��������
 * @param string $fieldname - ����, � ������� ���������� ��������
 * @param mixed $value - �������� ��� ��������� � ������
 * @param string $operator - =, > , < , != , LIKE, IN � �.�., ����������� "="
 * 
 * @return TRMDataObjectsContainerInterface - ������-���������, ����������� ������� �� ���������
 */
public function getBy( $objectname, $fieldname, $value, $operator = "=")
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

    // � ����� ���������� ��� ����������� ��� �������� �������, 
    // ������� ������� � ���� � ���������� (������� ������������)
    foreach( $this->DataObjectsContainer as $Index => $DataObject )
    {
        // ���� ������������ ������� ��� � ������������ ��� �������� � ����������, 
        // �� ��� ���� �� �������� getById, ���������� � ��������� � ���������� �������...
        // ����� getBy ������ ������� ����� ����� ������ ����� �������� �������
        if( !$this->DataObjectsContainer->isDependence($Index) )
        {
            continue;
        }
        $DependIndex = $this->DataObjectsContainer->getDependence($Index);
        
        TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor( $DataObject )
                        ->getById( $this->DataObjectsContainer->getMainDataObject()
                                        ->getFieldValue( $DependIndex[0], $DependIndex[1] )
                                );
    }

    if( !empty($this->GetEventName) )
    {
        // ����������� ���� ������������, ��� ������� ������� ������ �� ���������
        TRMDIContainer::getStatic(TRMEventManager::class)->notifyObservers(
                new TRMCommonEvent( // ��������� ������ �������
                        $this, // �������� ������ �� ���������� �������, �.�. �� ����
                        $this->GetEventName // ��� ������� (��� ���)
                    )
                );
    }

    return $this->DataObjectsContainer;
}

/**
 * ��������� ������ ������
 * � ��� �������� ������� � ����������, 
 * ���� ��� ��������� �� ������� updateComplexProductDBEvent.
 * ���������� ���������� �� ���������� �������-�����������!!!
 * ����������� - ��� ��������� ����������� ��������, ����������� ��������,
 * ���� ������ �������������� ��������
 * 
 * @return boolean
 */
public function update()
{
    if( !$this->getMainRepository()->update() ) { return false; }

    if( !empty($this->UpdateEventName) )
    {
        // ����������� ���� ������������, ��� �������� ������� ������ - ������� UpdateEventName
        TRMDIContainer::getStatic(TRMEventManager::class)->notifyObservers(
                new TRMCommonEvent( // ��������� ������ �������
                        $this, // �������� ������ �� ���������� �������, �.�. �� ����
                        $this->UpdateEventName // ��� ������� (��� ���)
                    )
                );
    }
    return true;
}

/**
 * ������� �������� ������, ��� ������������!!!
 * �������-����������� �� ������� �� �������� ������� � ��������� ���������.
 * �������� ������� deleteComplexProductDBEvent,
 * �������� ��� �������� �������, ��� �������� ������,
 * ����� ���������� �������� �������� �������
 * 
 * @return boolean
 */
public function delete()
{
    if( !empty($this->DeleteEventName) )
    {
        // ����������� ���� ������������, ��� ������� ������ ����� ������ - ������� DeleteEventName
        TRMDIContainer::getStatic(TRMEventManager::class)->notifyObservers(
                new TRMCommonEvent( // ��������� ������ �������
                        $this, // �������� ������ �� ���������� �������, �.�. �� ����
                        $this->DeleteEventName // ��� ������� (��� ���)
                    )
                );
    }

    return $this->getMainRepository()->delete();
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
        throw new TRMRepositoryNoDataObjectException( "�� ���������� ������ � ������� � ����������� " . get_class($this) );
    }
    return $this->update();
}


} // TRMRepositoiesContainer