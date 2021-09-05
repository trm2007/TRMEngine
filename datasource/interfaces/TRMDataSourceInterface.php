<?php

namespace TRMEngine\DataSource\Interfaces;

use TRMEngine\DataMapper\TRMSafetyFields;
use TRMEngine\DataObject\TRMDataObjectsCollection;
use TRMEngine\DataSource\TRMSqlDataSource;
use TRMEngine\Exceptions\TRMSqlQueryException;
use TRMEngine\TRMDBObject;

/**
 *  абстрактный класс, общий для всех классов обработки записей из таблицы БД - TableName
 */
interface TRMDataSourceInterface
{
/**
 * @return TRMDBObject
 */
public function getDBObject();
/**
 * @param TRMDBObject $DBObject
 */
public function setDBObject(TRMDBObject $DBObject);

/**
 * устанавливает с какой записи начинать выборку - StartPosition
 * и какое количество записей выбирать - Count
 *
 * @param int $Count - какое количество записей выбирать
 * @param int $StartPosition - с какой записи начинать выборку
 */
public function setLimit( $Count , $StartPosition = null );
/**
 * задает массив сортировки по полям, старые значения удаляются
 *
 * @param array $orderfields - массив полей, по которым сортируется - array( fieldname1 => "ASC | DESC", ... )
 */
public function setOrder( array $orderfields );
/**
 * Устанавливает тип сортировки для поле при запросе
 *
 * @param string $OrderFieldName - имя поля , по которому устанавливается сортировка
 * @param bool $AscFlag - если true, то сортируется по этому полю как ASC, в противном случае, как DESC
 * @param int $FieldQuoteFlag - если установлен в значение TRMSqlDataSource::NEED_QUOTE,
 * то имя поля будет браться в апострофы `FieldName` ASC
 */
public function setOrderField( $OrderFieldName, $AscFlag = true, $FieldQuoteFlag = TRMSqlDataSource::NEED_QUOTE );
/**
 * добавляет поля в массив сортировки, 
 * если уже есть, то старые значения перезаписываются
 *
 * @param array $orderfields - массив полей, по которым сортируется 
 * array( fieldname1 => "ASC | DESC", fieldname2 => "ASC | DESC", ... )
 */
public function addOrder( array $orderfields );
/**
 * Добавляет поле, по которому будет произведена группировка
 * @param string $GroupFieldName
 */
public function setGroupField($GroupFieldName);
/**
 * очищает порядок сортировки
 */
public function clearOrder();
/**
 * очищает список полей для группировки
 */
public function clearGroup();
/**
 * очистка параметров WHERE запроса, порядок сортировки
 * и строк запросов SELECT, UPDATE/INSERT, DELETE
 */
public function clear();
/**
 * очищает ограничение выборки (на количество получаемых записей)
 */
public function clearLimit();
/**
 * очистка параметров для WHERE-условий в SQL-запросе
 */
public function clearParams();
/**
 * очистка параметров для HAVING-условий в SQL-запросе
 */
public function clearHavingParams();
/**
 * добавляет параметр для условия WHERE в запросе
 * 
 * @param string $tablename - имя таблицы для поля, которое добавляется к условию
 * @param string $fieldname - имя поля для сравнения
 * @param string|numeric|bool $data - данные для сравнения
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
public function addWhereParam($tablename, $fieldname, $data, $operator = "=", $andor = "AND", $quote = TRMSqlDataSource::NEED_QUOTE, $alias = null, $dataquote = TRMSqlDataSource::NEED_QUOTE);
/**
 * добавляет условие в секцию WHERE-запроса
 * 
 * @param string $tablename - имя таблицы для поля, которое добавляется к условию
 * @param array $param - массив с параметрами следующего формата<br>
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
public function addWhereParamFromArray($tablename, array $param);
/**
 * добавляет параметр для условия WHERE в запросе
 * 
 * @param string $tablename - имя таблицы для поля, которое добавляется к условию
 * @param string $fieldname - имя поля для сравнения
 * @param string|numeric|bool $data - данные для сравнения
 * @param string $operator - оператор сравнения (=, !=, >, < и т.д.), поумолчанию =
 * @param string $andor - что ставить перед этим условием OR или AND ? по умолчанию AND
 * @param integer $quote - нужно ли брать в апострофы имена полей, по умолчанию нужно - TRMSqlDataSource::NEED_QUOTE
 * @param string $alias - альяс для таблицы из которой сравнивается поле, если не задан, то будет совпадать с альясом главной таблицы
 * @param integer $dataquote - если нужно оставить сравниваемое выражение без кавычек, 
 * то этот аргумент доложен быть - TRMSqlDataSource::NOQUOTE
 * 
 * @return $this
 */
public function addHavingParam($tablename, $fieldname, $data, $operator = "=", $andor = "AND", $quote = TRMSqlDataSource::NEED_QUOTE, $alias = null, $dataquote = TRMSqlDataSource::NEED_QUOTE);
/**
 * добавляет условие в секцию WHERE-запроса
 * 
 * @param string $tablename - имя объекта для которого устанавливается поле
 * @param array $param - массив с параметрами следующего формата<br>
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
public function addHavingParamFromArray($tablename, array $param);
/**
 * добавляет массив параметров к уже установленному
 *
 * @param string $tablename - имя объекта для которого устанавливаются параметры
 * @param array $params- параметры, используемые в запросе, как правило сюда передается ID-записи 
 * все должно передаваться в массиве array( $fieldname => array(value, operator, andor, quote, alias, dataquote), ...)
 * обязательными являются array(..., $fieldname => array(value), ...)
 */
public function generateParamsFrom($tablename, array $params);
/**
 * убирает условие из секции WHERE-запроса
 * 
 * @param string $tablename - имя объекта, для которого удалется условие для поля
 * @param string $key - имя поля, для которого удаляется условие
 * @param string $value - значение данных для сравнения, должно передаваться вместе с оператором,
 * что бы одноначно идентифицировать условие
 * @param string $operator - оператор сравнения, должен передаваться вместе со сзначением $value,
 * что бы одноначно идентифицировать условие
 * 
 * @return $this
 */
public function removeHavingParam( $tablename, $key, $value = null, $operator = null );
/**
 * убирает условие из секции WHERE-запроса
 * 
 * @param string $tablename - имя объекта, для которого удалется условие для поля
 * @param string $key - имя поля, для которого удаляется условие
 * @param string $value - значение данных для сравнения, должно передаваться вместе с оператором,
 * что бы одноначно идентифицировать условие
 * @param string $operator - оператор сравнения, должен передаваться вместе со сзначением $value,
 * что бы одноначно идентифицировать условие
 * 
 * @return $this
 */
public function removeWhereParam( $tablename, $key, $value = null, $operator = null );
/**
 * Выполняет запрос переданный в $query
 * 
 * @param string $query - строка SQL-запроса
 * 
 * @return \mysqli_result - объект-результат выполнения запроса
 * @throws TRMSqlQueryException - в случае неудачного выполнения запроса выбрасывается исключение
 */
public function executeQuery($query);
/**
 * считываем данные из БД используя запрос, который возвращает функция makeSelectQuery
 * 
 * @param TRMSafetyFields $SafetyFields - DataMapper, для которого формируется выборка из БД
 *
 * @return \mysqli_result - количество прочитанных строк из БД
 * @throws TRMSqlQueryException - в случае неудачного выполнения запроса выбрасывается исключение
 */
public function getDataFrom(TRMSafetyFields $SafetyFields);
/**
 * считываем данные из БД используя запрос, который возвращает функция makeSelectQuery
 * добавляет полученное множество к имеющимся данным
 *
 * @return int - количество прочитанных строк из БД
 * @throws TRMSqlQueryException - в случае неудачного выполнения запроса выбрасывается исключение
 */
//public function addDataFrom();
/**
 * обновляет записи в таблице БД данными из коллекции объектов-данных $DataCollection,
 * если записи еще нет в таблице, т.е. нет ID для текущей строки, то добавляет ее,
 * в данной версии использует INSERT ... ON DUPLICATE KEY UPDATE ...
 *
 * @param TRMSafetyFields $SafetyFields - DataMapper, для которого формируется выборка из БД
 * @param TRMDataObjectsCollection $DataCollection - коллекция с объектами данных
 * 
 * @return bool - если обновление прошло успешно, то вернет true, иначе - false
 */
public function update(TRMSafetyFields $SafetyFields, TRMDataObjectsCollection $DataCollection);
/**
 * добавляет новые записи в БД из коллекции $DataCollection, 
 * 
 * @param TRMSafetyFields $SafetyFields - DataMapper, для которого добавляются данные в БД
 * @param TRMDataObjectsCollection $DataCollection - коллекция с объектами данных
 * @param bool $ODKUFlag - если установлен в TRUE, 
 * то используется метод вставки с заменой, если встречаются дубликаты ключевых полей,
 * ON DUPLICATE KEY UPDATE, по умолчанию = FALSE - используется обычная вставка
 * 
 * @throws TRMSqlQueryException
 */
public function insert( TRMSafetyFields $SafetyFields, TRMDataObjectsCollection $DataCollection, $ODKUFlag = false );
/**
 * удаляет записи коллекции из таблиц БД,
 * из основной таблицы удаляются записи, которые удовлетворяют значению сохраненного ID-поля,
 * если такого нет, то сравниваются на совпадение значения из всех полей 
 * (доступных для записи, которые имют флаг TRM_AR_UPDATABLE_FIELD) и найденная запись удаляется,
 * так же удалются записи из дочерних таблиц, 
 * если у них стоит хотя бы одно поле доступное для редактирования - TRM_AR_UPDATABLE_FIELD
 * 
 * @param TRMSafetyFields $SafetyFields - DataMapper, для которого формируется выборка из БД
 * @param TRMDataObjectsCollection $DataCollection - коллекция с объектами данных
 * 
 * @return bool - возвращает результат запроса DELETE
 */
public function delete(TRMSafetyFields $SafetyFields, TRMDataObjectsCollection $DataCollection);
/**
 * выполняет запрос из нескольких (или одного) SQL-выражений
 * и завершает выполнение, очищает буфер для возможности выполнения следующих запросов, 
 * перебирает все результаты
 * 
 * @param string $querystring - строка SQL-запроса
 * @throws TRMSqlQueryException - в случае неудачного запроса выбрасывается исключение!
 */
public function completeMultiQuery($querystring);

} // TRMDataSourceInterface