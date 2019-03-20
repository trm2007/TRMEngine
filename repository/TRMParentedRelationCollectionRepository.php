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
 * @var string - ��� ������� ����������� ��������� ������-��-�������, � ������ ������ ��� �������� �������� �������� � SQL-�������
 */
protected $RelationTableName;
/**
 * @var string - ��� ���� ������������� ID � ��������� �������,
 * � ������ ���������� ��� ���� �� ������������, �������� ���� �������, 
 * ��� ������� ���������� ��� ������ ��������� ������ � ����� ����� ID,
 * ��������, ��� ����������� ( ID-������-1 - [ID-������-M, ID-��������������-M] - ID-��������������-1 )
 * ����� ���� ������ ID-������-M, ��� ������ ������ ���������� ��������� �������������
 */
protected $ParentRelationIdFieldName;
/**
 * @var string - ��� ���� �� ���������-�������� ������� (����������� �������� ��������� ������-��-�������), 
 * �� �������� ����� ����������� ����� �� ������ ��������
 */
protected $RelationIdFieldName;
/**
 * @var string - ��� ������� � ����� �� ������������ (������� � ����������), 
 * � ������ ������ ��� �������� �������������� (��������) ��������
 */
protected $TableName;
/**
 * @var string - ��� ���� ID-����������� ��� �������� ��������� (��������������-��������), 
 * � ������ JOIN ... ON $IdFieldName = $RelationIdFieldName
 */
protected $IdFieldName;


public function __construct($objectclassname)
{
    if( empty($this->ParentRelationIdFieldName) )
    {
        throw new TRMObjectCreateException("� �������� ������������ �� ������� ��� ����, ���������� �������� ������������� ID ��� �������� ". get_class($this), 500);
    }
    parent::__construct($objectclassname);
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