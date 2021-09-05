<?php

namespace TRMEngine\EventObserver;

use TRMEngine\EventObserver\Interfaces\TRMEventInterface;

/**
 * Класс события
 */
class TRMCommonEvent implements TRMEventInterface
{
  /**
   * @var IObservable - инициатор события
   */
  protected $EventSender;
  /**
   * @var string - имя типа события
   */
  protected $EventName;
  /**
   * @var array - данные, необходимые для передачи в возникшем событии
   */
  public $Data;

  /**
   * 
   * @param object $Sender - объект инициатор события
   * @param string $EventName - имя генерируемого события
   * @param array $Data - данные, которые передаются вместе с объектом события
   */
  public function __construct($Sender, $EventName, $Data = array())
  {
    $this->EventSender = $Sender;
    $this->EventName = $EventName;
    $this->Data = $Data;
  }
  /**
   * @return object - возвращает инициатора события, кто его послал
   */
  public function getSender()
  {
    return $this->EventSender;
  }

  /**
   * @return string - возвращает имя события
   */
  public function getType()
  {
    // static:: - используем позднее связывание, будет возвращена $EventName определенная в каждом дочернем классе своя
    // в то время как self:: возвращает статичесоке значение только для данного TRMCommonEvent класса
    //return static::$EventName;
    //------------------------------
    return $this->EventName;
  }

  public function __toString()
  {
    return $this->EventName;
  }
} // TRMCommonEvent