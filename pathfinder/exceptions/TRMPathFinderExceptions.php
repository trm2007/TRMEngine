<?php

namespace TRMEngine\PathFinder\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * ����� ����������, ������� ������ �������������, 
 * ���� �� ������ �������
 */
class TRMPathNotFoundedException extends TRMException
{
    public function __construct( $message = "", $code = 404, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ��������� �� ��������� ������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMPathNotFoundedException

/**
 * ����� ����������, ������� ������ ������������� ����������� �����, 
 * ���� �� ������ ���������� ��� �������� URI
 */
class TRMControllerNotFoundedException extends TRMException
{
    public function __construct( $message = "", $code = 404, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " �� ������ ������ (����� �����������)! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMControllerNotFoundedException


/**
 * ����� ����������, ������� ������ ������������� ����������� �����,
 * ���� �� ������ Action � ����������� ��� �������� URI
 */
class TRMActionNotFoundedException extends TRMException
{
    public function __construct( $ControllerName, $ActionName, $Param, $Code = 404 )
    {
        parent::__construct( "� ������ [" . $ControllerName . "] �� ������ ����� [" 
                            . $ActionName
                            . "]<br>   ��� ��������� [" 
                            . $Param .  "]<br>",
                $Code );
    }
} // TRMActionNotFoundedException
