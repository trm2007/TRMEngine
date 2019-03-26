<?php

namespace TRMEngine\EMail\Exceptions;


/**
 * ����� ����� ���������� ������������� �������� TRMEmail
 */
class TRMEMailExceptions extends \TRMEngine\Exceptions\TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ������ ��� ������ � �������� �����! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * ������ ������������� ��� ����� ��������
 */
class TRMEMailSendingExceptions extends \TRMEngine\EMail\Exceptions\TRMEMailExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ������ �������� �����! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * ������ ������������� ���� ������� ������ ���������� ������, ��� ����� ����������
 */
class TRMEMailWrongRecepientExceptions extends \TRMEngine\EMail\Exceptions\TRMEMailExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ������� ������ ��� ���������� ����������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * ������ ������������� ���� ������� ������� ���� ������, ��� ����� ����������
 */
class TRMEMailWrongThemeExceptions extends \TRMEngine\EMail\Exceptions\TRMEMailExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ������� ������� ��� ���������� ���� ������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

/**
 * ������ ������������� ���� �� ��������� ��� ������������ ���� ���������� ������
 */
class TRMEMailWrongBodyExceptions extends \TRMEngine\EMail\Exceptions\TRMEMailExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " �� ��������� ��� ������������ ���������� ������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}
