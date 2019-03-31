<?php

namespace TRMEngine\DataSource;

use TRMEngine\DataMapper\TRMSafetyFields;

/**
 * класс для получения и обработки одной записи по SQL-зпаросу из БД,
 * подключая значения из других таблиц со связью один-к-одному
 */
class TRMSqlOneToOneDataSource extends TRMSqlDataSource
{

/**
 * @param \mysqli $MySQLiObject - драйвер для работы с MySQL
 */
public function __construct( \mysqli $MySQLiObject ) //$MainTableName, array $MainIndexFields, array $SecondTablesArray = null, $MainAlias = null )
{
    parent::__construct($MySQLiObject);
    $this->StartPosition = null;
    $this->Count = 1;
}

} // TRMSqlOneToOneDataSource