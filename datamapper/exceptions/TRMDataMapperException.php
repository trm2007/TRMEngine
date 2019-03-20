<?php

namespace TRMEngine\DataMapper\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * выбрасываетс€, если некорректно задано им€ одного их полей
 */
class TRMDataMapperNotStringFieldNameException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ¬ качестве имени пол€ используетс€ не строковое значение! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * выбрасываетс€, если DataMapper не заполнен
 */
class TRMDataMapperEmptySafetyFieldsArrayException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ћассив SafetyFieldsArray - пустой, "
                . "необходимо указать хот€бы имена таблиц как ключи массива array( TableName => array(...), ... )" . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}
