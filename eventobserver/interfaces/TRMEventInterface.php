<?php

namespace TRMEngine\EventObserver\Interfaces;

/* 
 * интерфейс простого события
 */
interface TRMEventInterface
{
/**
 * конструктор для события
 * @param object $sender - всегда должен передаваться с родителем данного события, кто его создал - это наблюдаемый объект
 * @param string $eventtype - тип события, его имя
 */
public function __construct($sender, $eventtype );
/**
 * возвращает инициатора, который создал данное событие
 */
public function getSender();

/**
 * возвращает тип события
 */
public function getType();

/**
 * функция вызывается когда к объекту события обращаются как к строке, желательно возвращать имя типа события
 */
public function __toString();

} // TRMEventInterface