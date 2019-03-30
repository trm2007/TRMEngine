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
 * общий класс для репозитория контейнера объектов
 */
class TRMDataObjectsContainerEventRepository extends TRMDataObjectsContainerRepository
{
const TRM_GET_OBJECT_EVENT_INDEX = 1;
const TRM_UPDATE_OBJECT_EVENT_INDEX = 2;
const TRM_DELETE_OBJECT_EVENT_INDEX = 4;
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
    parent::__construct($objectclassname);

    $this->GetEventName = $GetEventName;
    $this->UpdateEventName = $UpdateEventName;
    $this->DeleteEventName = $DeleteEventName;
}

/**
 * при установке объекта создаются все репозитории для объектов контейнера,
 * что бы они могли прослушивать события главного объекта
 */
protected function setRepositoryArrayForContainer(TRMDataObjectsContainerInterface $DataObjectsContainer)
{
    foreach( $DataObjectsContainer as $DataObject )
    {
        // получаем репозиторий для текущего объекта...
        // одновременно устанавливает текущий объект для
        TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor($DataObject);
    }
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
 * @param TRMDataObjectInterface $DataObject - если задан объект, то новый создаваться не будет,
 * будут заполняться свойства этого объекта
 * 
 * @return TRMDataObjectsContainerInterface - объект-контейнер, заполненный данными из хранилища
 */
public function getOneBy( $objectname, $fieldname, $value, TRMDataObjectInterface $DataObject = null)
{
    $Container = $this->getOneBy($objectname, $fieldname, $value, $DataObject);

    if( !empty($this->GetEventName) )
    {
        // информируем всех наблюдателей, что получен главный объект из хранилища
        TRMDIContainer::getStatic(TRMEventManager::class)->notifyObservers(
                new TRMCommonEvent( // создается объект события
                        $this, // передаем ссылку на инициатора события, т.е. на себя
                        $this->GetEventName, // тип события (его имя)
                        array( 
                            TRMRepositoryEvents::CONTAINER_OBJECT_INDEX => $Container,
                            TRMRepositoryEvents::MAIN_OBJECT_INDEX => $Container->getMainDataObject() )
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
function update(TRMDataObjectInterface $DataObject)
{
    $this->update($DataObject);

    if( !empty($this->UpdateEventName) )
    {
        // информируем всех наблюдателей, что обновлен галвный объект - событие UpdateEventName
        TRMDIContainer::getStatic(TRMEventManager::class)->notifyObservers(
                new TRMCommonEvent( // создается объект события
                        $this, // передаем ссылку на инициатора события, т.е. на себя
                        $this->UpdateEventName, // тип события (его имя)
                        array( 
                            TRMRepositoryEvents::CONTAINER_OBJECT_INDEX => $Container,
                            TRMRepositoryEvents::MAIN_OBJECT_INDEX => $Container->getMainDataObject() )
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
public function delete(TRMDataObjectInterface $Container)
{
    if( !empty($this->DeleteEventName) )
    {
        // информируем всех наблюдателей, что главный объект будет удален - событие DeleteEventName
        TRMDIContainer::getStatic(TRMEventManager::class)->notifyObservers(
                new TRMCommonEvent( // создается объект события
                        $this, // передаем ссылку на инициатора события, т.е. на себя
                        $this->DeleteEventName, // тип события (его имя)
                        array( 
                            TRMRepositoryEvents::CONTAINER_OBJECT_INDEX => $Container,
                            TRMRepositoryEvents::MAIN_OBJECT_INDEX => $Container->getMainDataObject() )
                    )
                );
    }

    $this->delete($Container);
}

/**
 * сохраняет составной объект с главным объектом и вспомогательными в виде коллекции
 * 
 * @param TRMDataObjectInterface $object - сохраняемый объект, на самом деле должен быть тип TRMDataObjectsContainerInterface
 * будет установлен как текущий объект обрабатываемырепозиторием
 */
public function save(TRMDataObjectInterface $object = null)
{
    if( null === $this->DataObjectsContainer )
    {
        throw new TRMRepositoryNoDataObjectException( "Не установлен объект с данными в репозитории " . get_class($this) );
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