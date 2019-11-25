<?php

namespace TRMEngine\DataMapper;

use TRMEngine\DataMapper\Interfaces\TRMParentedDataMapperInterface;

/**
 * TRMParentedDataMapper описывает объекты, у которых есть родительский объект,
 * и они связаны с его ID-полес через $ParentIdFieldName
 *
 * @author TRM 2019-11-25
 */
class TRMParentedDataMapper extends TRMDataMapper implements TRMParentedDataMapperInterface
{
/**
 * @var array - массив = (имя объекта, имя свойства) содержащего Id родителя в коллекции,
 * должен определяться в каждом дочернем классе со своими именами
 */
protected $ParentIdFieldName = array();

/**
 * @return array - имя поля, ссылающееся (содержащее значение) Id родителя
 */
public function getParentIdFieldName()
{
    return $this->ParentIdFieldName;
}
/**
 * @param array $ParentIdFieldName - имя поля, ссылающееся (содержащее значение) Id родителя
 */
public function setParentIdFieldName(array $ParentIdFieldName)
{
    $this->ParentIdFieldName[0] = reset($ParentIdFieldName);
    $this->ParentIdFieldName[1] = next($ParentIdFieldName);
}


} // TRMParentedDataMapper
