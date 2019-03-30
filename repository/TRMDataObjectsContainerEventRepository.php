<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\EventObserver\TRMCommonEvent;
use TRMEngine\EventObserver\TRMEventManager;
use TRMEngine\Repository\Events\TRMRepositoryEvents;
use TRMEngine\Repository\Exeptions\TRMRepositoryNoDataObjectException;

/**
 * ����� ����� ��� ����������� ���������� ��������
 */
class TRMDataObjectsContainerEventRepository extends TRMDataObjectsContainerRepository
{
const TRM_GET_OBJECT_EVENT_INDEX = 1;
const TRM_UPDATE_OBJECT_EVENT_INDEX = 2;
const TRM_DELETE_OBJECT_EVENT_INDEX = 4;
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
    parent::__construct($objectclassname);

    $this->GetEventName = $GetEventName;
    $this->UpdateEventName = $UpdateEventName;
    $this->DeleteEventName = $DeleteEventName;
}

/**
 * ��� ��������� ������� ��������� ��� ����������� ��� �������� ����������,
 * ��� �� ��� ����� ������������ ������� �������� �������
 */
protected function setRepositoryArrayForContainer(TRMDataObjectsContainerInterface $DataObjectsContainer)
{
    foreach( $DataObjectsContainer as $DataObject )
    {
        // �������� ����������� ��� �������� �������...
        // ������������ ������������� ������� ������ ���
        TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor($DataObject);
    }
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
 * @param TRMDataObjectInterface $DataObject - ���� ����� ������, �� ����� ����������� �� �����,
 * ����� ����������� �������� ����� �������
 * 
 * @return TRMDataObjectsContainerInterface - ������-���������, ����������� ������� �� ���������
 */
public function getOneBy( $objectname, $fieldname, $value, TRMDataObjectInterface $DataObject = null)
{
    $Container = $this->getOneBy($objectname, $fieldname, $value, $DataObject);

    if( !empty($this->GetEventName) )
    {
        // ����������� ���� ������������, ��� ������� ������� ������ �� ���������
        TRMDIContainer::getStatic(TRMEventManager::class)->notifyObservers(
                new TRMCommonEvent( // ��������� ������ �������
                        $this, // �������� ������ �� ���������� �������, �.�. �� ����
                        $this->GetEventName, // ��� ������� (��� ���)
                        array( 
                            TRMRepositoryEvents::CONTAINER_OBJECT_INDEX => $Container,
                            TRMRepositoryEvents::MAIN_OBJECT_INDEX => $Container->getMainDataObject() )
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
function update(TRMDataObjectInterface $DataObject)
{
    $this->update($DataObject);

    if( !empty($this->UpdateEventName) )
    {
        // ����������� ���� ������������, ��� �������� ������� ������ - ������� UpdateEventName
        TRMDIContainer::getStatic(TRMEventManager::class)->notifyObservers(
                new TRMCommonEvent( // ��������� ������ �������
                        $this, // �������� ������ �� ���������� �������, �.�. �� ����
                        $this->UpdateEventName, // ��� ������� (��� ���)
                        array( 
                            TRMRepositoryEvents::CONTAINER_OBJECT_INDEX => $Container,
                            TRMRepositoryEvents::MAIN_OBJECT_INDEX => $Container->getMainDataObject() )
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
public function delete(TRMDataObjectInterface $Container)
{
    if( !empty($this->DeleteEventName) )
    {
        // ����������� ���� ������������, ��� ������� ������ ����� ������ - ������� DeleteEventName
        TRMDIContainer::getStatic(TRMEventManager::class)->notifyObservers(
                new TRMCommonEvent( // ��������� ������ �������
                        $this, // �������� ������ �� ���������� �������, �.�. �� ����
                        $this->DeleteEventName, // ��� ������� (��� ���)
                        array( 
                            TRMRepositoryEvents::CONTAINER_OBJECT_INDEX => $Container,
                            TRMRepositoryEvents::MAIN_OBJECT_INDEX => $Container->getMainDataObject() )
                    )
                );
    }

    $this->delete($Container);
}

/**
 * ��������� ��������� ������ � ������� �������� � ���������������� � ���� ���������
 * 
 * @param TRMDataObjectInterface $object - ����������� ������, �� ����� ���� ������ ���� ��� TRMDataObjectsContainerInterface
 * ����� ���������� ��� ������� ������ �������������������������
 */
public function save(TRMDataObjectInterface $object = null)
{
    if( null === $this->DataObjectsContainer )
    {
        throw new TRMRepositoryNoDataObjectException( "�� ���������� ������ � ������� � ����������� " . get_class($this) );
    }
    return $this->update();
}

    public function deleteCollection(Interfaces\TRMDataObjectsCollection $Collection) {
        
    }

    public function doDelete() {
        
    }

    public function doInsert() {
        
    }

    public function doUpdate() {
        
    }

    public function getAll() {
        
    }

    public function getOne() {
        
    }

    public function getBy($objectname, $fieldname, $value, $operator = "=") {
        
    }

    public function insert(TRMDataObjectInterface $DataObject) {
        
    }

    public function insertCollection(Interfaces\TRMDataObjectsCollection $Collection) {
        
    }

    public function updateCollection(Interfaces\TRMDataObjectsCollection $Collection) {
        
    }

} // TRMRepositoiesContainer