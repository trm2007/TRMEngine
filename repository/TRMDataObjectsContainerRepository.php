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
 * общий класс для репозитория контейнера объектов
 */
abstract class TRMDataObjectsContainerRepository implements TRMRepositoryInterface
{
const TRM_GET_OBJECT_EVENT_INDEX = 1;
const TRM_UPDATE_OBJECT_EVENT_INDEX = 2;
const TRM_DELETE_OBJECT_EVENT_INDEX = 4;
/**
 * @var string - имя типа данных, с которыми работает данный экземпляр класса Repository
 */
protected $ObjectTypeName = TRMDataObjectsContainer::class;
/**`
 * @var TRMDataObjectsContainerInterface - контейнер объектов данных
 */
protected $DataObjectsContainer;
/**
 * @var string - имя события, которое генерируется репозиторием при получении объетка
 */
protected $GetEventName = "";
/**
 * @var string - имя события, которое генерируется репозиторием при обновлении объетка
 */
protected $UpdateEventName = "";
/**
 * @var string - имя события, которое генерируется репозиторием при удалении объетка
 */
protected $DeleteEventName = "";


/**
 * при создании конструктор дочеренего объекта должен передать имена событий, 
 * которые будут генерироваться при наступлении 3-х событий - получение/обновление/удаление
 * 
 * @param string $objectclassname - имя класса для объектов, за которые отвечает этот Repository
 * @param string $GetEventName - имя события, которое генерируется репозиторием при получении объетка
 * @param string $UpdateEventName - имя события, которое генерируется репозиторием при обновлении объетка
 * @param string $DeleteEventName - имя события, которое генерируется репозиторием при удалении объетка
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
 * @return TRMIdDataObjectRepository - возвращает объект (точнее ссылку) на репозиторий для главного объекта
 */
public function getMainRepository()
{
    return TRMDIContainer::getStatic(TRMRepositoryManager::class)
            ->getRepositoryFor( $this->DataObjectsContainer->getMainDataObject() );
}

/**
 * Возвращает ссылку на текущий контейнер объектов, с которым работает Repository
 * 
 * @return TRMDataObjectsContainerInterface
 */
public function getObject()
{
    return $this->DataObjectsContainer;
}

/**
 * задает текущий контейнер объектов, с которым будет работать репозиторий, 
 * только ссылка, объект не копируется и все изменения, если произойдет чтение объекта из БД, будут в основном объекте,
 * 
 * @param TRMDataObjectInterface $DataObjectsContainer - текущий объект, с которым будет работать репозиторий, должен быть типа - TRMDataObjectsContainerInterface
 */
public function setObject(TRMDataObjectInterface $DataObjectsContainer)
{
    $this->DataObjectsContainer = $DataObjectsContainer;
    // при инициализации объект должны быть созданы все репозитории для дочерних объектов,
    // так как они могут прослушивать сообщения, отправляемые данным репозиторием о полчении, 
    // удалении или обновлении всего контейнера
    $this->setRepositoryArrayForContainer();
}

/**
 * при установке объекта создаются все репозитории для объектов контейнера,
 * что бы они могли прослушивать события главного объекта
 */
protected function setRepositoryArrayForContainer()
{
    $this->RepositoriesArray = array();

    foreach( $this->DataObjectsContainer as $DataObject )
    {
        // получаем репозиторий для текущего объекта...
        // одновременно устанавливает текущий объект для
        TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor($DataObject);
    }
}

/**
 * обнуляет указательна на объект данных, сам объект не изменяяется, рвется только связь с репозиторием!!!
 */
public function unlinkObject()
{
    $this->DataObjectsContainer = null;
}

/**
 * Производит выборку главного объекта, удовлетворяющего указанному значению для указанного поля,
 * поочередно вызывает метод getBy для репозиториев всех объектов-зависимостей,
 * от которых зависит главный объект контейнера, передавая ссылку в getBy через getDependence().
 * объекты-зависимости должны реализовывать TRMIdDataObjectInterface
 * и оповещает всех подписчиков-детей, что получен новый объект, 
 * передавая ссылку на него через стандартное событие TRMCommonEvent,
 * в свою очередь эти объекты устанавливают ссылка на главный объект контейнера как родителя,
 * поэтому при опвещении знаю, их родитель послал событие или нет!
 * 
 * @param string $objectname - имя объекта для поиска по значению
 * @param string $fieldname - поле, в котором выбираются значения
 * @param mixed $value - значение для сравнения и поиска
 * @param string $operator - =, > , < , != , LIKE, IN и т.д., поумолчанию "="
 * 
 * @return TRMDataObjectsContainerInterface - объект-контейнер, заполненный данными из хранилища
 */
public function getBy( $objectname, $fieldname, $value, $operator = "=")
{
    // если объект контейнера данных еще не ассоциирован с этим репозиторием,
    // то создаем новый и работаем с ним
    if( !$this->DataObjectsContainer )
    {
        $this->setObject(new $this->ObjectTypeName);
    }

    // получаем основные данные для главной части составного объекта
    // без главного объекта нет смысла продолжать работу, поэтому проверям, 
    // что он получен родительским getBy,
    // там же вызывается метод setObject, который связывает все зависимости
    $this->getMainRepository()->getBy( $objectname, $fieldname, $value, $operator );

    // в цикле получаются все зависимости для главного объекта, 
    // которые связаны и есть в контейнере (массиве зависимостей)
    foreach( $this->DataObjectsContainer as $Index => $DataObject )
    {
        // если проверяемого объекта нет в зависимостях для главного в контейнере, 
        // то для него не вызываем getById, пропускаем и переходим к следующему объекту...
        // метод getBy такого объекта будет позже вызван через механизм событий
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
        // информируем всех наблюдателей, что получен главный объект из хранилища
        TRMDIContainer::getStatic(TRMEventManager::class)->notifyObservers(
                new TRMCommonEvent( // создается объект события
                        $this, // передаем ссылку на инициатора события, т.е. на себя
                        $this->GetEventName // тип события (его имя)
                    )
                );
    }

    return $this->DataObjectsContainer;
}

/**
 * обновляет объект товара
 * и все дочерние объекты в контейнере, 
 * если они подписаны на событие updateComplexProductDBEvent.
 * обновление происходит не затрагивая объекты-зависимости!!!
 * зависимости - это отдельные независимые сущности, обновляются отдельно,
 * либо должен использоваться механизм
 * 
 * @return boolean
 */
public function update()
{
    if( !$this->getMainRepository()->update() ) { return false; }

    if( !empty($this->UpdateEventName) )
    {
        // информируем всех наблюдателей, что обновлен галвный объект - событие UpdateEventName
        TRMDIContainer::getStatic(TRMEventManager::class)->notifyObservers(
                new TRMCommonEvent( // создается объект события
                        $this, // передаем ссылку на инициатора события, т.е. на себя
                        $this->UpdateEventName // тип события (его имя)
                    )
                );
    }
    return true;
}

/**
 * удаляет основной объект, без зависимостей!!!
 * Объекты-зависимости не зависят от главного объекта и удаляются автономно.
 * Вызывает событие deleteComplexProductDBEvent,
 * оповещая все дочерние объекты, что родитель удален,
 * затем происходит удадение главного объекта
 * 
 * @return boolean
 */
public function delete()
{
    if( !empty($this->DeleteEventName) )
    {
        // информируем всех наблюдателей, что главный объект будет удален - событие DeleteEventName
        TRMDIContainer::getStatic(TRMEventManager::class)->notifyObservers(
                new TRMCommonEvent( // создается объект события
                        $this, // передаем ссылку на инициатора события, т.е. на себя
                        $this->DeleteEventName // тип события (его имя)
                    )
                );
    }

    return $this->getMainRepository()->delete();
}

/**
 * сохраняет составной объект с главным объектом и вспомогательными в виде коллекции
 * 
 * @param TRMDataObjectInterface $object - сохраняемый объект, на самом деле должен быть тип TRMDataObjectsContainerInterface
 * будет установлен как текущий объект обрабатываемырепозиторием
 */
public function save(TRMDataObjectInterface $object = null)
{
    if( null !== $object )
    {
        $this->setObject($object);
    }
    if( null === $this->DataObjectsContainer )
    {
        throw new TRMRepositoryNoDataObjectException( "Не установлен объект с данными в репозитории " . get_class($this) );
    }
    return $this->update();
}


} // TRMRepositoiesContainer