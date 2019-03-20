<?php

namespace TRMEngine\DataSource\Exceptions;

use TRMEngine\Exceptions\TRMSqlQueryException;

/**
 * ����� ����������, ������� ������ �������������, 
 * ���� �������� ��������� ����� INSERT � SQL-�������
 */
class TRMDataSourceSQLInsertException extends TRMSqlQueryException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . "  �� ������� �������� ������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * ����� ����������, ������� ������ �������������, 
 * ���� � SQL-������� ��� ������, �� ������� ����� ����� �������,
 * ��� ����� ���� ������ ����� ������ DataMapper
 */
class TRMDataSourceSQLEmptyTablesListException extends TRMSqlQueryException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . "  ������ ������ ������ ��� ������� SELECT! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * ����� ����������, ������� ������ �������������, 
 * ���� � ������ SQLDataSource �� ������� DataMapper = SafetyFields
 */
class TRMDataSourceSQLNoSafetyFieldsException extends TRMSqlQueryException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . "  �� ���������� ������ SafetyFields! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * ����� ����������, ������� ������ �������������, 
 * ���� �� ������� ������������� ������� � DataMapper, � ����������� � �������������,
 * ������ ����� �� ��� �� ��������� �����������
 */
class TRMDataSourceWrongTableSortException extends TRMSqlQueryException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . "  ������������� ������ � ��������� �� �������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * ����� ����������, ������� ������ �������������, 
 * ���� � ������� ������� � ���������� 
 * �� ������� �������� ��������� ��� ���������� ���� �� DataMapper
 */
class TRMDataSourceNoUpdatebleFieldsException extends TRMSqlQueryException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . "  ��� ����� ��� ����������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}
