<?php

namespace TRMEngine\DataObject\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * ������������� ��� ������ � ������ � ��������� ������ TRMDataObject � �� ������������
 */
class TRMDataObjectException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ������ ������� ������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * ������������� ��� ������ � ������ � ����������� �������� ������ TRMDataObjectsContainer � �� ������������
 */
class TRMDataObjectContainerException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ������ ��� ������ � ����������� ������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

class TRMDataObjectContainerNoMainException extends TRMDataObjectContainerException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ���������� ���� Main! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

class TRMDataObjectsContainerWrongIndexException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ������ � ��������� �������� ������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * ������������� ��� ������ � ������ � ���������� �������� ������ TRMDataObjectsCollection � �� ������������
 */
class TRMDataObjectsCollectionException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ������ � ��������� �������� ������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

class TRMDataObjectsCollectionWrongIndexException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ������ � ��������� �������� ������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}

class TRMDataObjectsCollectionWrongTypeException extends TRMException
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " �������� ��� �������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}
