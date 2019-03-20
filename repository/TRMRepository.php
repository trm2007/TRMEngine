<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\TRMDataObject;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\DataSource\TRMSqlDataSource;
use TRMEngine\Repository\Exeptions\TRMRepositoryNoDataObjrctException;
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
 * @var TRMDataObjectInterface - ссылка на текущий объект
 */
protected $CurrentObject = null;

/**
 * @var string - имя типа данных, с которыми работает данный экземпляр класса Repository
 */
protected $ObjectTypeName = TRMDataObject::class; //"TRMDataObject";

/**
 * @param string $objectclassname - имя класса для объектов, за которые отвечает этот Repository
 */
public function __construct($objectclassname)
{
    $this->ObjectTypeName = (string)$objectclassname;
}

/**
 * связывает данные в репозитории с данными в объекте
 * 
 * @param TRMDataObjectInterface $object - объект данных
 * 
 * @throws TRMRepositoryUnknowDataObjectClassException
 */
public function setObject(TRMDataObjectInterface $object)
{
    if( !is_a($object, $this->ObjectTypeName) )
    {
        throw new TRMRepositoryUnknowDataObjectClassException( get_class($object) . " репозиторий " . get_class($this) );
    }
    $this->CurrentObject = $object;
    // $do = $this->CurrentObject->getDataObject();
    
    $this->DataSource->linkData( $object );
//    $this->DataSource->clear();
}

/**
 * Возвращает ссылку на текущий объект, с которым работает Repository
 * 
 * @return TRMDataObjectInterface
 */
public function getObject()
{
    return $this->CurrentObject;
}

/**
 * обнуляет указательна на объект данных, сам объект не изменяяется, рвется только связь с репозиторием!!!
 */
public function unlinkObject()
{
    $this->CurrentObject = null;
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
 * @param string $fieldname - имя поля для сравнения
 * @param string|numeric|boolean $data - данные для сравнения
 * @param string $operator - оператор сравнения (=, !=, >, < и т.д.), поумолчанию =
 * @param string $andor - что ставить перед этим условием OR или AND ? по умолчанию AND
 * @param integer $quote - нужно ли брать в апострофы имена полей, по умолчанию нужно - TRMARCommon::TRM_AR_QUOTE
 * @param string $alias - альяс для таблицы из которой сравнивается поле
 * @param integer $dataquote - если нужно оставить сравниваемое выражение без кавычек, 
 * то этот аргумент доложен быть - TRMARCommon::TRM_AR_NOQUOTE
 */
public function setWhereCondition($fieldname, $data, $operator = "=", $andor = "AND", $quote = TRMSqlDataSource::NEED_QUOTE, $alias = null, $dataquote = TRMSqlDataSource::NEED_QUOTE )
{
    $this->DataSource->addWhereParam($fieldname, $data, $operator, $andor, $quote, $alias, $dataquote);
}

/**
 * Производит выборку записей, удовлетворяющих указанным значениям для указанного поля
 * 
 * @param string $fieldname - поле. в котором выбираются значения
 * @param mixed $value - значение для сравнения и поиска
 * @param string $operator - =, > , < , != , LIKE, IN и т.д.
 * 
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
 */
public function getBy($fieldname, $value, $operator = "=")
{
    $this->DataSource->clearParams();
    $this->DataSource->addWhereParam($fieldname, $value, $operator);
    return $this->getAll();
}

/**
 * Производит выборку всех записей,
 * если ранее для $this->DataSource были установлены какие-то условия, то они будут использованы для выборки,
 * например начальный элемент, количество выбираемых записей, или условия WHERE
 * 
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища, 
 * объект может быть пустым, если из БД вернулся пустой запрос, при этом никаких ошибок не возникает
 */
public function getAll()
{
    if( null === $this->CurrentObject )
    {
        $this->setObject(new $this->ObjectTypeName);
    }
    if( !$this->DataSource->getDataFrom() )
    {
        $this->CurrentObject->clear();
    }
/*
    if( !$this->DataSource->getDataFrom() )
    {
        throw new TRMRepositoryGetObjectException( __METHOD__ . " Объект [{$this->ObjectTypeName}] получить не удалось!");
//        return null;
    }
 * 
 */

    return $this->CurrentObject;
}

/**
 * Сохраняет объект в хранилище данных
 * 
 * @param TRMDataObjectInterface $object - объект, данные которого нужно сохранить в репозитории,
 * если объект уже установлен ранее, то можно передать null, тогда будет сохранен ранее установленный объект
 * 
 * @return boolean
 * 
 * @throws TRMRepositoryNoDataObjrctException
 */
public function save(TRMDataObjectInterface $object = null)
{
    if( null !== $object )
    {
        $this->setObject($object);
    }
    if( null === $this->CurrentObject )
    {
        throw new TRMRepositoryNoDataObjrctException( "Не установлен объект с данными в репозитории " . get_class($this) );
    }
    return $this->update();
}

/**
 * 
 * @return boolean
 * 
 * @throws TRMRepositoryNoDataObjrctException
 */
public function update()
{
    if( null === $this->CurrentObject )
    {
        throw new TRMRepositoryNoDataObjrctException( __METHOD__ );
    }
    return $this->DataSource->update();
}

/**
 * 
 * @return boolean
 * 
 * @throws TRMRepositoryNoDataObjrctException
 */
public function insert()
{
    if( null === $this->CurrentObject )
    {
        throw new TRMRepositoryNoDataObjrctException( __METHOD__ );
    }
    return $this->DataSource->insert();
}

/**
 * 
 * @return boolean
 * 
 * @throws TRMRepositoryNoDataObjrctException
 */
public function delete()
{
    if( null === $this->CurrentObject )
    {
        throw new TRMRepositoryNoDataObjrctException( __METHOD__ );
    }
    return $this->DataSource->delete();
}


} // TRMRepository
