<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;
use TRMEngine\DataObject\TRMDataObject;
use TRMEngine\Exceptions\TRMObjectCreateException;
use TRMEngine\Repository\Exeptions\TRMRepositoryGetObjectException;

/**
 * класс дл€ работы с хранилищем коллекции зависимой от родительского объекта
 */
abstract class TRMParentedRelationCollectionRepository extends TRMRepository
{
/**
 * @var string - им€ таблицы определющей отношени€ многое-ко-многому, в данном классе она €вл€етс€ основной таблицей в SQL-запросе
 */
protected $RelationTableName;
/**
 * @var string - им€ пол€ родительского ID в св€зующей таблице,
 * в данной реализации это одна из зависимостей, играюща€ роль главной, 
 * дл€ которой выбираютс€ все записи коллекции именно с одним таким ID,
 * например, дл€ соотношени€ ( ID-товара-1 - [ID-товара-M, ID-характеристики-M] - ID-характеристики-1 )
 * такую роль играет ID-товара-M, дл€ одного товара выбираетс€ коллекци€ характеристик
 */
protected $ParentRelationIdFieldName;
/**
 * @var string - им€ пол€ из св€зующей-основной таблицы (определющее дочернее отношени€ многое-ко-многому), 
 * по которому будет установлена св€зь со второй таблицей
 */
protected $RelationIdFieldName;
/**
 * @var string - им€ таблицы с одной из зависимостей (таблица с коллекцией), 
 * в данном классе она €вл€етс€ присоедин€емой (дочерней) таблицей
 */
protected $TableName;
/**
 * @var string - им€ пол€ ID-зависимости дл€ объектов коллекции (присоедин€ема€-дочерн€€), 
 * в секции JOIN ... ON $IdFieldName = $RelationIdFieldName
 */
protected $IdFieldName;


public function __construct($objectclassname)
{
    if( empty($this->ParentRelationIdFieldName) )
    {
        throw new TRMObjectCreateException("¬ дочернем конструкторе не указано им€ пол€, содержащее значение родительского ID дл€ объектов ". get_class($this), 500);
    }
    parent::__construct($objectclassname);
}


/**
 * возвращает объект с коллекцией дл€ заданного родител€
 * 
 * @param TRMIdDataObjectInterface $parentobject - объект родител€, 
 * который будет установлен дл€ коллекции и дл€ которого будет выбрана из репозитори€ данна€ коллекци€
 * @return TRMDataObject
 */
public function getByParent( TRMIdDataObjectInterface $parentobject )
{
    //if( null !== $this->getBy( $parentobject->getIdFieldName(), $parentobject->getId() ) )
    try
    {
        $this->getBy( $this->ParentRelationIdFieldName, $parentobject->getId() );
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
 * дл€ коллекции сначала все удал€етс€ из Ѕƒ, 
 * затем снова записываютс€ данные из объекта данных
 *
 * @return boolean
 */
public function update()
{
    if( !$this->delete() ) { return false; }
    return $this->DataSource->insert();
}


} // TRMParentedRelationCollectionRepository