<?php

namespace TRMEngine\DataSource\Interfaces;

use TRMEngine\DataMapper\TRMSafetyFields;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataSource\TRMSqlDataSource;

/**
 *  абстрактный класс, общий для всех классов обработки записей из таблицы БД - TableName
 */
interface TRMDataSourceInterface
{
/**
 * @return TRMSafetyFields - объект DataMapper для текущего набора данных
 */
function getSafetyFields();
/**
 * @param TRMSafetyFields $SafetyFields - объект DataMapper для текущего набора данных
 */
function setSafetyFields(TRMSafetyFields $SafetyFields);
/**
 * устанавливает связь с объектом данных,
 * объект данных должен реализовывать интерфейс TRMDataObjectInterface
 * 
 * @param TRMDataObjectInterface $data - объект, данные которого будут получены и/или сохранены/удалены в БД
 */
public function linkData( TRMDataObjectInterface $data );
/**
 * получить последнее значение auto_increment поля для основной таблицы!
 * 
 * @return int - значение для поля auto_increment после операции вставки записи
 */
//public function getLastId();
/**
 * очистка параметров WHERE запроса и строки текущего запроса
 */
public function clear();

/**
 * очистка параметров для WHERE-условий в SQL-запросе
 */
public function clearParams();
/**
 * добавляет параметр для условия WHERE в запросе
 * 
 * @param string $fieldname - имя поля для сравнения
 * @param string|numeric|boolean $data - данные для сравнения
 * @param string $operator - оператор сравнения (=, !=, >, < и т.д.), поумолчанию =
 * @param string $andor - что ставить перед этим условием OR или AND ? по умолчанию AND
 * @param integer $quote - нужно ли брать в апострофы имена полей, по умолчанию нужно - TRMSqlDataSource::NEED_QUOTE
 * @param string $alias - альяс для таблицы из которой сравнивается поле, если не задан, то будет совпадать с альясом главной таблицы
 * @param integer $dataquote - если нужно оставить сравниваемое выражение без кавычек, 
 * то этот аргумент доложен быть - TRMSqlDataSource::NOQUOTE, 
 * по умолчанию в кавычках - TRMSqlDataSource::NEED_QUOTE
 * 
 * @return $this
 */
public function addWhereParam($fieldname, $data, $operator = "=", $andor = "AND", $quote = TRMSqlDataSource::NEED_QUOTE, $alias = null, $dataquote = TRMSqlDataSource::NEED_QUOTE);
/**
 * добавляет условие в секцию WHERE-запроса
 * 
 * @param array $params - массив с параметрами следующего формата<br>
 * array(
 * "key" => $fieldname,<br>
 * "value" => $data,<br>
 * "operator" => $operator,<br>
 * "andor" => $andor,<br>
 * "quote" => $quote,<br>
 * "alias" => $alias,<br>
 * "dataquote" => $dataquote );
 * 
 * @return $this
 */
public function addWhereParamFromArray(array $params);
/**
 * добавляет массив параметров к уже установленному
 *
 * @param array - параметры, используемые в запросе, как правило сюда передается ID-записи 
 * все должно передаваться в массиве array( $fieldname => array(value, operator, andor, quote, alias, dataquote), ...)
 * обязательными являются array(..., $fieldname => array(value), ...)
 */
public function generateParamsFrom(array $params = null);
/**
 * Выполняет запрос переданный в $query
 * 
 * @param string $query - строка SQL-запроса
 * 
 * @return mysqli_result - объект-результат выполнения запроса
 * @throws Exception - в случае неудачного выполнения запроса выбрасывается исключение
 */
public function executeQuery($query);
/**
 * считываем данные из БД используя запрос, который возвращает функция makeSelectQuery
 * перезаписывает связанный объект с данными
 *
 * @return int - количество прочитанных строк из БД
 * @throws Exception - в случае неудачного выполнения запроса выбрасывается исключение
 */
public function getDataFrom();
/**
 * считываем данные из БД используя запрос, который возвращает функция makeSelectQuery
 * добавляет полученное множество к имеющимся данным
 *
 * @return int - количество прочитанных строк из БД
 * @throws Exception - в случае неудачного выполнения запроса выбрасывается исключение
 */
public function addDataFrom();
/**
 * генерирует объект типа TRMSafetyFields - допустимых для записи и чтения полей,
 * а так же сразу формирует список полей на основании данных из таблиц БД,
 * обрамляет имена полей в апострофы и добавляет имя таблицы или альяса, если установлено
 *
 * @throws Exception - если не задано имя главной таблицы генерируется исключение
 */
//public function generateSafetyFromDB();
/**
 * обновляет записи в таблице БД данными из объекта-данных DataObject,
 * если записи еще нет в таблице, т.е. нет ID для текущей строки, то добавляет ее,
 * в данной версии использует INSERT ... ON DUPLICATE KEY UPDATE ...
 *
 * @return boolean - если обновление прошло успешно, то вернет true, иначе - false
 */
public function update();
/**
 * удаляет записи коллекции из таблиц БД,
 * из основной таблицы удаляются записи, которые удовлетворяют значению сохраненного ID-поля,
 * если такого нет, то сравниваются на совпадение значения из всех полей 
 * (доступных для записи, которые имют флаг TRM_AR_UPDATABLE_FIELD) и найденная запись удаляется,
 * так же удалются записи из дочерних таблиц, 
 * если у них стоит хотя бы одно поле доступное для редактирования - TRM_AR_UPDATABLE_FIELD
 * 
 * @return boolean - возвращает результат запроса DELETE
 */
public function delete();

} // TRMDataSourceInterface