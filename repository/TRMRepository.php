<?php

namespace TRMEngine\Repository;

use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataObject\TRMDataObjectsCollection;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\DataSource\TRMSqlDataSource;
use TRMEngine\Repository\Exceptions\TRMRepositoryUnknowDataObjectClassException;
use TRMEngine\Repository\Interfaces\TRMRepositoryInterface;

/**
 * основной класс для репозитория объектов разных типов,
 * позволяет создавать, обновлять, удалять и читать данные для объектов из источника данных (DataSource).
 * В дочерних классах должен быть создан DataMapper,
 * а так же нуэно передать объект DataSource (в данной версии используется SQL с БД MySQL)
 */
abstract class TRMRepository implements TRMRepositoryInterface
{
/**
 * @var TRMDataSourceInterface - источник данных - объект для работы с данными в постоянном хранилище, в данном случае в БД
 */
protected $DataSource = null;

/**
 * @var string - имя типа данных, с которыми работает данный экземпляр класса Repository
 */
protected $ObjectTypeName = ""; //TRMDataObject::class; //"TRMDataObject";
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
 * @var TRMDataMapper 
 */
protected $DataMapper;

/**
 * @param string $objectclassname - имя класса для объектов, за которые отвечает этот Repository
 */
public function __construct($objectclassname)
{
    if( !class_exists($objectclassname) )
    {
        throw new TRMRepositoryUnknowDataObjectClassException( $objectclassname );
    }
    $this->ObjectTypeName = $objectclassname;
    
    $this->CollectionToInsert = new TRMDataObjectsCollection();
    $this->CollectionToUpdate = new TRMDataObjectsCollection();
    $this->CollectionToDelete = new TRMDataObjectsCollection();
}

/**
 * @return TRMDataMapper
 */
function getDataMapper()
{
    return $this->DataMapper;
}
/**
 * @param TRMDataMapper $DataMapper
 */
function setDataMapper(TRMDataMapper $DataMapper)
{
    $this->DataMapper = $DataMapper;
}


/**
 * @param TRMDataSourceInterface $datasource - источник данных - объект для работы с данными в постоянном хранилище, в данном случае в БД
 */
public function setDataSource(TRMDataSourceInterface $datasource)
{
    $this->DataSource = $datasource;
}

/**
 * @return TRMDataSourceInterface - источник данных - объект для работы с данными в постоянном хранилище, в данном случае в БД
 */
public function getDataSource()
{
    return $this->DataSource;
}

/**
 * устанавливает условие для WHERE секции SQL-запроса при выборке из БД,
 * 
 * @param string $objectname - имя объекта, содержащее поле для сравнения
 * @param string $fieldname - имя поля для сравнения
 * @param string|numeric|boolean $data - данные для сравнения
 * @param string $operator - оператор сравнения (=, !=, >, < и т.д.), поумолчанию =
 * @param string $andor - что ставить перед этим условием OR или AND ? по умолчанию AND
 * @param integer $quote - нужно ли брать в апострофы имена полей, по умолчанию нужно - TRMSqlDataSource::TRM_AR_QUOTE
 * @param string $alias - альяс для таблицы из которой сравнивается поле
 * @param integer $dataquote - если нужно оставить сравниваемое выражение без кавычек, 
 * то этот аргумент доложен быть - TRMSqlDataSource::TRM_AR_NOQUOTE
 * 
 * @return self - возвращает указатель на себя, это дает возможность писать такие выражения:
 * $this->setWhereCondition(...)->setWhereCondition(...)->setWhereCondition(...)...
 */
public function addCondition($objectname, $fieldname, $data, $operator = "=", $andor = "AND", $quote = TRMSqlDataSource::NEED_QUOTE, $alias = null, $dataquote = TRMSqlDataSource::NEED_QUOTE )
{
    $this->DataSource->addWhereParam($objectname, $fieldname, $data, $operator, $andor, $quote, $alias, $dataquote);
    return $this;
}
/**
 * очищает условия для выборки (в SQL-запросах секция WHERE)
 */
public function clearCondition()
{
    $this->DataSource->clearParams();
}

/**
 * Производит выборку одной записи, 
 * если ранее для $this->DataSource были установлены какие-то условия, то они будут использованы для выборки,
 * например начальный элемент, количество выбираемых записей, или условия WHERE
 * 
 * @param TRMDataObjectInterface $DataObject - если задан объект, то новый создаваться не будет,
 * будут заполняться свойства этого объекта
 * 
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
 */
public function getOne( TRMDataObjectInterface $DataObject = null )
{
    $this->DataSource->setLimit( 1 );

    // в случае ошибочного запроса DataSource->getDataFrom() выбрасывает исключение
    $result = $this->DataSource->getDataFrom( $this->DataMapper );
    // если в апросе нет данных, возвращается путсая коллекция
    if( !$result->num_rows ) { return null; }

    // должна вернуться только одна строка,
    // из нее создается объект данных
    return $this->getDataObjectFromDataArray($result->fetch_row(), $DataObject);
}

/**
 * Производит выборку одной записи, 
 * удовлетворяющих указанному значению для указанного поля.
 * Если в постоянном хранилище (БД) есть несколько записей, удовлтворящих запросу,
 * то все-равно вернется только один объект.
 * Все установленные ранее условия будут очищены и проигнорированны,
 * выборка из DataSource только под одному условию (полю),
 * если нужна выборка по нескольким условиям нужна функция getOne();
 * 
 * @param string $objectname - имя объекта для поиска по значению поля
 * @param string $fieldname - имя поля, в котором выбираются значения
 * @param mixed $value - значение для сравнения и поиска
 * @param TRMDataObjectInterface $DataObject - если задан объект, то новый создаваться не будет,
 * будут заполняться свойства этого объекта
 * 
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
 */
public function getOneBy($objectname, $fieldname, $value, TRMDataObjectInterface $DataObject = null)
{
    $this->clearCondition();
    $this->addCondition($objectname, $fieldname, $value);
    
    return $this->getOne( $DataObject );
}

/**
 * Производит выборку записей, удовлетворяющих указанному значению для указанного поля
 * 
 * @param string $objectname - имя объекта для поиска по значению поля
 * @param string $fieldname - имя поля, в котором выбираются значения
 * @param mixed $value - значение для сравнения и поиска
 * @param TRMDataObjectsCollectionInterface $Collection - если задан объект, то новый создаваться не будет,
 * будут заполняться свойства этого объекта
 * 
 * @return TRMDataObjectsCollectionInterface - объект, заполненный данными из хранилища
 */
public function getBy($objectname, $fieldname, $value, TRMDataObjectsCollectionInterface $Collection = null)
{
    $this->clearCondition();
    $this->addCondition($objectname, $fieldname, $value);
    return $this->getAll($Collection);
}

/**
 * Производит выборку всех записей,
 * если ранее для $this->DataSource были установлены какие-то условия, 
 * то они будут использованы для выборки,
 * например, начальный элемент, количество выбираемых записей, или условия WHERE
 * 
 * @param TRMDataObjectsCollectionInterface $Collection - если задан объект, то новый создаваться не будет,
 * будут заполняться свойства этого объекта
 * 
 * @return TRMDataObjectsCollection - коллекция с объектами, заполненными данными из постоянного хранилища, 
 * коллекция может быть пустой, если из БД вернулся пустой запрос, при этом никаких ошибок не возникает
 */
public function getAll( TRMDataObjectsCollectionInterface $Collection = null )
{
    if( isset($Collection) )
    {
        $NewGetCollection = $Collection;
    }
    else
    {
        $NewGetCollection = new TRMDataObjectsCollection();
    }

    // в случае ошибочного запроса DataSource->getDataFrom() выбрасывает исключение
    $result = $this->DataSource->getDataFrom($this->DataMapper);
    // если в апросе нет данных, возвращается путсая коллекция
    if( !$result->num_rows ) { return null; }

    // из каждой строки вернувшегося результата создается объект данных
    while( $Row = $result->fetch_row() )
    {
        // в коллекцию всегда добавляется новый объект
        $NewGetCollection->addDataObject( $this->getDataObjectFromDataArray($Row) );
    }

    return $NewGetCollection;
}
/**
 * @param array $DataArray - массив с данными, из которых будет создан объект
 * @param TRMDataObjectInterface $DataObject - если задан объект, то новый создаваться не будет,
 * будут заполняться свойства этого объекта
 * 
 * @return TRMDataObjectInterface - созданный объект данных, который обрабатывает этот экземпляр репозитория
 */
protected function getDataObjectFromDataArray( array $DataArray, TRMDataObjectInterface $DataObject = null )
{
    if( !$DataObject )
    {
        $DataObject = new $this->ObjectTypeName;
    }
    $k = 0;
    // преобразуем одномерный массив в многомерный согласно DataMapper-у
    foreach( $this->DataMapper as $TableName => $TableState )
    {
        foreach( array_keys($TableState[TRMDataMapper::FIELDS_INDEX]) as $FieldName )
        {
            $DataObject->setData(0, $TableName, $FieldName, $DataArray[$k++]);
        }
    }
    return $DataObject;
}

/**
 * Сохраняет объект в хранилище данных,
 * в данной реализации вызывает $this->update($DataObject),
 * который сохраняет объет в локальной коллекции,
 * фактическая запись данных объекта в хранилище произойдет после вызова doUpdate();
 * 
 * @param TRMDataObjectInterface $DataObject - объект, данные которого нужно сохранить в репозитории,
 */
public function save( TRMDataObjectInterface $DataObject)
{
    return $this->update($DataObject);
}

/**
 * @param TRMDataObjectInterface $DataObject - объект, который будет добавлен в коллекцию сохраняемых
 */
public function update( TRMDataObjectInterface $DataObject )
{
    // если указатель на этот объект уже есть в коллекции,
    // то addDataObject без специального флага не добавит его,
    // поэтому дубли объектов не появятся
    $this->CollectionToUpdate->addDataObject($DataObject);
}
/**
 * @param TRMDataObjectsCollectionInterface $Collection - коллекция, объекты которой будут добавлен в коллекцию сохраняемых
 */
public function updateCollection(TRMDataObjectsCollectionInterface $Collection )
{
    $this->CollectionToUpdate->mergeCollection($Collection);
}
/**
 * фактически обновляет объекты из подготовительной коллекции,
 * в случае работы с БД отправляет SQL-серверу UPDATE-запрос
 * 
 * @param bool $ClearCollectionFlag - если нужно после обновления сохранить коллекцию обновленных объектов, 
 * то этот флаг следует утсановить в false, это может понадобиться дочерним методам,
 * но перед завершением дочернего doUpdate нужно очистить коллекцию,
 * что бы не повторять обновление в будущем 2 раза!
 */
public function doUpdate( $ClearCollectionFlag = true )
{
    if( $this->CollectionToUpdate->count() )
    {
        $this->DataSource->update( $this->DataMapper, $this->CollectionToUpdate );

        if( $ClearCollectionFlag ) { $this->CollectionToUpdate->clearCollection(); }
    }
}

/**
 * Добавляет объект в подготовительную коллекцию для дальнейшей вставки в DataSource
 * 
 * @param TRMDataObjectInterface $DataObject - объект, который будет добавлен в коллекцию сохраняемых
 */
public function insert( TRMDataObjectInterface $DataObject )
{
    // если указатель на этот объект уже есть в коллекции,
    // то addDataObject без специального флага не добавит его,
    // поэтому дубли объектов не появятся
    $this->CollectionToInsert->addDataObject($DataObject);
}
/**
 * @param TRMDataObjectsCollectionInterface $Collection - коллекция, объекты которой будут добавлен в коллекцию вставляемых
 */
public function insertCollection(TRMDataObjectsCollectionInterface $Collection )
{
    $this->CollectionToInsert->mergeCollection($Collection);
}
/**
 * производит фактический вызов метода добавляения данных в постоянное хранилище DataSource
 * 
 * @param bool $ClearCollectionFlag - если нужно после удаления сохранить коллекцию удаленных объектов, 
 * то этот флаг следует утсановить в false, это может понадобиться дочерним методам,
 * но перед завершением дочернего doDelete нужно очистить коллекцию,
 * что бы не повторять удаление в будущем 2 раза!
 * 
 * @param bool $ClearCollectionFlag - если нужно после обновления сохранить коллекцию добавленных объектов, 
 * то этот флаг следует утсановить в false, это может понадобиться дочерним методам,
 * но перед завершением дочернего doInsert нужно очистить коллекцию,
 * что бы не повторять вставку в будущем 2 раза!
 */
public function doInsert( $ClearCollectionFlag = true )
{
    if( $this->CollectionToInsert->count() )
    {
        $this->DataSource->insert( $this->DataMapper, $this->CollectionToInsert );

        if( $ClearCollectionFlag ) { $this->CollectionToInsert->clearCollection(); }
    }
}
/**
 * Добавляет объект в подготовительную коллекцию для дальнейшего удаления в DataSource
 * 
 * @param TRMDataObjectInterface $DataObject - объект, который будет добавлен в коллекцию удаляемых
 */
public function delete( TRMDataObjectInterface $DataObject)
{
    // если указатель на этот объект уже есть в коллекции,
    // то addDataObject без специального флага не добавит его,
    // поэтому дубли объектов не появятся
    $this->CollectionToDelete->addDataObject($DataObject);
}
/**
 * @param TRMDataObjectsCollectionInterface $Collection - коллекция, объекты которой будут добавлен в коллекцию удаляемых
 */
public function deleteCollection(TRMDataObjectsCollectionInterface $Collection )
{
    $this->CollectionToDelete->mergeCollection($Collection);
}
/**
 * производит фактичесоке удаление данных объетов коллекции из постоянного хранилища DataSource
 * 
 * @param bool $ClearCollectionFlag - если нужно после удаления сохранить коллекцию удаленных объектов, 
 * то этот флаг следует утсановить в false, это может понадобиться дочерним методам,
 * но перед завершением дочернего doDelete нужно очистить коллекцию,
 * что бы не повторять удаление в будущем 2 раза!
 */
public function doDelete( $ClearCollectionFlag = true )
{
    if( $this->CollectionToDelete->count() )
    {
        $this->DataSource->delete( $this->DataMapper, $this->CollectionToDelete );

        if( $ClearCollectionFlag ) { $this->CollectionToDelete->clearCollection(); }
    }
}

/**
 * Все данные, которые были добавлены в коллекции для вставки, добавления и удаления 
 * будут фактически добавлены, всталвены и удалены, соответсвенно из постоянного хранилища DataSource. 
 * вызывается сначала doInsert, затем - doUpdate, затем - doDelete !
 */
public function doAll()
{
    $this->doInsert();
    $this->doUpdate();
    $this->doDelete();
}


} // TRMRepository
