<?php

namespace TRMEngine\DataSource\Exceptions;

use TRMEngine\Exceptions\TRMSqlQueryException;

/**
 * Класс исключения, которое должно выбрасываться, 
 * если неудачно отработал метод INSERT в SQL-запросе
 */
class TRMDataSourceSQLInsertException extends TRMSqlQueryException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . "  Не удалось добавить запись! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Класс исключения, которое должно выбрасываться, 
 * если в SQL-запросе нет таблиц, из которых нужно длеть выборку,
 * это может быть просто путой объект DataMapper
 */
class TRMDataSourceSQLEmptyTablesListException extends TRMSqlQueryException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . "  Пустой список таблиц для запроса SELECT! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * Класс исключения, которое должно выбрасываться, 
 * если в объект SQLDataSource не передан DataMapper = SafetyFields
 */
class TRMDataSourceSQLNoSafetyFieldsException extends TRMSqlQueryException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . "  Не установлен объект SafetyFields! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * Класс исключения, которое должно выбрасываться, 
 * если не удалось отсортировать объекты в DataMapper, в соответсвии с зависимостями,
 * скорее всего он был не правильно сформирован
 */
class TRMDataSourceWrongTableSortException extends TRMSqlQueryException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . "  Отсортировать массив с таблицами не удалось! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * Класс исключения, которое должно выбрасываться, 
 * если в методах вставки и обновления 
 * не удалось получить доступные для обновления поля из DataMapper
 */
class TRMDataSourceNoUpdatebleFieldsException extends TRMSqlQueryException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . "  Нет полей для обновления! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}
