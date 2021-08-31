<?php

namespace TRMEngine\DiContainer\Interfaces;

/**
 * Интерфейс фабрики для создания объектов разных типов,
 */
interface TRMStaticFactoryInterface
{
  /**
   * Создает и возвращает новый объект
   * 
   * @param string $ClassName - имя класса(типа) создаваемого объекта
   * @param array $Params - массив параметров, с которыми будет создаваться объект
   * 
   * @return mixed - новый объект
   */
  public function create($ClassName, array $Params = array());
}
