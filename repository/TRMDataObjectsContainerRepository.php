<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DataObject\TRMDataObjectsContainer;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\Repository\Exceptions\TRMRepositoryNoDataObjectException;
use TRMEngine\Repository\Exceptions\TRMRepositoryUnknowDataObjectClassException;
use TRMEngine\Repository\Interfaces\TRMIdDataObjectRepositoryInterface;

/**
 * общий класс для репозитория контейнера объектов
 */
class TRMDataObjectsContainerRepository implements TRMIdDataObjectRepositoryInterface
{
/**
 * @var string - имя типа данных, с которыми работает данный экземпляр класса Repository
 */
protected $ObjectTypeName = "";


/**
 * при создании конструктор дочеренего объекта должен передать имена событий, 
 * которые будут генерироваться при наступлении 3-х событий - получение/обновление/удаление
 * 
 * @param string $objectclassname - имя класса для объектов, за которые отвечает этот Repository
 */
public function __construct( $objectclassname )
{
    if( !class_exists($objectclassname) )
    {
        throw new TRMRepositoryUnknowDataObjectClassException( $objectclassname . " не зарегистрирован в системе - " . get_class($this) );
    }
    if( !is_subclass_of($objectclassname, TRMDataObjectsContainer::class) )
    {
        throw new TRMRepositoryUnknowDataObjectClassException( $objectclassname . " не является контейнером - " . get_class($this) );
    }

    $this->ObjectTypeName = $objectclassname;
}

/**
 * @param TRMDataObjectsContainerInterface $Container - контейнер объектов и коллекций, 
 * для главного объекта этого контейнера нужно получить репозиторий
 * 
 * @return TRMIdDataObjectRepository - возвращает объект (точнее ссылку) на репозиторий для главного объекта
 */
public function getMainRepository( TRMDataObjectsContainerInterface $Container )
{
    return TRMDIContainer::getStatic(TRMRepositoryManager::class)
            ->getRepositoryFor( $Container->getMainDataObject() );
}

/**
 * {inheritDoc}
 */
public function getById($id, TRMDataObjectInterface $DataObject = null)
{
    $IdFieldName = $this->getIdFieldName();
    $Container = $this->getOneBy($IdFieldName[0], $IdFieldName[1], $id, $DataObject);
    
    return $Container;
}

/**
 * Производит выборку главного объекта, удовлетворяющего указанному значению для указанного поля,
 * поочередно вызывает метод getOneBy для репозиториев всех объектов-зависимостей,
 * от которых зависит главный объект контейнера, передавая ссылку в getBy через getDependence().
 * объекты-зависимости должны реализовывать TRMIdDataObjectInterface
 * и для всех дочерних коллекций вызывает getByParent, 
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
    if( $DataObject === null )
    {
        $Container = new $this->ObjectTypeName;
    }
    else
    {
        // если передан объект для обработки, 
        // проверяем его на соответствие типу,
        // если не пройдет проверка , validateContainerObject выбрасывает исключение
        $this->validateContainerObject($DataObject);
        $Container = $DataObject;
    }
    
    // получаем данные для главного объекта контейнера, 
    // без главного объекта нет смысла продолжать работу, 
    // поэтому проверям, что он получен getOneBy,
    if( !$this->getMainRepository($Container)->getOneBy( 
            $objectname, 
            $fieldname, 
            $value, 
            $Container->getMainDataObject() ) )
    {
        throw new TRMRepositoryNoDataObjectException( "Данные для главного объекта получить не удалось - "  . get_class($this) );
    }
    // цикл по всем дочерним коллекциям в контейнере
    foreach( $Container as $Index => $Collection )
    {
        // это дочерние типизированные коллекции,
        // у каждого репозиторя объектов, которые хранятся в коллекциях, вызываем getByParent, 
        // тип для объектов коллекции можно получить через ее метод ->getObjectsType()
        $Collection = TRMDIContainer::getStatic(TRMRepositoryManager::class)
                ->getRepository( $Collection->getObjectsType() )
                ->getByParent( $Container->getMainDataObject(), $Collection );
    }
    // цикл по всем объектам-зависимостям в контейнере
    foreach( $Container->getDependenciesObjectsArray() as $Index => $DataObject )
    {
        $DependIndex = $Container->getDependenceField($Index);
        // Если это объект-зависимость для главного, то
        // для него вызываем getById
        TRMDIContainer::getStatic(TRMRepositoryManager::class)
                ->getRepositoryFor( $DataObject )
                ->getById(
                        $Container->getMainDataObject()->getFieldValue( $DependIndex[0], $DependIndex[1] ),
                        $DataObject
                        );
    }

    return $Container;
}

/**
 * обновляет объект товара
 * и все дочерние объекты в контейнере, 
 * если они подписаны на событие updateComplexProductDBEvent.
 * обновление происходит не затрагивая объекты-зависимости!!!
 * зависимости - это отдельные независимые сущности, обновляются отдельно,
 * либо должен использоваться механизм
 * 
 * @param TRMDataObjectInterface $Container - обновляемый объект-контейнер, 
 * на самом деле должен быть тип TRMDataObjectsContainerInterface
 * 
 * @return boolean
 */
function update( TRMDataObjectInterface $Container )
{
    $this->validateContainerObject($Container);
    
    $this->getMainRepository()->update( $Container->getMainDataObject() );

    // цикл по всем дочерним коллекциям в контейнере,
    // реализация итератора не затрагивает зависимости
    foreach( $Container as $DataObjectsCollection )
    {
        // это дочерние коллекции, для них вызываем updateCollection
        // добавляем коллекцию объектов $DataObjectsCollection 
        // к предварительной для обновления в репозитории
        TRMDIContainer::getStatic(TRMRepositoryManager::class)
                ->getRepository( $DataObjectsCollection->getObjectsType() )
                ->updateCollection( $DataObjectsCollection );
    }
}
/**
 * @param TRMDataObjectsCollectionInterface $Collection - коллекция объектов-контейнеров, 
 * которые будут добавлен в коллекцию обновляемых
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
    
    $this->getMainRepository()->update( $Container->getMainDataObject() );

    // цикл по всем дочерним коллекциям в контейнере
    foreach( $Container as $DataObjectsCollection )
    {
        TRMDIContainer::getStatic(TRMRepositoryManager::class)
                ->getRepository( $DataObjectsCollection->getObjectsType() )
                ->insertCollection( $DataObjectsCollection );
    }
}

public function insertCollection(TRMDataObjectsCollectionInterface $Collection)
{
    foreach( $Collection as $Container )
    {
        $this->insert($Container);
    }
}

/**
 * удаляет основной объект, без зависимостей!!!
 * Объекты-зависимости не зависят от главного объекта и удаляются автономно.
 * Вызывает событие deleteComplexProductDBEvent,
 * оповещая все дочерние объекты, что родитель удален,
 * затем происходит удадение главного объекта
 * 
 * @param TRMDataObjectInterface $Container - удаляемый объект-контейнер, 
 * на самом деле должен быть тип TRMDataObjectsContainerInterface
 * 
 * @return boolean
 */
public function delete( TRMDataObjectInterface $Container )
{
    $this->validateContainerObject($Container);

    // цикл по всем дочерним коллекциям в контейнере
    foreach( $Container as $DataObjectsCollection )
    {
        // добавляем коллекцию объектов $DataObjectsCollection 
        // к предварительной для удаления в репозитории
        TRMDIContainer::getStatic(TRMRepositoryManager::class)
                ->getRepository( $DataObjectsCollection->getObjectsType() )
                ->deleteCollection( $DataObjectsCollection );
    }

    $this->getMainRepository()->delete( $Container->getMainDataObject() );
}
/**
 * @param TRMDataObjectsCollectionInterface $Collection - коллекция объектов-контейнеров, 
 * которые будут добавлен в коллекцию удаляемых
 */
public function deleteCollection(TRMDataObjectsCollectionInterface $Collection)
{
    foreach( $Collection as $Container )
    {
        $this->delete($Container);
    }
}

/**
 * сохраняет составной объект с главным объектом и вспомогательными в виде коллекции
 * 
 * @param TRMDataObjectInterface $Container - сохраняемый объект-контейнер, 
 * на самом деле должен быть тип TRMDataObjectsContainerInterface
 */
public function save(TRMDataObjectInterface $Container)
{
    return $this->update($Container);
}

/**
 * проверяет, что объект обрабатывается именно этим репозиторием
 * 
 * @param TRMDataObjectsContainerInterface $Container - проверяемый объект
 * @throws TRMRepositoryUnknowDataObjectClassException
 */
public function validateContainerObject( TRMDataObjectsContainerInterface $Container )
{
    if( get_class($Container) !== $this->ObjectTypeName )
    {
        throw new TRMRepositoryUnknowDataObjectClassException( get_class($Container) . " для " . get_class($this) );
    }
    return true;
}


    public function doDelete() {
        
    }

    public function doInsert() {
        
    }

    public function doUpdate() {
        
    }

    public function getAll(TRMDataObjectsCollectionInterface $Collection = null) {
        
    }

    public function getBy($objectname, $fieldname, $value, TRMDataObjectsCollectionInterface $Collection = null) {
        
    }

    public function getOne(TRMDataObjectInterface $DataObject = null) {
        
    }


public function getIdFieldName()
{
    $type = $this->ObjectTypeName;
    return $type::getIdFieldName(); //$this->MainDataObject->getIdFieldName();
}

//public function setIdFieldName(array $IdFieldName)
//{
//    $type = $this->ObjectTypeName;
//    return $type::setIdFieldName($IdFieldName);
//
//}


} // TRMRepositoiesContainer