<?php

namespace TRMEngine\DataSource;

use TRMEngine\DataMapper\TRMSafetyFields;

/**
 * класс для получения и обработки одной записи по SQL-зпаросу из БД,
 * подключая значения из других таблиц со связью один-к-одному
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