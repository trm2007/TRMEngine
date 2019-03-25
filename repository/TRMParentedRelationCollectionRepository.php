<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;
use TRMEngine\DataObject\TRMDataObject;
use TRMEngine\Exceptions\TRMObjectCreateException;
use TRMEngine\Repository\Exeptions\TRMRepositoryGetObjectException;

/**
 * класс для работы с хранилищем коллекции зависимой от родительского объекта
 */
abstract class TRMParentedRelationCollectionRepository extends TRMRepository
{
/**
 * @var array - массив array( имя объект, имя поля ) родительского ID в связующей таблице,
 * в данной реализации это одна из зависимостей, играющая роль главной, 
 * для которой выбираются все записи коллекции именно с одним таким ID,
 * например, для соотношения ( ID-товара-1 - [ID-товара-M, ID-характеристики-M] - ID-характеристики-1 )
 * такую роль играет ID-товара-M, для одного товара выбирается коллекция характеристик
 */
private $ParentRelationIdFieldName;
/**
 * @var array - имя поля из связующей-основной таблицы (определющее дочернее отношения многое-ко-многому), 
 * по которому будет установлена связь со второй таблицей
 */
protected $RelationIdFieldName;


public function __construct($objectclassname)
{
    if( empty($this->ParentRelationIdFieldName) )
    {
        throw new TRMObjectCreateException("В дочернем конструкторе не указано имя поля, содержащее значение родительского ID для объектов ". get_class($this), 500);
    }
    parent::__construct($objectclassname);
}

/**
 * @return array -  array( имя родительского объекта, имя поля для связи )
 */
function getParentRelationIdFieldName()
{
    return $this->ParentRelationIdFieldName;
}
/**
 * @param array $ParentRelationIdFieldName - array( имя родительского объекта, имя поля для связи )
 */
function setParentRelationIdFieldName(array $ParentRelationIdFieldName)
{
    $this->ParentRelationIdFieldName[0] = reset($ParentRelationIdFieldName);
    $this->ParentRelationIdFieldName[1] = next($ParentRelationIdFieldName);
    reset($ParentRelationIdFieldName);
}


/**
 * возвращает объект с коллекцией для заданного родителя
 * 
 * @param TRMIdDataObjectInterface $parentobject - объект родителя, 
 * который будет установлен для коллекции и для которого будет выбрана из репозитория данная коллекция
 * @return TRMDataObject
 */
public function getByParent( TRMIdDataObjectInterface $parentobject )
{
    try
    {
        $ParentRelationIdFieldName = $this->getParentRelationIdFieldName();
        $this->getBy( $ParentRelationIdFieldName[0], $ParentRelationIdFieldName[1], $parentobject->getId() );
        $this->CurrentObject->setParentDataObject( $parentobject );

        return $this->CurrentObject;
    }
    catch( TRMRepositoryGetObjectException $e )
    {
        $this->CurrentObject = null;
        return null;
    }
}

/**
 * для коллекции сначала все удаляется из БД, 
 * затем снова записываются данные из объекта данных
 *
 * @return boolean
 */
public function update()
{
    if( !$this->delete() ) { return false; }
    return $this->DataSource->insert();
}


} // TRMParentedRelationCollectionRepository