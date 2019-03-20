<?php

namespace TRMEngine\Helpers;

/**
 * ����������� ����� ��� ���� ��������, � �������� ����� ���� ��� � ������ ��������, �������� �� ����� ������
 *
 * @author TRM 2019-02-24
 */
abstract class TRMState
{
/**
 * @var int - ��� ��������
 */
private $StateCode = 0;

/**
 * @var string - ������ (��������) ���������
 */
private $StateString = "";

/**
 * @return int - ���������� ������� ��� ��������
 */
public function getStateCode() {
    return $this->StateCode;
}

/**
 * @return string - ���������� ������� ������ (��������) ��������
 */
public function getStateString() {
    return $this->StateString;
}

/**
 * @param int $StateCode - ������������� ��� ��������
 */
public function setStateCode($StateCode) {
    $this->StateCode = $StateCode;
}

/**
 * @param string $StateString - ������������� ������� ������ (��������) ��������
 */
public function setStateString($StateString) {
    $this->StateString = $StateString;
}

/**
 * @param int $StateCode - ��������� ��� �������� � �������� ����� ����������� �������� ��� (|)
 */
public function addStateCode($StateCode) {
    $this->StateCode |= $StateCode;
}

/**
 * @param string $StateString - ��������� ������ (��������) �������� � ��� ��������
 */
public function addStateString($StateString) {
    $this->StateString .= PHP_EOL . $StateString;
}

/**
 * ������������� ��� �������� � 0 � ������� ������ ���������
 */
public function clear()
{
    $this->StateCode = 0;
    $this->StateString = "";
}

} // TRMState
