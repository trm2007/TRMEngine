<?php

namespace TRMEngine\DataObject\Interfaces;

/**
 * интерфейс для объектов данных, у которых есть родитель (обычно в свойствах есть ссылка на объект родителя),
 * например, у объекта товара может быть ссылка на группу,
 * у коллекции изображений ссылка на товар, к которому он принадлежит и т.д...
 */
interface TRMParentedDataObjectInterface extends TRMIdDataObjectInterface
{
  /**
   * @return array - имя свойства внутри объекта содержащего Id родителя
   */
  static public function getParentIdFieldName();
  /**
   * @param array $ParentIdFieldName - имя свойства внутри объекта содержащего Id родителя
   */
  static public function setParentIdFieldName(array $ParentIdFieldName);
  /**
   * @return TRMIdDataObjectInterface - возвращает объект родителя
   */
  public function getParentDataObject();
  /**
   * @param TRMIdDataObjectInterface $ParentDataObject - устанавливает объект родителя, 
   */
  public function setParentDataObject(TRMIdDataObjectInterface $ParentDataObject);
}
