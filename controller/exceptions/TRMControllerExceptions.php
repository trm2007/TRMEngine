<?php

namespace TRMEngine\Controller\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * ������������� ���� �� ������ Controller
 */
class TRMNoControllerException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ��� ����������� �� �������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMNoControllerException


/**
 * �������������, ���� �� ������ Action
 */
class TRMNoActionException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " �� ������� ��� Action! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMNoActionException


/**
 * ���������� �������������, ��� ������� �����������, ���� ������ ���� ������� ������ Action
 */
class TRMMustStartOtherActionException extends TRMException
{
    /**
     * @var string - ��� �������-Action, ������� ������ ���� ��������
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