<?php

namespace TRMEngine\Repository\Interfaces;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;

/**
 * интерфейс для объектов репозитория, используемых в системе TRMEngine
 */
interface TRMRepositoryInterface
{
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
public function getOne(TRMDataObjectInterface $DataObject = null);
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
public function getOneBy($objectname, $fieldname, $value, TRMDataObjectInterface $DataObject = null);
/**
 * Производит выборку всех записей,
 * если ранее для $this->DataSource были установлены какие-то условия, то они будут использованы для выборки,
 * например начальный элемент, количество выбираемых записей, или условия WHERE
 * 
 * @param TRMDataObjectsCollectionInterface $Collection - если задан объект, то новый создаваться не будет,
 * будут заполняться свойства этого объекта
 * 
 * @return TRMDataObjectsCollectionInterface - коллекция с объектами, заполненными данными из постоянного хранилища, 
 * коллекция может быть пустой, если из БД вернулся пустой запрос, при этом никаких ошибок не возникает
 */
public function getAll(TRMDataObjectsCollectionInterface $Collection = null);

/**
 * Производит выборку записей, удовлетворяющих указанному значению одного поля,
 * целесообразно применять, если нужно сделать выборку по одному полю 
 * без сложных WHERE запросов
 * 
 * @param string $objectname - имя объекта для поиска по значению поля
 * @param string $fieldname - имя поля, в котором выбираются значения
 * @param mixed $value - значение для сравнения и поиска
 * @param TRMDataObjectsCollectionInterface $Collection - если задан объект, то новый создаваться не будет,
 * будут заполняться свойства этого объекта
 * 
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
 */
public function getBy($objectname, $fieldname, $value, TRMDataObjectsCollectionInterface $Collection = null);
/**
 * Сохраняет объект в хранилище данных
 * 
 * @param TRMDataObjectInterface $DataObject - объект, данные которого нужно сохранить в репозитории
 */
function save(TRMDataObjectInterface $DataObject);
/**
 * обновляет или добавляет (если у объекта не установлено значение в уникальном поле или в поле первичного ключа) данные объекта в хранилище
 * @param TRMDataObjectInterface $DataObject - объект, который будет добавлен в коллекцию сохраняемых
 */
function update(TRMDataObjectInterface $DataObject);
/**
 * @param TRMDataObjectsCollectionInterface $Collection - коллекция, объекты которой будут добавлен в коллекцию сохраняемых
 */
public function updateCollection(TRMDataObjectsCollectionInterface $Collection );
/**
 * фактически обновляет объекты из подготовительной коллекции,
 * в случае работы с БД отправляет SQL-серверу UPDATE-запрос
 */
public function doUpdate();

/**
 * Добавляет объект в подготовительную коллекцию для дальнейшей вставки в DataSource
 * 
 * @param TRMDataObjectInterface $DataObject - объект, который будет добавлен в коллекцию сохраняемых
 */
public function insert( TRMDataObjectInterface $DataObject );
/**
 * @param TRMDataObjectsCollectionInterface $Collection - коллекция, объекты которой будут добавлен в коллекцию вставляемых
 */
public function insertCollection( TRMDataObjectsCollectionInterface $Collection );
/**
 * производит фактический вызов метода добавляения данных в постоянное хранилище DataSource
 */
public function doInsert();

/**
 * добавляет данные объекта в хранилище, 
 * как првило используется INSERT ... ON DUPLICATE KEY UPDATE,
 * нужно смотреть реализацию
 */
//function insert();
/**
 * удаляет все данные об объекте из хранилища
 * @param TRMDataObjectInterface $DataObject - объект, который будет добавлен в коллекцию сохраняемых
 */
function delete(TRMDataObjectInterface $DataObject);
/**
 * @param TRMDataObjectsCollectionInterface $Collection - коллекция, объекты которой будут добавлен в коллекцию удаляемых
 */
public function deleteCollection( TRMDataObjectsCollectionInterface $Collection );
/**
 * производит фактичесоке удаление данных объетов коллекции из постоянного хранилища DataSource
 */
public function doDelete();


} // TRMRepositoryInterface


/**
 * интерфейс для объектов репозитория, используемых в системе TRMEngine
 */
interface TRMIdDataObjectRepositoryInterface extends TRMRepositoryInterface
{
/**
 * получает данные объекта из хранилища по ID,
 * никакие условия кроме выборки по ID не срабатывают и удаляются!
 * 
 * @param scalar $id - идентификатор (Id) объекта
 * @param TRMDataObjectInterface $DataObject - если задан объект, то новый создаваться не будет,
 * будут заполняться свойства этого объекта
 * 
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
 */
public function getById($id, TRMDataObjectInterface $DataObject = null);

/**
 * @return array - array(имя суб-объекта, имя поля) для ID у обрабатываемых данным репозиторием объектов
 */
public function getIdFieldName();

/**
 * @param array $IdFieldName - array(имя суб-объекта, имя поля) 
 * для ID у обрабатываемых данным репозиторием объектов
 */
//public function setIdFieldName( array $IdFieldName );


} // TRMIdDataObjectRepositoryInterface