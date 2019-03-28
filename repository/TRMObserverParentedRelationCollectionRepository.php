<?php

namespace TRMEngine\Repository;

use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\EventObserver\TRMCommonEvent;
use TRMEngine\EventObserver\TRMEventManager;

/**
 * ����� ��� ������ � ���������� ��������� ��������� �� ������������� �������
 */
abstract class TRMObserverParentedRelationCollectionRepository extends TRMParentedRelationCollectionRepository
{
/**
 * @var string - ��� ������� ��� ��������� �������, ������� ������������� ������ ����������� �����������
 */
protected $GetEventName = "";
/**
 * @var string - ��� ������� ��� ���������� �������, ������� ������������� ������ ����������� �����������
 */
protected $UpdateEventName = "";
/**
 * @var string - ��� ������� ��� �������� �������, ������� ������������� ������ ����������� �����������
 */
protected $DeleteEventName = "";

/**
 * ��� �������� ����������� ���������� ������� ������ �������� ����� �������, 
 * ������� ����� ������������� ���� ����������� - ���������/����������/��������,
 * ���� ��� ��� ������-�� ������� �� �������, ��� �� �������������
 * 
 * @param string $objectclassname - ��� ������ ��� ��������, �� ������� �������� ���� Repository
 * @param string $GetEventName - ��� ������� ��� ��������� �������, ������� ������������� ������ ����������� �����������
 * @param string $UpdateEventName - ��� ������� ��� ���������� �������, ������� ������������� ������ ����������� �����������
 * @param string $DeleteEventName - ��� ������� ��� �������� �������, ������� ������������� ������ ����������� �����������
 */
public function __construct($objectclassname, $GetEventName = "", $UpdateEventName = "", $DeleteEventName = "")
{
    parent::__construct($objectclassname);
    $this->GetEventName = $GetEventName;
    $this->UpdateEventName = $UpdateEventName;
    $this->DeleteEventName = $DeleteEventName;

    if( !empty($this->GetEventName) )
    {
        // ������ ������ ����������� ��������� �������� �� ���������, ����� ���������� ���� ������
        TRMDIContainer::getStatic(TRMEventManager::class)->addObserver($this, $this->GetEventName, "getHandle");
    }
    if( !empty($this->UpdateEventName) )
    {
        // ������ ������ ����������� ��������� ��������
        TRMDIContainer::getStatic(TRMEventManager::class)->addObserver($this, $this->UpdateEventName, "updateHandle");
    }
    if( !empty($this->DeleteEventName) )
    {
        // ������ ������ ����������� �������� �������� �� ���������
        TRMDIContainer::getStatic(TRMEventManager::class)->addObserver($this, $this->DeleteEventName, "deleteHandle");
    }
}

/**
 * ���������� ������� �������� �������
 * 
 * @param TRMCommonEvent $event
 */
public function getHandle(TRMCommonEvent $event)
{
    // � ���� ������� $event->getSender()->getObject() - ������ ������ ��������� � ������������ � ������ ������
    if( $this->CurrentObject && ($event->getSender()->getObject() === $this->CurrentObject->getParentDataObject()) )
    {
        $this->getByParent( $this->CurrentObject->getParentDataObject() );
    }
}

/**
 * ���������� ������� ���������� �������
 * 
 * @param TRMCommonEvent $event
 */
public function updateHandle(TRMCommonEvent $event)
{
    if( $this->CurrentObject && ($event->getSender()->getObject() === $this->CurrentObject->getParentDataObject()) )
    {
        $ParentRelationIdFieldName = $this->getParentRelationIdFieldName();
        $this->CurrentObject->changeAllValuesFor( $ParentRelationIdFieldName[0], 
                                                $ParentRelationIdFieldName[1], 
                                                $event->getSender()->getObject()->getId() );
        $this->update();
    }
}

/**
 * ���������� ������� �������� �������
 * 
 * @param TRMCommonEvent $event
 */
public function deleteHandle(TRMCommonEvent $event)
{
    if( $this->CurrentObject && ($event->getSender()->getObject() === $this->CurrentObject->getParentDataObject()) )
    {
        $this->delete();
    }
}


} // TRMObserverParentedRelationCollectionRepository