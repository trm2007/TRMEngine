<?php

namespace TRMEngine\DataSource;

use TRMEngine\DataMapper\TRMSafetyFields;

/**
 * класс для получения и обработки одной записи из таблицы БД - TableName,
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

    /*
public function __construct()//$MainTableName, $MainIdName, array $SecondTableArray = null, $MainAlias = null )
{
    parent::__construct($MainTableName, $MainIdName, $SecondTableArray, $MainAlias);

    $this->StartPosition = null;
    $this->Count = 1;
}
 * 
 */


/**
 * добавляем новую запись в БД
 *
 * @return int|boolean - если все прошло удачно, то для одной записи возвращается ее номер авто-инкремента, иначе 0, 
 * в случае ошибки - false, результат работы следует проверять через === false, чтобы не путать с нулем!
 */
function add()
{
    if( false === parent::add() )
    {
            return false;
    }
    return $this->LastId;
}

} // TRMSqlOneToOneDataSource