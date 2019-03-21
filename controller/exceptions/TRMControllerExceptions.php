<?php

namespace TRMEngine\Controller\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * выбрасываетс€ если не указан Controller
 */
class TRMNoControllerException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " »м€ контроллера не указано! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMNoControllerException


/**
 * выбрасываетс€, если не указан Action
 */
class TRMNoActionException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ќе указано им€ Action! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMNoActionException


/**
 * исключение выбрасываетс€, как правило кнтроллером, если должен быть запущен другой Action
 */
class TRMMustStartOtherActionException extends TRMException
{
    /**
     * @var string - им€ функции-Action, котора€ должна быть запушена
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