<?php

namespace TRMEngine\DataMapper\Interfaces;

interface TRMParentedDataMapperInterface extends TRMIdDataMapperInterface
{
  /**
   * @return array - имя поля, ссылающееся (содержащее значение) Id родителя
   */
  public function getParentIdFieldName();
  /**
   * @param array $ParentIdFieldName - имя поля, ссылающееся (содержащее значение) Id родителя
   */
  public function setParentIdFieldName(array $ParentIdFieldName);
}