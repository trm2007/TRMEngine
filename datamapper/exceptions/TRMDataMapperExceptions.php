<?php

namespace TRMEngine\DataMapper\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * �������������, ���� ����������� ������ ��� ������ �� �����
 */
class TRMDataMapperNotStringFieldNameException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " � �������� ����� ���� ������������ �� ��������� ��������! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * �������������, ���� DataMapper �� ��������
 */
class TRMDataMapperEmptySafetyFieldsArrayException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " ������ SafetyFieldsArray - ������, "
                . "���������� ������� ������ ����� ������ ��� ����� ������� array( TableName => array(...), ... )" . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


/**
 * ������ �������������, ���� � DataMapper ������� ������ ����� ����� ��������,
 * ������ ���� ���� ������� ������, �� �������� ����� �� ���������
 */
class TRMDataMapperRelationException extends TRMException
{
    public function __construct( $message = "", $code = 0, Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " � ������� ������ ���������� ����������� �����! ��� ���������� �������� ��� ������ �� ���!" . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
} // TRMDataMapperRelationException