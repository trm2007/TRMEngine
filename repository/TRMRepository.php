<?php

namespace TRMEngine\Repository;

use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\TRMDataObject;
use TRMEngine\DataObject\TRMDataObjectsCollection;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\DataSource\TRMSqlCollectionDataSource;
use TRMEngine\DataSource\TRMSqlDataSource;
use TRMEngine\Repository\Exeptions\TRMRepositoryUnknowDataObjectClassException;
use TRMEngine\Repository\Interfaces\TRMRepositoryInterface;

// use TRMEngine\Repository\Exeptions\TRMRepositoryGetObjectException;

abstract class TRMRepository implements TRMRepositoryInterface
{
/**
 * @var TRMDataSourceInterface - источник данных - объект для работы с данными в постоянном хранилище, в данном случае в БД
 */
protected $DataSource = null;

/**
 * @var string - имя типа данных, с которыми работает данный экземпляр класса Repository
 */
protected $ObjectTypeName = TRMDataObject::class; //"TRMDataObject";

/**
 * @var TRMDataObjectsCollection - коллекция объектов , 
 * полученных при последнем вызове одного из методов getBy,
 * getOne - тоже заолняет коллекцию, но только одним объектом!
 */
protected $CollectionToGet;
/**
 * @var TRMDataObjectsCollection - коллекция объектов , 
 * добавленных в репозиторий, которые нужно обновить или добавить в постоянное хранилище DataSource
 */
protected $CollectionToUpdate;
/**
 * @var TRMDataObjectsCollection - коллекция объектов , 
 * добавленных в репозиторий, которые нужно обновить или добавить в постоянное хранилище DataSource
 */
protected $CollectionToInsert;
/**
 * @var TRMDataObjectsCollection - коллекция объектов , 
 * которые подготовлены к удалению из постоянного хранилища DataSource
 */
protected $CollectionToDelete;


/**
 * @param string $objectclassname - имя класса для объектов, за которые отвечает этот Repository
 */
public function __construct($objectclassname)
{
    $this->ObjectTypeName = (string)$objectclassname;
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
public function setWhereCondition($objectname, $fieldname, $data, $operator = "=", $andor = "AND", $quote = TRMSqlDataSource::NEED_QUOTE, $alias = null, $dataquote = TRMSqlDataSource::NEED_QUOTE )
{
    $this->DataSource->addWhereParam($objectname, $fieldname, $data, $operator, $andor, $quote, $alias, $dataquote);
    return $this;
}

/**
 * Производит выборку одной записи, 
 * если ранее для $this->DataSource были установлены какие-то условия, то они будут использованы для выборки,
 * например начальный элемент, количество выбираемых записей, или условия WHERE
 * 
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
 */
public function getOne()
{
    $this->DataSource->setLimit( 1 );
    
    $this->getAll();
    if( !$this->CollectionToGet->count() ) { return null; }
    
    $this->CollectionToGet->rewind();
    
    return $this->CollectionToGet->current();
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
 * @param string $operator - =, > , < , != , LIKE, IN и т.д.
 * 
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
 */
public function getOneBy($objectname, $fieldname, $value, $operator = "=")
{
    $this->DataSource->clearParams();
    $this->DataSource->addWhereParam($objectname, $fieldname, $value, $operator);
    $this->DataSource->setLimit( 1 );
    
    $this->getAll();
    if( !$this->CollectionToGet->count() ) { return null; }
    
    $this->CollectionToGet->rewind();
    
    return $this->CollectionToGet->current();
}

/**
 * Производит выборку записей, удовлетворяющих указанному значению для указанного поля
 * 
 * @param string $objectname - имя объекта для поиска по значению поля
 * @param string $fieldname - имя поля, в котором выбираются значения
 * @param mixed $value - значение для сравнения и поиска
 * @param string $operator - =, > , < , != , LIKE, IN и т.д.
 * 
 * @return TRMDataObjectsCollection - объект, заполненный данными из хранилища
 */
public function getBy($objectname, $fieldname, $value, $operator = "=")
{
    $this->DataSource->clearParams();
    $this->DataSource->addWhereParam($objectname, $fieldname, $value, $operator);
    return $this->getAll();
}

/**
 * Производит выборку всех записей,
 * если ранее для $this->DataSource были установлены какие-то условия, то они будут использованы для выборки,
 * например начальный элемент, количество выбираемых записей, или условия WHERE
 * 
 * @return TRMDataObjectsCollection - коллекция с объектами, заполненными данными из постоянного хранилища, 
 * коллекция может быть пустой, если из БД вернулся пустой запрос, при этом никаких ошибок не возникает
 */
public function getAll()
{
    $this->CollectionToGet->clearCollection();

    // в случае ошибочного запросу DataSource->getDataFrom() выбрасывает исключение
    $result = $this->DataSource->getDataFrom();
    // если в апросе нет данных, возвращается путсая коллекция
    if( !$result->num_rows ) { return $this->CollectionToGet; }

    // из каждой строки вернувшегося результата создается объект данных
    while( $Row = $result->fetch_row() )
    {
        $this->CollectionToGet[] = $this->getDataObjectFromDataArray($Row);
    }
    
    return $this->CollectionToGet;
}
/**
 * @param array $DataArray - массив с данными, из которых будет создан объект
 * 
 * @return TRMDataObjectInterface - созданный объект данных, который обрабатывает этот экземпляр репозитория
 */
protected function getDataObjectFromDataArray(array $DataArray)
{
    $DataObject = new $this->ObjectTypeName;
    $k = 0;
    // преобразуем одномерный массив в многомерный согласно DataMapper-у
    foreach( $this->SafetyFields as $TableName => $TableState )
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
public function save(TRMDataObjectInterface $DataObject)
{
    return $this->update($DataObject);
}

/**
 * @param TRMDataObjectInterface $DataObject - объект, который будет добавлен в коллекцию сохраняемых
 */
public function update(TRMDataObjectInterface $DataObject )
{
    foreach( $this->CollectionToUpdate as $CurrentObject )
    {
        // если указатель на этот объект уже есть в коллекции,
        // то завершаем работу функции
        if( $DataObject === $CurrentObject )
        {
            return;
        }
    }
    $this->CollectionToUpdate->addDataObject($DataObject);
    
}

public function doUpdate()
{
    if( $this->CollectionToUpdate->count() )
    {
        $this->DataSource->update( $this->CollectionToUpdate );
    }
    $this->CollectionToUpdate->clearCollection();
}

/**
 * Добавляет объект в подготовительную коллекцию для дальнейшей вставки в DataSource
 * 
 * @param TRMDataObjectInterface $DataObject - объект, который будет добавлен в коллекцию сохраняемых
 */
public function insert(TRMDataObjectInterface $DataObject )
{
    foreach( $this->CollectionToInsert as $CurrentObject )
    {
        // если указатель на этот объект уже есть в коллекции,
        // то завершаем работу функции
        if( $DataObject === $CurrentObject )
        {
            return;
        }
    }
    $this->CollectionToInsert->addDataObject($DataObject);
}
/**
 * производит фактический вызов метода добавляения данных в постоянное хранилище DataSource
 */
public function doInsert()
{
    if( $this->CollectionToInsert->count() )
    {
        $this->DataSource->update( $this->CollectionToInsert );
    }
    $this->CollectionToInsert->clearCollection();
}

/**
 * Добавляет объект в подготовительную коллекцию для дальнейшего удаления в DataSource
 * 
 * @param TRMDataObjectInterface $DataObject - объект, который будет добавлен в коллекцию сохраняемых
 */
public function delete(TRMDataObjectInterface $DataObject)
{
    foreach( $this->CollectionToDelete as $CurrentObject )
    {
        // если указатель на этот объект уже есть в коллекции,
        // то завершаем работу функции
        if( $DataObject === $CurrentObject )
        {
            return;
        }
    }
    $this->CollectionToDelete->addDataObject($DataObject);
}
/**
 * производите фактичесое удаление данных объетов коллекции из постоянного хранилища DataSource
 */
public function doDelete()
{
    if( $this->CollectionToDelete->count() )
    {
        $this->DataSource->delete( $this->CollectionToDelete );
    }
    $this->CollectionToDelete->clearCollection();
}

/**
 * Все данные, которые были добавлены в коллекции для вставки, добавления и удаления 
 * будут фактически добавлены, всталвены и удалены, соответсвенно из полстоянного хранилища DataSource. 
 * вызывается сначала doInsert, затем - , затем - doDelete !
 */
public function doAll()
{
    $this->doInsert();
    $this->doUpdate();
    $this->doDelete();
}

} // TRMRepository
