<?php

namespace TRMEngine\DiContainer\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * ������ ������������� � ����������,
 * ���� � ���������� �� ������ ������������� �����
 */
class TRMDiClassNotFoundedException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ����� �� ������, ��� �� ������� ������ � DI-����������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMDiClassNotFoundedException


/**
 * ������ ������������� � ����������,
 * ���� � ������� �� ��������� ����������� � ��� ������ ��������� ������� ��� ��������
 */
class TRMDiNotPublicConstructorException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " � ������ ��� ���������� ������������, ������� �������� ������� ��� �� ����������!! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMDiNotPublicConstructorException


/**
 * ������ ������������� � ����������,
 * ���� ������ �� ������� ������� ������-���� ���������
 */
class TRMDiCanNotCreateObjectException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . "� ������ ��� ������������, ������� �������� � ���������� new ������� ��� �� ����������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMDiCanNotCreateObjectException


/**
 * ������ ������������� � ����������,
 * ���� � ����� ������� (__constructor, hasInstance, ��� ������ ��� ��������)
 * ������ ����������� �����-�� ���������, �� ��� �������� �� ���������
 */
class TRMDiNoDefaultArgsException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL ." � ���������� �� ������ ����� � �������� �� ��������� ��� ��������� � ������ �������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMDiCanNotCreateObjectException

