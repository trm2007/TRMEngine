<?php

namespace TRMEngine\DataSource\Interfaces;

use TRMEngine\DataMapper\TRMSafetyFields;
use TRMEngine\DataObject\TRMDataObjectsCollection;
use TRMEngine\DataSource\TRMSqlDataSource;
use TRMEngine\Exceptions\TRMSqlQueryException;

/**
 *  абстрактный класс, общий для всех классов обработки записей из таблицы БД - TableName
 */
interface TRMDataSourceInterface
{
/**
 * устанавливает с какой записи начинать выборку - StartPosition
 * и какое количество записей выбирать - Count
 *
 * @param int $Count - с какой записи начинать выборку
 * @param int $StartPosition - какое количество записей выбирать
 */
public function setLimit( $Count , $StartPosition = null );
/**
 * задает массив сортировки по полям, старые значения удаляются
 *
 * @param array $orderfields - массив полей, по которым сортируется - array( fieldname1 => "ASC | DESC", ... )
 */
public function setOrder( array $orderfields );
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
 * @param string $tablename - имя таблицы для поля, которое добавляется к условию
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
public function addWhereParam($tablename, $fieldname, $data, $operator = "=", $andor = "AND", $quote = TRMSqlDataSource::NEED_QUOTE, $alias = null, $dataquote = TRMSqlDataSource::NEED_QUOTE);
/**
 * добавляет условие в секцию WHERE-запроса
 * 
 * @param string $tablename - имя таблицы для поля, которое добавляется к условию
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
public function addWhereParamFromArray($tablename, array $params);
/**
 * добавляет массив параметров к уже установленному
 *
 * @param string $tablename - имя объекта для которого устанавливаются параметры
 * @param array - параметры, используемые в запросе, как правило сюда передается ID-записи 
 * все должно передаваться в массиве array( $fieldname => array(value, operator, andor, quote, alias, dataquote), ...)
 * обязательными являются array(..., $fieldname => array(value), ...)
 */
public function generateParamsFrom($tablename, array $params);
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
 * @return boolean - если обновление прошло успешно, то вернет true, иначе - false
 */
public function update(TRMSafetyFields $SafetyFields, TRMDataObjectsCollection $DataCollection);
/**
 * добавляет новую запись в БД, 
 * 
 * @param TRMSafetyFields $SafetyFields - DataMapper, для которого формируется выборка из БД
 * 
 * @return boolean - если обновление прошло успешно, то вернет true, иначе - false
 */
public function insert( TRMSafetyFields $SafetyFields, TRMDataObjectsCollection $DataCollection );
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
 * @return boolean - возвращает результат запроса DELETE
 */
public function delete(TRMSafetyFields $SafetyFields, TRMDataObjectsCollection $DataCollection);

} // TRMDataSourceInterface