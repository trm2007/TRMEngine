<?php

namespace TRMEngine\Repository;

use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\EventObserver\TRMCommonEvent;
use TRMEngine\EventObserver\TRMEventManager;

/**
 * класс для работы с хранилищем коллекции зависимой от родительского объекта
 */
abstract class TRMObserverParentedDataObjectRepository extends TRMParentedDataObjectRepository
{
/**
 * @var string - имя события при получении объетка, которое отслеживается данным экземпляром репозитория
 */
protected $GetEventName = "";
/**
 * @var string - имя события при обновлении объетка, которое отслеживается данным экземпляром репозитория
 */
protected $UpdateEventName = "";
/**
 * @var string - имя события при удалении объетка, которое отслеживается данным экземпляром репозитория
 */
protected $DeleteEventName = "";

/**
 * при создании в конструкторе дочеренего объекта должны передать имена событий, 
 * которые будут отслеживаться этим экземпляром - получение/обновление/удаление,
 * если имя для какого-то события не указано, оно не отслеживается
 * 
 * @param string $objectclassname - имя класса для объектов, за которые отвечает этот Repository
 * @param string $GetEventName - имя события при получении объетка, которое отслеживается данным экземпляром репозитория
 * @param string $UpdateEventName - имя события при обновлении объетка, которое отслеживается данным экземпляром репозитория
 * @param string $DeleteEventName - имя события при удалении объетка, которое отслеживается данным экземпляром репозитория
 */
public function __construct($objectclassname, $GetEventName = "", $UpdateEventName = "", $DeleteEventName = "")
{
    parent::__construct($objectclassname);
    $this->GetEventName = $GetEventName;
    $this->UpdateEventName = $UpdateEventName;
    $this->DeleteEventName = $DeleteEventName;

    if( !empty($this->GetEventName) )
    {
        // объект должен отслеживать получение родителя из хранилища, чтобы подгрузить свои данные
        TRMDIContainer::getStatic(TRMEventManager::class)->addObserver($this, $this->GetEventName, "getHandle");
    }
    if( !empty($this->UpdateEventName) )
    {
        // объект должен отслеживать изменение родителя
        TRMDIContainer::getStatic(TRMEventManager::class)->addObserver($this, $this->UpdateEventName, "updateHandle");
    }
    if( !empty($this->DeleteEventName) )
    {
        // объект должен отслеживать удаление родителя из хранилища
        TRMDIContainer::getStatic(TRMEventManager::class)->addObserver($this, $this->DeleteEventName, "deleteHandle");
    }
}

/**
 * обработчик события загрузки объекта
 * 
 * @param TRMCommonEvent $event
 */
abstract public function getHandle(TRMCommonEvent $event);


/**
 * обработчик события обновления объекта
 * 
 * @param TRMCommonEvent $event
 */
abstract public function updateHandle(TRMCommonEvent $event);


/**
 * обработчик события удаления объекта
 * 
 * @param TRMCommonEvent $event
 */
abstract public function deleteHandle(TRMCommonEvent $event);


} // TRMObserverParentedRelationCollectionRepository