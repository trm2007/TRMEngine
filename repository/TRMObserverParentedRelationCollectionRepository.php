<?php

namespace TRMEngine\Repository;

use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\EventObserver\TRMCommonEvent;

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
        TRMDIContainer::getStatic("TRMEventManager")->addObserver($this, $this->GetEventName, "getHandle");
    }
    if( !empty($this->UpdateEventName) )
    {
        // ������ ������ ����������� ��������� ��������
        TRMDIContainer::getStatic("TRMEventManager")->addObserver($this, $this->UpdateEventName, "updateHandle");
    }
    if( !empty($this->DeleteEventName) )
    {
        // ������ ������ ����������� �������� �������� �� ���������
        TRMDIContainer::getStatic("TRMEventManager")->addObserver($this, $this->DeleteEventName, "deleteHandle");
    }
}

/**
 * ���������� ������� �������� �������
 * 
 * @param TRMCommonEvent $event
 */
public function getHandle(TRMCommonEvent $event)
{
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
        $this->CurrentObject->changeAllValuesFor( $this->ParentRelationIdFieldName, $event->getSender()->getObject()->getId() );
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