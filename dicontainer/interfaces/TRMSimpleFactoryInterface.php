<?php

namespace TRMEngine\DiContainer\Interfaces;

/**
 * Интерфейс фабрик для создания объектов орпеделенных типов,
 * для каждого типа объектов должна быть своя вабрика
 */
interface TRMSimpleFactoryInterface
{
  /**
   * Создает и возвращает новый объект
   * 
   * @param array $Params - массив параметров, с которыми будет создаваться объект
   * 
   * @return mixed - новый объект
   */
  public function create(array $Params = array());
}
