<?php

namespace TRMEngine\Repository;

use TRMEngine\DataMapper\Interfaces\TRMDataMapperInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DataObject\TRMDataObjectsCollection;
use TRMEngine\DataObject\TRMTypedCollection;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\DataSource\TRMSqlDataSource;
use TRMEngine\DiContainer\Interfaces\TRMStaticFactoryInterface;
use TRMEngine\DiContainer\TRMStaticFactory;
use TRMEngine\Repository\Exceptions\TRMRepositoryNoDataObjectException;
use TRMEngine\Repository\Exceptions\TRMRepositoryUnknowDataObjectClassException;
use TRMEngine\Repository\Interfaces\TRMIdDataObjectRepositoryInterface;
use TRMEngine\Repository\Interfaces\TRMRepositoryInterface;

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
   * @var TRMRepositoryInterface - указатель на репозиторий 
   * для объектов типа главного объекта в контейнере
   */
  protected $MainDataObjectRepository = null;
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
   * @var TRMRepositoryManager 
   */
  protected $_RM;
  /**
   * @var TRMStaticFactory - фабрика, которая может создавать объекты данного типа
   */
  protected $Factory;

  /**
   * @param string $ObjectTypeName - тип объектов, с которыми работает этот Repository
   * @param TRMRepositoryManager $RM
   */
  public function __construct($ObjectTypeName, TRMRepositoryManager $RM, TRMStaticFactoryInterface $Factory) // TRMDIContainer $DIC )
  {
    if (!class_exists($ObjectTypeName)) {
      throw new TRMRepositoryUnknowDataObjectClassException($ObjectTypeName . " не зарегистрирован в системе - " . get_class($this));
    }
    if (!is_subclass_of($ObjectTypeName, TRMDataObjectsContainerInterface::class)) {
      throw new TRMRepositoryUnknowDataObjectClassException($ObjectTypeName . " не является контейнером - " . get_class($this));
    }
    $this->_RM = $RM; // $DIC->get(TRMRepositoryManager::class);
    $this->Factory = $Factory;

    $this->ObjectTypeName = $ObjectTypeName;
    // сразу получим репозиторий для главного объекта
    $type = $this->ObjectTypeName;
    $MainObjectType = $type::getMainDataObjectType();
    $this->MainDataObjectRepository = $this->_RM->getRepository($MainObjectType);

    $this->CollectionToInsert = new TRMDataObjectsCollection();
    $this->CollectionToUpdate = new TRMDataObjectsCollection();
    $this->CollectionToDelete = new TRMDataObjectsCollection();
  }

  /**
   * {@inheritDoc}
   */
  public function getIdFieldName()
  {
    return $this->MainDataObjectRepository->getDataMapper()->getIdFieldName();
    //    $type = $this->ObjectTypeName;
    //    return $type::getIdFieldName();
  }

  /**
   * @param TRMDataObjectsContainerInterface $Container - контейнер объектов и коллекций, 
   * для главного объекта этого контейнера нужно получить репозиторий
   * 
   * @return TRMIdDataObjectRepository - возвращает объект (точнее ссылку) на репозиторий для главного объекта
   */
  public function getMainRepositoryFor(TRMDataObjectsContainerInterface $Container)
  {
    return $this->_RM->getRepositoryFor($Container->getMainDataObject());
  }

  /**
   * устанавливает условие для WHERE секции SQL-запроса при выборке из БД,
   * 
   * @param string $objectname - имя объекта, содержащее поле для сравнения
   * @param string $fieldname - имя поля для сравнения
   * @param string|numeric|bool $data - данные для сравнения
   * @param string $operator - оператор сравнения (=, !=, >, < и т.д.), поумолчанию =
   * @param string $andor - что ставить перед этим условием OR или AND ? по умолчанию AND
   * @param integer $quote - нужно ли брать в апострофы имена полей, по умолчанию нужно - TRMSqlDataSource::TRM_AR_QUOTE
   * @param string $alias - альяс для таблицы из которой сравнивается поле
   * @param integer $dataquote - если нужно оставить сравниваемое выражение без кавычек, 
   * то этот аргумент доложен быть - TRMSqlDataSource::NOQUOTE
   * 
   * @return self - возвращает указатель на себя, это дает возможность писать такие выражения:
   * $this->setWhereCondition(...)->setWhereCondition(...)->setWhereCondition(...)...
   */
  public function addCondition(
    $objectname,
    $fieldname,
    $data,
    $operator = "=",
    $andor = "AND",
    $quote = TRMSqlDataSource::NEED_QUOTE,
    $alias = null,
    $dataquote = TRMSqlDataSource::NEED_QUOTE
  ) {
    $this->MainDataObjectRepository->addCondition(
      $objectname,
      $fieldname,
      $data,
      $operator,
      $andor,
      $quote,
      $alias,
      $dataquote
    );
    return $this;
  }
  /**
   * 
   * удаляет условие для WHERE секции SQL-запроса при выборке из БД,
   * 
   * @param string $objectname - имя объекта, содержащее поле для сравнения
   * @param string $fieldname - имя поля для сравнения
   * @param string|numeric|bool $data - данные для сравнения
   * @param string $operator - оператор сравнения
   * 
   * @return $this
   */
  public function removeCondition($objectname, $fieldname, $data = null, $operator = null)
  {
    $this->MainDataObjectRepository->removeCondition($objectname, $fieldname, $data, $operator);
    return $this;
  }

  /**
   * очищает условия для выборки (в SQL-запросах секция WHERE)
   */
  public function clearCondition()
  {
    $this->MainDataObjectRepository->clearCondition();
  }

  /**
   * @param int $Count - количество выбираемых элементов для коллекуии главного объекта!
   * @param int $StartPosition - позиция, с которой начинается выборка, null - с начала (по умолчанию)
   */
  public function setLimit($Count, $StartPosition = null)
  {
    $this->MainDataObjectRepository->setLimit($Count, $StartPosition);
  }

  /**
   * 
   * @param TRMDataObjectsContainerInterface $Container - объект контейнера, 
   * в котором заполняются из соответствующих репозиториев дочерние коллекции
   */
  protected function getAllChildCollectionForContainer(TRMDataObjectsContainerInterface $Container)
  {
    foreach ($Container as $Collection) {
      // это дочерние типизированные коллекции,
      // у каждого репозиторя объектов, которые хранятся в коллекциях, вызываем getByParent, 
      // тип для объектов коллекции можно получить через ее метод ->getObjectsType()
      $Rep = $this->_RM->getRepository($Collection->getObjectsType());
      $Rep->getByParent($Container->getMainDataObject(), $Collection);
    }
  }
  /**
   * 
   * @param TRMDataObjectsContainerInterface $Container - объект контейнера, 
   * в котором заполняются из соответствующих репозиториев дочерние коллекции
   */
  protected function getAllDependenciesObjectsForContainer(TRMDataObjectsContainerInterface $Container)
  {
    // что бы на каждой итерации цикла не вызывалась функция getDependenciesFieldsArray(),
    // сохраняем ее результат в переменной $DependenciesFieldsArray
    $DependenciesFieldsArray = $Container->getDependenciesFieldsArray();

    foreach ($DependenciesFieldsArray as $DependenceField) {
      // $DependenceField[0] - имя суб-объекта в главном объекте для связи
      // $DependenceField[1] - имя поля в суб-объекте главного объекта для связи
      // $DependenceField[2] - тип объекта-зависимости для этой связи 
      // это объект-зависимость для главного объекта,
      // поэтому для него вызываем getById,
      // в массиве зависимостей хранится только тип объекта-зависимости,
      // сам объект-зависимость, если он не установлен, то вернет его репозиторий
      $Container->setDependenceObject(
        $this->_RM
          ->getRepository($DependenceField[2])
          ->getById(
            $Container->getMainDataObject()->getData($DependenceField[0], $DependenceField[1]),
            $Container->getDependenceObject($DependenceField[2])
          )
      );
    }
  }

  /**
   * {@inheritDoc}
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
   * @param string $objectname - имя суб-объекта (таблицы в БД) для поиска по значению
   * @param string $fieldname - поле, в котором выбираются значения
   * @param mixed $value - значение для сравнения и поиска
   * @param TRMDataObjectInterface $Container - если задан объект, то новый создаваться не будет,
   * будут заполняться свойства этого объекта
   * 
   * @return TRMDataObjectsContainerInterface - объект-контейнер, заполненный данными из хранилища
   */
  public function getOneBy($objectname, $fieldname, $value, TRMDataObjectInterface $Container = null)
  {
    $this->clearCondition();
    $this->addCondition($objectname, $fieldname, $value);
    return $this->getOne($Container);
  }

  public function getOne(TRMDataObjectInterface $Container = null)
  {
    if (!$Container) {
      $Container = $this->Factory->create($this->ObjectTypeName); // new $this->ObjectTypeName;
    } else {
      // если передан объект для обработки, 
      // проверяем его на соответствие типу,
      // если не пройдет проверка , validateContainerObject выбрасывает исключение
      $this->validateContainerObject($Container);
    }

    // получаем данные для главного объекта контейнера, 
    // без него нет смысла продолжать работу, 
    // поэтому проверям, что он получен getOne,
    if (!$this->getMainRepositoryFor($Container)->getOne($Container->getMainDataObject())) {
      throw new TRMRepositoryNoDataObjectException("Данные для главного объекта получить не удалось - "  . get_class($this));
    }
    // цикл по всем дочерним коллекциям в контейнере
    $this->getAllChildCollectionForContainer($Container);
    // цикл по всем объектам-зависимостям в контейнере
    $this->getAllDependenciesObjectsForContainer($Container);

    return $Container;
  }

  /**
   * {@inheritDoc}
   * 
   * @param TRMDataObjectsCollectionInterface $ContainerCollection - коллекция с контейнерами, которые нужно заполнить данными
   * @throws TRMRepositoryNoDataObjectException
   */
  public function getAll(TRMDataObjectsCollectionInterface $ContainerCollection = null)
  {
    if (!$ContainerCollection) {
      $ContainerCollection = new TRMTypedCollection($this->ObjectTypeName);
    } else {
      // если передана коллекция для обработки, 
      // проверяем ее на соответствие типов одъектов данных,
      // если не пройдет проверка , validateContainerCollection выбрасывает исключение
      $this->validateContainerCollection($ContainerCollection);
    }

    // получаем коллекцию главных объектов
    $MainDataObjectsCollection = $this->MainDataObjectRepository->getAll();
    if (!$MainDataObjectsCollection) {
      throw new TRMRepositoryNoDataObjectException("Данные для главных объектов получить не удалось - "  . get_class($this));
    }
    // перебираем все главные объекты, полученные по условию
    foreach ($MainDataObjectsCollection as $MainDataObject) {
      // для каждого главного объекта создается свой контейнер
      $Container = $this->Factory->create($this->ObjectTypeName); // new $this->ObjectTypeName;

      $Container->setMainDataObject($MainDataObject);
      // цикл по всем дочерним коллекциям в очередном контейнере
      $this->getAllChildCollectionForContainer($Container);
      // цикл по всем объектам-зависимостям в очередном контейнере
      $this->getAllDependenciesObjectsForContainer($Container);
      // добавляем созданный контейнер с главным объектом 
      // и полученными зависимостями в результирующую коллекци
      $ContainerCollection->addDataObject($Container);
    }

    return $ContainerCollection;
  }
  /**
   * {@inheritDoc}
   */
  public function getBy($objectname, $fieldname, $value, TRMDataObjectsCollectionInterface $Collection = null)
  {
    $this->clearCondition();
    $this->addCondition($objectname, $fieldname, $value);
    return $this->getAll($Collection);
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
   * @return bool
   */
  function update(TRMDataObjectInterface $Container)
  {
    $this->validateContainerObject($Container);

    $this->getMainRepositoryFor($Container)->update($Container->getMainDataObject());

    // цикл по всем дочерним коллекциям в контейнере,
    // реализация итератора не затрагивает зависимости
    foreach ($Container as $DataObjectsCollection) {
      // это дочерние коллекции, для них вызываем updateCollection
      // добавляем коллекцию объектов $DataObjectsCollection 
      // к предварительной для обновления в репозитории
      $this->_RM
        ->getRepository($DataObjectsCollection->getObjectsType())
        ->updateCollection($DataObjectsCollection);
    }

    $this->CollectionToUpdate->addDataObject($Container);
  }
  /**
   * @param TRMDataObjectsCollectionInterface $Collection - коллекция объектов-контейнеров, 
   * которые будут добавлен в коллекцию обновляемых
   */
  public function updateCollection(TRMDataObjectsCollectionInterface $Collection)
  {
    foreach ($Collection as $Container) {
      $this->update($Container);
    }
  }

  public function insert(TRMDataObjectInterface $Container)
  {
    $this->validateContainerObject($Container);

    $this->getMainRepositoryFor($Container)->insert($Container->getMainDataObject());

    // цикл по всем дочерним коллекциям в контейнере
    foreach ($Container as $DataObjectsCollection) {
      $this->_RM
        ->getRepository($DataObjectsCollection->getObjectsType())
        ->insertCollection($DataObjectsCollection);
    }

    $this->CollectionToInsert->addDataObject($Container);
  }

  public function insertCollection(TRMDataObjectsCollectionInterface $Collection)
  {
    foreach ($Collection as $Container) {
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
   * @return bool
   */
  public function delete(TRMDataObjectInterface $Container)
  {
    $this->validateContainerObject($Container);

    // цикл по всем дочерним коллекциям в контейнере
    foreach ($Container as $DataObjectsCollection) {
      // добавляем коллекцию объектов $DataObjectsCollection 
      // к предварительной для удаления в репозитории
      $this->_RM
        ->getRepository($DataObjectsCollection->getObjectsType())
        ->deleteCollection($DataObjectsCollection);
    }

    $this->getMainRepositoryFor($Container)->delete($Container->getMainDataObject());

    $this->CollectionToDelete->addDataObject($Container);
  }
  /**
   * @param TRMDataObjectsCollectionInterface $Collection - коллекция объектов-контейнеров, 
   * которые будут добавлен в коллекцию удаляемых
   */
  public function deleteCollection(TRMDataObjectsCollectionInterface $Collection)
  {
    foreach ($Collection as $Container) {
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
   * 
   * @return bool - в случае совпадения типов вернет true, иначе выбрасывается исключение
   * 
   * @throws TRMRepositoryUnknowDataObjectClassException
   */
  public function validateContainerObject(TRMDataObjectsContainerInterface $Container)
  {
    if (get_class($Container) !== $this->ObjectTypeName) {
      throw new TRMRepositoryUnknowDataObjectClassException(get_class($Container) . " для " . get_class($this));
    }
    return true;
  }
  /**
   * проверяет, что бы коллекция работала с объектами того же типа, 
   * что и данный эеземпляр репозитория
   * 
   * @param TRMTypedCollection $ContainerCollection - коллеция для проверуи
   * 
   * @return bool - в случае совпадения типов вернет true, иначе выбрасывается исключение
   * 
   * @throws TRMRepositoryUnknowDataObjectClassException
   */
  public function validateContainerCollection(TRMTypedCollection $ContainerCollection)
  {
    if ($ContainerCollection->getObjectsType() !== $this->ObjectTypeName) {
      throw new TRMRepositoryUnknowDataObjectClassException(get_class($ContainerCollection) . " для " . get_class($this));
    }
    return true;
  }

  /**
   * производит фактичесоке удаление коллекции из постоянного хранилища DataSource
   * 
   * @param bool $ClearCollectionFlag - если нужно после удаления сохранить коллекцию удаленных объектов, 
   * то этот флаг следует утсановить в false, это может понадобиться дочерним методам,
   * но перед завершением дочернего doDelete нужно очистить коллекцию,
   * что бы не повторять удаление в будущем 2 раза!
   */
  public function doDelete($ClearCollectionFlag = true)
  {
    if (!$this->CollectionToDelete->count()) {
      return;
    }

    foreach ($this->CollectionToDelete as $Container) {
      foreach ($Container as $DataObjectsCollection) {
        // добавляем коллекцию объектов $DataObjectsCollection 
        // к предварительной для удаления в репозитории
        $this->_RM
          ->getRepository($DataObjectsCollection->getObjectsType())
          ->doDelete($ClearCollectionFlag);
      }

      $this->getMainRepositoryFor($Container)->doDelete($ClearCollectionFlag);
    }
    if ($ClearCollectionFlag) {
      $this->CollectionToDelete->clearCollection();
    }
  }

  /**
   * {@inheritDoc}
   * @param bool $ClearCollectionFlag - флаг, указывающий нужно ли очищать коллекцию
   * объектов CollectionToInsert после вставки
   * 
   * @return void
   */
  public function doInsert($ClearCollectionFlag = true)
  {
    if (!$this->CollectionToInsert->count()) {
      return;
    }

    foreach ($this->CollectionToInsert as $Container) {
      $this->getMainRepositoryFor($Container)->doInsert($ClearCollectionFlag);

      foreach ($Container as $DataObjectsCollection) {
        // перед обновлением коллекции устанавливаем для нее родительский элемент,
        // тем самым будет обновлен Id-родителя во всех дочерних объектах
        $DataObjectsCollection->setParentDataObject($Container);
        // вызываем doInsert из репозитория для объектов
        // хранящихся в очередной коллекции $DataObjectsCollection
        $this->_RM
          ->getRepository($DataObjectsCollection->getObjectsType())
          ->doInsert($ClearCollectionFlag);
      }
    }
    if ($ClearCollectionFlag) {
      $this->CollectionToInsert->clearCollection();
    }
  }
  /**
   * {@inheritDoc}
   * @param bool $ClearCollectionFlag - флаг, указывающий нужно ли очищать коллекцию
   * объектов CollectionToUpdate после обновления
   * 
   * @return void
   */
  public function doUpdate($ClearCollectionFlag = true)
  {
    if (!$this->CollectionToUpdate->count()) {
      return;
    }

    foreach ($this->CollectionToUpdate as $Container) {
      $this->getMainRepositoryFor($Container)->doUpdate($ClearCollectionFlag);

      foreach ($Container as $DataObjectsCollection) {
        // перед обновлением коллекции устанавливаем для нее родительский элемент,
        // тем самым будет обновлен Id-родителя во всех дочерних объектах
        $DataObjectsCollection->setParentDataObject($Container);
        // вызываем doUpdate из репозитория для объектов
        // хранящихся в очередной коллекции $DataObjectsCollection
        $this->_RM
          ->getRepository($DataObjectsCollection->getObjectsType())
          ->doUpdate($ClearCollectionFlag);
      }
    }
    if ($ClearCollectionFlag) {
      $this->CollectionToUpdate->clearCollection();
    }
  }

  public function clearQueryParams()
  {
    $this->MainDataObjectRepository->clearQueryParams();
  }

  public function getKeepQueryParams()
  {
    return $this->MainDataObjectRepository->getKeepQueryParams();
  }

  public function setKeepQueryParams($KeepQueryParams)
  {
    $this->MainDataObjectRepository->setKeepQueryParams($KeepQueryParams);
  }

  public function getNewObject(TRMDataObjectInterface $DataContainer = null)
  {
    if (!$DataContainer) {
      $DataContainer = $this->Factory->create($this->ObjectTypeName); // new $this->ObjectTypeName;
    }

    $DataContainer->setMainDataObject(
      $this->MainDataObjectRepository->getNewObject($DataContainer->getMainDataObject())
    );
    // что бы на каждой итерации цикла не вызывалась функция getDependenciesFieldsArray(),
    // сохраняем ее результат в переменной $DependenciesObjectsArray
    $DependenciesFieldsArray = $DataContainer->getDependenciesFieldsArray();

    foreach ($DependenciesFieldsArray as $DependenceField) {
      // $DependenceField[0] - имя суб-объекта в главном объекте для связи
      // $DependenceField[1] - имя поля в суб-объекте главного объекта для связи
      // $DependenceField[2] - тип объекта-зависимости для этой связи 
      $DataContainer->setDependenceObject(
        $this->_RM
          ->getRepository($DependenceField[2])
          ->getNewObject()
      );
    }

    return $DataContainer;
  }

  public function clearOrder()
  {
    $this->MainDataObjectRepository->clearOrder();
  }

  public function setOrderField($OrderFieldName, $AscFlag = true, $FieldQuoteFlag = TRMSqlDataSource::NEED_QUOTE)
  {
    $this->MainDataObjectRepository->setOrderField($OrderFieldName, $AscFlag, $FieldQuoteFlag);
  }

  /**
   * @return TRMDataMapperInterface
   */
  public function getDataMapper()
  {
    return $this->MainDataObjectRepository->getDataMapper();
  }
  /**
   * @return TRMDataSourceInterface
   */
  public function getDataSource()
  {
    return $this->MainDataObjectRepository->getDataSource();
  }
  /**
   * @param TRMDataMapperInterface $DataMapper
   */
  public function setDataMapper(TRMDataMapperInterface $DataMapper)
  {
    $this->MainDataObjectRepository->setDataMapper($DataMapper);
  }
  /**
   * @param TRMDataSourceInterface $datasource
   */
  public function setDataSource(TRMDataSourceInterface $datasource)
  {
    $this->MainDataObjectRepository->setDataSource($datasource);
  }
}