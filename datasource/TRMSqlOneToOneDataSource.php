<?php

namespace TRMEngine\DataSource;

use TRMEngine\DataMapper\TRMSafetyFields;

/**
 * ����� ��� ��������� � ��������� ����� ������ �� SQL-������� �� ��,
 * ��������� �������� �� ������ ������ �� ������ ����-�-������
 */
class TRMSqlOneToOneDataSource extends TRMSqlDataSource
{

/**
 * @param \mysqli $MySQLiObject - ������� ��� ������ � MySQL
 */
public function __construct( \mysqli $MySQLiObject ) //$MainTableName, array $MainIndexFields, array $SecondTablesArray = null, $MainAlias = null )
{
    parent::__construct($MySQLiObject);
    $this->StartPosition = null;
    $this->Count = 1;
}

} // TRMSqlOneToOneDataSource