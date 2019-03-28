<?php

namespace TRMEngine\Repository;

use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\EventObserver\TRMCommonEvent;
use TRMEngine\EventObserver\TRMEventManager;

/**
 * класс дл€ работы с хранилищем коллекции зависимой от родительского объекта
 */
abstract class TRMObserverParentedRelationCollectionRepository extends TRMParentedRelationCollectionRepository
{
/**
 * @var string - им€ событи€ при получении объетка, которое отслеживаетс€ данным экземпл€ром репозитори€
 */
protected $GetEventName = "";
/**
 * @var string - им€ событи€ при обновлении объетка, которое отслеживаетс€ данным экземпл€ром репозитори€
 */
protected $UpdateEventName = "";
/**
 * @var string - им€ событи€ при удалении объетка, которое отслеживаетс€ данным экземпл€ром репозитори€
 */
protected $DeleteEventName = "";

/**
 * при создании конструктор дочеренего объекта должен передать имена событий, 
 * которые будут отслеживатьс€ этим экземпл€ром - получение/обновление/удаление,
 * если им€ дл€ какого-то событи€ не указано, оно не отслеживаетс€
 * 
 * @param string $objectclassname - им€ класса дл€ объектов, за которые отвечает этот Repository
 * @param string $GetEventName - им€ событи€ при получении объетка, которое отслеживаетс€ данным экземпл€ром репозитори€
 * @param string $UpdateEventName - им€ событи€ при обновлении объетка, которое отслеживаетс€ данным экземпл€ром репозитори€
 * @param string $DeleteEventName - им€ событи€ при удалении объетка, которое отслеживаетс€ данным экземпл€ром репозитори€
 */
public function __construct($objectclassname, $GetEventName = "", $UpdateEventName = "", $DeleteEventName = "")
{
    parent::__construct($objectclassname);
    $this->GetEventName = $GetEventName;
    $this->UpdateEventName = $UpdateEventName;
    $this->DeleteEventName = $DeleteEventName;

    if( !empty($this->GetEventName) )
    {
        // объект должен отслеживать получение родител€ из хранилища, чтобы подгрузить свои данные
        TRMDIContainer::getStatic(TRMEventManager::class)->addObserver($this, $this->GetEventName, "getHandle");
    }
    if( !empty($this->UpdateEventName) )
    {
        // объект должен отслеживать изменение родител€
        TRMDIContainer::getStatic(TRMEventManager::class)->addObserver($this, $this->UpdateEventName, "updateHandle");
    }
    if( !empty($this->DeleteEventName) )
    {
        // объект должен отслеживать удаление родител€ из хранилища
        TRMDIContainer::getStatic(TRMEventManager::class)->addObserver($this, $this->DeleteEventName, "deleteHandle");
    }
}

/**
 * обработчик событи€ загрузки объекта
 * 
 * @param TRMCommonEvent $event
 */
public function getHandle(TRMCommonEvent $event)
{
    // в свою очередь $event->getSender()->getObject() - вернет объект св€занный с репозиторием в данный момент
    if( $this->CurrentObject && ($event->getSender()->getObject() === $this->CurrentObject->getParentDataObject()) )
    {
        $this->getByParent( $this->CurrentObject->getParentDataObject() );
    }
}

/**
 * обработчик событи€ обновлени€ объекта
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
 * обработчик событи€ удалени€ объекта
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