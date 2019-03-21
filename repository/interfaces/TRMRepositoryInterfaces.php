<?php

namespace TRMEngine\Repository\Interfaces;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;

/**
 * интерфейс для объектов репозитория, используемых в системе TRMEngine
 */
interface TRMRepositoryInterface
{
/**
 * связывает данные в репозитории с данными в объекте
 * 
 * @param TRMDataObjectInterface $object - объект модели с данными, 
 * должен реализовывать метод getDataObject(), 
 * который возвращает объект типа TRMDataObject, или производный от него
 */
public function setObject(TRMDataObjectInterface $object);
/**
 * Возвращает ссылку на текущий объект, с которым работает Repository
 * 
 * @return TRMDataObjectInterface
 */
public function getObject();
/**
 * обнуляет указатель на объект данных, сам объект не изменяяется, 
 * теряется только связь с репозиторием!!!
 */
public function unlinkObject();
/**
 * Производит выборку записей, удовлетворяющих указанным значениям для указанного поля
 * 
 * @param string $fieldname - поле. в котором выбираются значения
 * @param mixed $value - значение для сравнения и поиска
 * @param string $operator - =, > , < , != , LIKE, IN и т.д.
 * 
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
 */
function getBy($fieldname, $value, $operator = "=");
/**
 * Сохраняет объект в хранилище данных
 * 
 * @param TRMDataObjectInterface $object - объект, данные которого нужно сохранить в репозитории
 */
function save(TRMDataObjectInterface $object = null);
/**
 * обновляет или добавляет (если у объекта не установлено значение в уникальном поле или в поле первичного ключа) данные объекта в хранилище
 */
function update();

/**
 * добавляет данные объекта в хранилище, 
 * как првило используется INSERT ... ON DUPLICATE KEY UPDATE,
 * нужно смотреть реализацию
 */
//function insert();
/**
 * удаляет все данные об объекте из хранилища
 */
function delete();

} // TRMRepositoryInterface
