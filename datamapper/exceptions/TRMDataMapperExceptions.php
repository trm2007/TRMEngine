<?php

namespace TRMEngine\DataMapper\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * родительский класс исключений для DataMapper
 */
class TRMDataMapperExceptions extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка в DataMapper! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * выбрасывается, если некорректно задано имя одного их полей
 */
class TRMDataMapperNotStringFieldNameException extends TRMDataMapperExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " В качестве имени поля используется не строковое значение! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * выбрасывается, если DataMapper не заполнен
 */
class TRMDataMapperEmptySafetyFieldsArrayException extends TRMDataMapperExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Массив SafetyFieldsArray - пустой, "
                . "необходимо указать хотябы имена таблиц как ключи массива array( TableName => array(...), ... )" . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * должно выбрасываться, если в DataMapper неверно заданы связи между объектам,
 * должен быть один главный объект, на которого никто не ссылается
 */
class TRMDataMapperRelationException extends TRMDataMapperExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " В объекте данных обнаружены циклические связи! Нет внутренних объектов без ссылок на них!" . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMDataMapperRelationException


/**
 * выбрасывается если не удалось получить информацию  оглавном объекте
 */
class TRMDataMapperEmptyMainObjectException extends TRMDataMapperExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Не удалось определить основной объект для выборки! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * выбрасывается если главных объектов много и выбрать из них один не удается
 */
class TRMDataMapperTooManyMainObjectException extends TRMDataMapperExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Объектов много и выбрать из них главный не удается! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * выбрасывается если не удалось получить имя ID-поля главного объекта
 */
class TRMDataMapperEmptyIdFieldException extends TRMDataMapperExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Не удалось определить имя поля содержащее Id! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * выбрасывается если не удалось получить имя ID-поля главного объекта
 */
class TRMDataMapperEmptyParentIdFieldException extends TRMDataMapperExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Не удалось определить имя поля содержащее родительский Id! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}