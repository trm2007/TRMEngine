<?php

namespace TRMEngine\DataObject\Interfaces;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;

interface TRMTypedCollectionInterface extends TRMDataObjectsCollectionInterface
{
  /**
   * @return string - тип сохраняемых объектов в коллекциях данного типа
   */
  public function getObjectsType();

  /**
   * проверяет соответствие типа объекта установленному для коллекции
   * 
   * @param TRMDataObjectInterface $DataObject - проверяемый объект
   * 
   * @throws TRMDataObjectSCollectionWrongTypeException
   */
  public function validateObject(TRMDataObjectInterface $DataObject);
} // TRMTypedCollectionInterface
