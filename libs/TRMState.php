<?php

namespace TRMEngine\Helpers;

/**
 * абстрактный класс дл€ всех объектов, у котороых может быть код и строка сост€ни€, например во врем€ ошибок
 *
 * @author TRM 2019-02-24
 */
abstract class TRMState
{
/**
 * @var int - код сост€ни€
 */
private $StateCode = 0;

/**
 * @var string - строка (описание) состо€ни€
 */
private $StateString = "";

/**
 * @return int - возвращает текущий код сост€ни€
 */
public function getStateCode() {
    return $this->StateCode;
}

/**
 * @return string - возвращает текущую строку (описание) сост€ни€
 */
public function getStateString() {
    return $this->StateString;
}

/**
 * @param int $StateCode - устанавливает код сост€ни€
 */
public function setStateCode($StateCode) {
    $this->StateCode = $StateCode;
}

/**
 * @param string $StateString - устанавливает текущую строку (описание) сост€ни€
 */
public function setStateString($StateString) {
    $this->StateString = $StateString;
}

/**
 * @param int $StateCode - добавл€ет код сост€ни€ к текущему путем логического сложени€ »Ћ» (|)
 */
public function addStateCode($StateCode) {
    $this->StateCode |= $StateCode;
}

/**
 * @param string $StateString - добавл€ет строку (описание) сост€ни€ к уже имющейс€
 */
public function addStateString($StateString) {
    $this->StateString .= PHP_EOL . $StateString;
}

/**
 * устанавливает код сост€ни€ в 0 и очищает строку состо€ни€
 */
public function clear()
{
    $this->StateCode = 0;
    $this->StateString = "";
}

} // TRMState
