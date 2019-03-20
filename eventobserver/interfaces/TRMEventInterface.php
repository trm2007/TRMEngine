<?php

namespace TRMEngine\EventObserver\Interfaces;

/* 
 * ��������� �������� �������
 */
interface TRMEventInterface
{
/**
 * ����������� ��� �������
 * @param object $sender - ������ ������ ������������ � ��������� ������� �������, ��� ��� ������ - ��� ����������� ������
 * @param string $eventtype - ��� �������, ��� ���
 */
public function __construct($sender, $eventtype );
/**
 * ���������� ����������, ������� ������ ������ �������
 */
public function getSender();

/**
 * ���������� ��� �������
 */
public function getType();

/**
 * ������� ���������� ����� � ������� ������� ���������� ��� � ������, ���������� ���������� ��� ���� �������
 */
public function __toString();

} // TRMEventInterface