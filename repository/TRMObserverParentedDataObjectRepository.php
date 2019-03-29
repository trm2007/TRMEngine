<?php

namespace TRMEngine\Repository;

use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\EventObserver\TRMCommonEvent;
use TRMEngine\EventObserver\TRMEventManager;

/**
 * ����� ��� ������ � ���������� ��������� ��������� �� ������������� �������
 */
abstract class TRMObserverParentedDataObjectRepository extends TRMParentedDataObjectRepository
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
 * ��� �������� � ������������ ���������� ������� ������ �������� ����� �������, 
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
abstract public function getHandle(TRMCommonEvent $event);


/**
 * ���������� ������� ���������� �������
 * 
 * @param TRMCommonEvent $event
 */
abstract public function updateHandle(TRMCommonEvent $event);


/**
 * ���������� ������� �������� �������
 * 
 * @param TRMCommonEvent $event
 */
abstract public function deleteHandle(TRMCommonEvent $event);


} // TRMObserverParentedRelationCollectionRepository