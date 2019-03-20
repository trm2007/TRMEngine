<?php

namespace TRMEngine\EventObserver;

use TRMEngine\EventObserver\Interfaces\TRMEventInterface;

/**
 * ����� �������
 */
class TRMCommonEvent implements TRMEventInterface
{
/**
 * @var IObservable - ��������� �������
 */
protected $EventSender;
/**
 * @var string - ��� ���� �������
 */
protected $EventName;
/**
 * @var array - ������, ����������� ��� �������� � ��������� �������
 */
public $Data;

/**
 * 
 * @param object $Sender - ������ ��������� �������
 * @param string $EventName - ��� ������������� �������
 * @param array $Data - ������, ������� ���������� ������ � �������� �������
 */
public function __construct( $Sender, $EventName, $Data = array() )
{
    $this->EventSender = $Sender;
    $this->EventName = $EventName;
    $this->Data = $Data;
}
/**
 * @return object - ���������� ���������� �������, ��� ��� ������
 */
public function getSender()
{
    return $this->EventSender;
}

/**
 * @return string - ���������� ��� �������
 */
public function getType()
{
    // static:: - ���������� ������� ����������, ����� ���������� $EventName ������������ � ������ �������� ������ ����
    // � �� ����� ��� self:: ���������� ����������� �������� ������ ��� ������� TRMCommonEvent ������
    //return static::$EventName;
    //------------------------------
    return $this->EventName;
}

public function __toString()
{
    return $this->EventName;
}


} // TRMCommonEvent