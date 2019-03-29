<?php

namespace TRMEngine\Repository\Interfaces;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;

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
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
 */
public function getOne();
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
public function getOneBy($objectname, $fieldname, $value, $operator = "=");
/**
 * Производит выборку всех записей,
 * если ранее для $this->DataSource были установлены какие-то условия, то они будут использованы для выборки,
 * например начальный элемент, количество выбираемых записей, или условия WHERE
 * 
 * @return TRMDataObjectsCollection - коллекция с объектами, заполненными данными из постоянного хранилища, 
 * коллекция может быть пустой, если из БД вернулся пустой запрос, при этом никаких ошибок не возникает
 */
public function getAll();

/**
 * Производит выборку записей, удовлетворяющих указанному значению одного поля,
 * целесообразно применять, если нужно сделать выборку по одному полю 
 * без сложных WHERE запросов
 * 
 * @param string $objectname - имя объекта для поиска по значению поля
 * @param string $fieldname - имя поля, в котором выбираются значения
 * @param mixed $value - значение для сравнения и поиска
 * @param string $operator - =, > , < , != , LIKE, IN и т.д.
 * 
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
 */
public function getBy($objectname, $fieldname, $value, $operator = "=");
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

} // TRMRepositoryInterface

/**
 * интерфейс для объектов репозитория, используемых в системе TRMEngine
 */
interface TRMIdDataObjectRepositoryInterface
{
/**
 * получает данные объекта из хранилища по ID,
 * никакие условия кроме выборки по ID не срабатывают и удаляются!
 * 
 * @param scalar $id - идентификатор (Id) объекта
 * 
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
 */
public function getById($id);

/**
 * @return array - array(имя суб-объекта, имя поля) для ID у обрабатываемых данным репозиторием объектов
 */
public function getIdFieldName();

/**
 * @param array $IdFieldName - array(имя суб-объекта, имя поля) 
 * для ID у обрабатываемых данным репозиторием объектов
 */
public function setIdFieldName( array $IdFieldName );

} // TRMIdDataObjectRepositoryInterface