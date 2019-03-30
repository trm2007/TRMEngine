<?php

namespace TRMEngine\Repository\Exeptions;

use TRMEngine\Exceptions\TRMException;

/**
 * выбрасывается, если репозиторий не смог получить объект
 */
class TRMRepositoryGetObjectException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошика при работе с репозиториями! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * выбрасывается, если в репозитории не установлена ссылка на объект данных
 */
class TRMRepositoryNoDataObjectException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Не установлен объект с данными в репозитории! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * выбрасывается, если репозиторий не знает объект данных такого типа
 */
class TRMRepositoryUnknowDataObjectClassException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Объект неизвестного класса передан в репозиторий! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}
