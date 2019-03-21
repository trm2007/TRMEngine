<?php

namespace TRMEngine\DataMapper\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * выбрасывается, если некорректно задано имя одного их полей
 */
class TRMDataMapperNotStringFieldNameException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " В качестве имени поля используется не строковое значение! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * выбрасывается, если DataMapper не заполнен
 */
class TRMDataMapperEmptySafetyFieldsArrayException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
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
class TRMDataMapperRelationException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " В объекте данных обнаружены циклические связи! Нет внутренних объектов без ссылок на них!" . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMDataMapperRelationException