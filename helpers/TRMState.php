<?php

namespace TRMEngine\Helpers;

/**
 * абстрактный класс для всех объектов, 
 * у которых может быть код и строка состяния, например во время ошибок
 *
 * @author Sergey Kolesnikov 2019-02-24 <trm@mail.ru>
 */
abstract class TRMState
{
    /**
     * @var int - код состяния
     */
    private $StateCode = 0;

    /**
     * @var string - строка (описание) состояния
     */
    private $StateString = "";

    /**
     * @return int - возвращает текущий код состяния
     */
    public function getStateCode()
    {
        return $this->StateCode;
    }

    /**
     * @return string - возвращает текущую строку (описание) состяния
     */
    public function getStateString()
    {
        return $this->StateString;
    }

    /**
     * @param int $StateCode - устанавливает код состяния
     */
    public function setStateCode($StateCode)
    {
        $this->StateCode = $StateCode;
    }

    /**
     * @param string $StateString - устанавливает текущую строку (описание) состяния
     */
    public function setStateString($StateString)
    {
        $this->StateString = $StateString;
    }

    /**
     * @param int $StateCode - добавляет код состяния к текущему путем логического сложения ИЛИ (|)
     */
    public function addStateCode($StateCode)
    {
        $this->StateCode |= $StateCode;
    }

    /**
     * @param string $StateString - добавляет строку (описание) состяния к уже имеющейся
     */
    public function addStateString($StateString)
    {
        $this->StateString .= PHP_EOL . $StateString;
    }

    /**
     * устанавливает код состояния в 0 и очищает строку состояния
     */
    public function clearState()
    {
        $this->StateCode = 0;
        $this->StateString = "";
    }
} // TRMState
