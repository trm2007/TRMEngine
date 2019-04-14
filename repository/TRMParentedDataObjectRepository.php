<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;
use TRMEngine\Exceptions\TRMObjectCreateException;

/**
 * класс для работы с хранилищем объектов, зависимых от родительского объекта
 */
abstract class TRMParentedDataObjectRepository extends TRMRepository
{
/**
 * @var array - массив array( имя объект, имя поля ) родительского ID в связующей таблице,
 * в данной реализации это одна из зависимостей, играющая роль главной, 
 * для которой выбираются все записи коллекции именно с одним таким ID,
 * например, для соотношения ( ID-товара-1 - [ID-товара-M, ID-характеристики-M] - ID-характеристики-1 )
 * такую роль играет ID-товара-M, для одного товара выбирается коллекция характеристик
 */
static protected $ParentRelationIdFieldName = array();


/**
 * @return array -  array( имя родительского объекта, имя поля для связи )
 */
public function getParentRelationIdFieldName()
{
    $type = $this->ObjectTypeName;
    return $type::getParentIdFieldName();
}

/**
 * возвращает коллекцию объектов, которые зависят от заданного родителя
 * 
 * @param TRMIdDataObjectInterface $parentobject - объект родителя, 
 * который будет установлен для коллекции и для которого будет выбрана из репозитория данная коллекция
 * @return TRMDataObjectsCollectionInterface
 */
public function getByParent( TRMIdDataObjectInterface $ParentObject, TRMDataObjectsCollectionInterface $Collection = null )
{
    $ParentRelationIdFieldName = static::getParentRelationIdFieldName();

    return $this->getBy(
            $ParentRelationIdFieldName[0], 
            $ParentRelationIdFieldName[1], 
            $ParentObject->getId(), 
            $Collection 
            );
}


} // TRMParentedDataObjectRepository