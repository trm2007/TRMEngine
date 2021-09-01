<?php

namespace TRMEngine\DataArray\Interfaces;

interface InitializibleFromArray
{
  /**
   * перебирает массив $Array,
   * на основе каждого его элемента создает новый объет хранимого типа,
   * и вызывает у него так же функцию initializeFromArray,
   * добавляет вновь созданный объект в коллекцию
   * 
   * @param array $Array - массив с данными для инициализации элементов коллекции
   */
  public function initializeFromArray(array $Array);
}
