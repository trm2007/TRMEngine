<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;
use TRMEngine\Exceptions\TRMObjectCreateException;

/**
 * ����� ��� ������ � ���������� ��������, ��������� �� ������������� �������
 */
abstract class TRMParentedDataObjectRepository extends TRMRepository
{
/**
 * @var array - ������ array( ��� ������, ��� ���� ) ������������� ID � ��������� �������,
 * � ������ ���������� ��� ���� �� ������������, �������� ���� �������, 
 * ��� ������� ���������� ��� ������ ��������� ������ � ����� ����� ID,
 * ��������, ��� ����������� ( ID-������-1 - [ID-������-M, ID-��������������-M] - ID-��������������-1 )
 * ����� ���� ������ ID-������-M, ��� ������ ������ ���������� ��������� �������������
 */
static protected $ParentRelationIdFieldName = array();


public function __construct($objectclassname)
{
    if( empty(static::$ParentRelationIdFieldName) )
    {
        throw new TRMObjectCreateException("� �������� ������� �� ������� ��� ����, ���������� �������� ������������� ID ��� �������� ". get_class($this), 500);
    }
    parent::__construct($objectclassname);
}

/**
 * @return array -  array( ��� ������������� �������, ��� ���� ��� ����� )
 */
public function getParentRelationIdFieldName()
{
    $type = $this->ObjectTypeName;
    return $type::getParentIdFieldName();
}
/**
 * @param array $ParentRelationIdFieldName - array( ��� ������������� �������, ��� ���� ��� ����� )
 */
//static public function setParentRelationIdFieldName(array $ParentRelationIdFieldName)
//{
//    static::$ParentRelationIdFieldName[0] = reset($ParentRelationIdFieldName);
//    static::$ParentRelationIdFieldName[1] = next($ParentRelationIdFieldName);
//    reset($ParentRelationIdFieldName);
//}

/**
 * ���������� ��������� ��������, ������� ������� �� ��������� ��������
 * 
 * @param TRMIdDataObjectInterface $parentobject - ������ ��������, 
 * ������� ����� ���������� ��� ��������� � ��� �������� ����� ������� �� ����������� ������ ���������
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