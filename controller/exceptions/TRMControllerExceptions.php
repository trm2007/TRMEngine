<?php

namespace TRMEngine\Controller\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * выбрасывается если не указан Controller
 */
class TRMNoControllerException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Имя контроллера не указано! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMNoControllerException


/**
 * выбрасывается, если не указан Action
 */
class TRMNoActionException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Не указано имя Action! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMNoActionException


/**
 * исключение выбрасывается, как правило кнтроллером, если должен быть запущен другой Action
 */
class TRMMustStartOtherActionException extends TRMException
{
    /**
     * @var string - имя функции-Action, которая должна быть запушена
     */
    protected $ActionName = "";

    public function __construct( $ActionName )
    {
        parent::__construct();
        $this->ActionName = $ActionName;
    }
    
    public function getActionName()
    {
        return $this->ActionName;
    }
} // TRMMustStartOtherActionException