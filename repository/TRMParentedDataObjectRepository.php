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
private $ParentRelationIdFieldName;
/**
 * @var array - ��� ���� �� ���������-�������� ������� (����������� �������� ��������� ������-��-�������), 
 * �� �������� ����� ����������� ����� �� ������ ��������
 */
protected $RelationIdFieldName;


public function __construct($objectclassname)
{
    if( empty($this->ParentRelationIdFieldName) )
    {
        throw new TRMObjectCreateException("� �������� ������������ �� ������� ��� ����, ���������� �������� ������������� ID ��� �������� ". get_class($this), 500);
    }
    parent::__construct($objectclassname);
}

/**
 * @return array -  array( ��� ������������� �������, ��� ���� ��� ����� )
 */
function getParentRelationIdFieldName()
{
    return $this->ParentRelationIdFieldName;
}
/**
 * @param array $ParentRelationIdFieldName - array( ��� ������������� �������, ��� ���� ��� ����� )
 */
function setParentRelationIdFieldName(array $ParentRelationIdFieldName)
{
    $this->ParentRelationIdFieldName[0] = reset($ParentRelationIdFieldName);
    $this->ParentRelationIdFieldName[1] = next($ParentRelationIdFieldName);
    reset($ParentRelationIdFieldName);
}

/**
 * ���������� ��������� ��������, ������� ������� �� ��������� ��������
 * 
 * @param TRMIdDataObjectInterface $parentobject - ������ ��������, 
 * ������� ����� ���������� ��� ��������� � ��� �������� ����� ������� �� ����������� ������ ���������
 * @return TRMDataObjectsCollectionInterface
 */
public function getByParent( TRMIdDataObjectInterface $ParentObject, TRMDataObjectsCollectionInterface $Collection = null )
{
    $ParentRelationIdFieldName = $this->getParentRelationIdFieldName();

    return $this->getBy(
            $ParentRelationIdFieldName[0], 
            $ParentRelationIdFieldName[1], 
            $ParentObject->getId(), 
            $Collection 
            );
}


} // TRMParentedDataObjectRepository