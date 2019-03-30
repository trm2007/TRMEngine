<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DataObject\TRMDataObjectsContainer;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\Repository\Exeptions\TRMRepositoryNoDataObjectException;
use TRMEngine\Repository\Exeptions\TRMRepositoryUnknowDataObjectClassException;
use TRMEngine\Repository\Interfaces\TRMIdDataObjectRepositoryInterface;

/**
 * общий класс для репозитория контейнера объектов
 */
class TRMDataObjectsContainerRepository implements TRMIdDataObjectRepositoryInterface
{
/**
 * @var TRMDataObjectsCollectionInterface - коллекция объектов , 
 * полученных при последнем вызове одного из методов getBy,
 * getOne - тоже заолняет коллекцию, но только одним объектом!
 */
protected $GetCollection;
/**
 * @var TRMDataObjectsCollectionInterface - коллекция объектов , 
 * добавленных в репозиторий, которые нужно обновить или добавить в постоянное хранилище DataSource
 */
protected $CollectionToUpdate;
/**
 * @var TRMDataObjectsCollectionInterface - коллекция объектов , 
 * добавленных в репозиторий, которые нужно обновить или добавить в постоянное хранилище DataSource
 */
protected $CollectionToInsert;
/**
 * @var TRMDataObjectsCollectionInterface - коллекция объектов , 
 * которые подготовлены к удалению из постоянного хранилища DataSource
 */
protected $CollectionToDelete;
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
    if( !is_a($objectclassname, TRMDataObjectsContainer::class) )
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
    // если передан объект для обработки, 
    // проверяем его на соответствие типу
    if( $DataObject && $this->validateContainerObject($DataObject) )
    {
        $Container = $DataObject;
    }
    else
    {
        // создаем новый объект контейнера данных и работаем с ним
        $Container = new $this->ObjectTypeName;
    }

    // получаем данные для главной части составного объекта, 
    // без главного объекта нет смысла продолжать работу, 
    // поэтому проверям, что он получен getOneBy,
    if( !$this->getMainRepository()->getOneBy( $objectname, $fieldname, $value, $Container->getMainDataObject() ) )
    {
        throw new TRMRepositoryNoDataObjectException( "Данные для главного объекта получиьт не удалось - "  . get_class($this) );
    }

    // цикл по всем объектам в контейнере
    foreach( $Container as $Index => $DataObject )
    {
        $DependIndex = $Container->getDependence($Index);
        // если проверяемого объекта нет в зависимостях для главного объекта в контейнере, 
        // то это дочерние коллекции,
        // для них вызываем getByParent 
        if( !$DependIndex )
        {
            // getByParent возвращает коллекцию TRMDataObjectsCollection
//            $Container->setChildObject(
//                    $Index,
//                    TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor( $DataObject )
//                            ->getByParent( $Container->getMainDataObject() )
//                    );
            $DataObject = TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor( $DataObject )
                            ->getByParent( $Container->getMainDataObject(), $DataObject );

        }
        // Если это объект-зависимость для главного, то
        // для него вызываем getById
        else
        {

            // возвращает коллекцию TRMDataObjectsCollection
//            $Container->setDependence(
//                    $Index,
//                    TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor( $DataObject )
//                            ->getById( $Container->getMainDataObject()
//                                            ->getFieldValue( $DependIndex[0], $DependIndex[1] )
//                                    ),
//                    $DependIndex[0],
//                    $DependIndex[1] 
//                    );
            TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor( $DataObject )->getById(
                            $Container->getMainDataObject()
                                ->getFieldValue( $DependIndex[0], $DependIndex[1] ),
                            $DataObject
                            );
        }
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

    // цикл по всем объектам в контейнере
    foreach( $Container as $Index => $DataObjectsCollection )
    {
        // если проверяемого объекта нет в зависимостях для главного объекта в контейнере, 
        // то это дочерние коллекции, для них вызываем updateCollection
        // Если это объект-зависимость для главного объекта контейнера, 
        // то зависимость не трогаем, она - автономный объект, обновляется и удаляется независимо!
        if( !$Container->isDependence($Index) )
        {
            // добавляем коллекцию $DataObject к предварительной для обновления в репозитории
            TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor( $DataObjectsCollection )
                   ->updateCollection( $DataObjectsCollection );
        }
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

    // цикл по всем объектам в контейнере
    foreach( $Container as $Index => $DataObjectsCollection )
    {
        // если проверяемого объекта нет в зависимостях для главного объекта в контейнере, 
        // то это дочерние коллекции, для них вызываем deleteCollection
        // Если это объект-зависимость для главного, 
        // то его не трогаем, это автономный объект и обновляется, и удаляется независимо!
        if( !$Container->isDependence($Index) )
        {
            // добавляем коллекцию $DataObject к предварительной для удаления в репозитории
            TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor( $DataObjectsCollection )
                   ->deleteCollection( $DataObjectsCollection );
        }
    }

    $this->getMainRepository()->delete( $Container->getMainDataObject() );
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
}

    public function deleteCollection(\TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface $Collection) {
        
    }

    public function doDelete() {
        
    }

    public function doInsert() {
        
    }

    public function doUpdate() {
        
    }

    public function getAll(\TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface $Collection = null) {
        
    }

    public function getBy($objectname, $fieldname, $value, \TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface $Collection = null) {
        
    }

    public function getById($id, TRMDataObjectInterface $DataObject = null) {
        
    }

    public function getIdFieldName() {
        
    }

    public function getOne(TRMDataObjectInterface $DataObject = null) {
        
    }

    public function insert(TRMDataObjectInterface $DataObject) {
        
    }

    public function insertCollection(\TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface $Collection) {
        
    }

    public function setIdFieldName(array $IdFieldName) {
        
    }

    public function updateCollection(\TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface $Collection) {
        
    }

} // TRMRepositoiesContainer