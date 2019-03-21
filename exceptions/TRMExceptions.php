<?php

namespace TRMEngine\Exceptions;

/**
 * ����� �����, �� �������� ����������� ��� ���������� � TRMEngine
 */
class TRMException extends \Exception
{
    /**
     * @param string $message
     * @param int $code
     * @param \Throwable $previous
     */
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ������ ��� ���������� � ���� TRMEngine! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMException


/**
 * ������������� �������������, ���� ������ �� ����� ���� ������
 */
class TRMObjectCreateException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ������ �� ����� ���� ������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMObjectCreateException


/**
 * ������������� ��� ������ SQL-�������
 */
class TRMSqlQueryException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ������ SQL-�������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

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
