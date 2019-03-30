<?php

namespace TRMEngine\Repository\Exeptions;

use TRMEngine\Exceptions\TRMException;

/**
 * �������������, ���� ����������� �� ���� �������� ������
 */
class TRMRepositoryGetObjectException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ����� ��� ������ � �������������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * �������������, ���� � ����������� �� ����������� ������ �� ������ ������
 */
class TRMRepositoryNoDataObjectException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " �� ���������� ������ � ������� � �����������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * �������������, ���� ����������� �� ����� ������ ������ ������ ����
 */
class TRMRepositoryUnknowDataObjectClassException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ������ ������������ ������ ������� � �����������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}
