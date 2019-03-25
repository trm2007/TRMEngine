<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;
use TRMEngine\DataObject\TRMDataObject;
use TRMEngine\Exceptions\TRMObjectCreateException;
use TRMEngine\Repository\Exeptions\TRMRepositoryGetObjectException;

/**
 * ����� ��� ������ � ���������� ��������� ��������� �� ������������� �������
 */
abstract class TRMParentedRelationCollectionRepository extends TRMRepository
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
 * ���������� ������ � ���������� ��� ��������� ��������
 * 
 * @param TRMIdDataObjectInterface $parentobject - ������ ��������, 
 * ������� ����� ���������� ��� ��������� � ��� �������� ����� ������� �� ����������� ������ ���������
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
 * ��� ��������� ������� ��� ��������� �� ��, 
 * ����� ����� ������������ ������ �� ������� ������
 *
 * @return boolean
 */
public function update()
{
    if( !$this->delete() ) { return false; }
    return $this->DataSource->insert();
}


} // TRMParentedRelationCollectionRepository