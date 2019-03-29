<?php

namespace TRMEngine\DataSource;

use TRMEngine\DataMapper\TRMSafetyFields;

/**
 * ����� ��� ��������� � ��������� ����� ������ �� ������� �� - TableName,
 * ��������� �������� �� ������ ������ �� ������ ����-�-������
 */
class TRMSqlOneToOneDataSource extends TRMSqlDataSource
{

public function __construct(TRMSafetyFields $SafetyFields)
{
    parent::__construct($SafetyFields);
    $this->StartPosition = null;
    $this->Count = 1;
}

} // TRMSqlOneToOneDataSource