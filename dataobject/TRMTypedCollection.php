<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Exceptions\TRMDataObjectsCollectionWrongIndexException;
use TRMEngine\DataObject\Exceptions\TRMDataObjectsCollectionWrongTypeException;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\Repository\Exceptions\TRMRepositoryUnknowDataObjectClassException;

/**
 * ����� ��� ������ � ����������� ���������� �������� DataObject
 * 
 * @version 2019-03-29
 */
class TRMTypedCollection extends TRMDataObjectsCollection
{
/**
 *
 * @var string - ��� ����������� �������� � ������ ���������
 */
protected $ObjectsType;


public function __construct($ObjectsType)
{
    if( !class_exists($ObjectsType) )
    {
        throw new TRMRepositoryUnknowDataObjectClassException( $objectclassname );
    }
    $this->ObjectsType = $ObjectsType;
}

/**
 * @return string - ��� ����������� �������� � ������ ���������
 */
public function getObjectsType()
{
    return $this->ObjectsType;
}

/**
 * ��������� ������������ ���� ������� �������������� ��� ���������
 * 
 * @param TRMDataObjectInterface $DataObject - ����������� ������
 * 
 * @throws TRMDataObjectsCollectionWrongTypeException
 */
public function validateObject(TRMDataObjectInterface $DataObject)
{
    if( get_class($DataObject) !== $this->ObjectsType )
    {
        throw new TRMDataObjectsCollectionWrongTypeException( get_class($this) . "-" . get_class($DataObject) );
    }
}

/**
 * @param int $Index - ������������� ������ ������� � ��������� ��������
 * @param TRMDataObjectInterface $DataObject - ������ ��� ��������� � ���������
 * 
 * @throws TRMDataObjectsCollectionWrongIndexException
 */
public function setDataObject($Index, TRMDataObjectInterface $DataObject)
{
    $this->validateObject($DataObject);
    parent::setDataObject($Index, $DataObject);
}

/**
 * @param TRMDataObjectInterface $DataObject - ������� ��� ������ � ���������
 * @param bool $AddDuplicateFlag - ���� ���� ���� ���������� � false, �� � ��������� �� ��������� ��������� ��������,
 * ���� ���������� � TRUE, �� ������ ��������� ��� �����,
 * ���� ���� �� ��������� ��� �������������,
 * �� ��������� - false (����� �� �����������)
 * 
 * @return boolean - ���� ������ �������� � ���������, �� �������� TRUE, ����� FALSE
 */
public function addDataObject( TRMDataObjectInterface $DataObject, $AddDuplicateFlag = false )
{
    $this->validateObject($DataObject);
    return parent::addDataObject($DataObject, $AddDuplicateFlag);
}

/**
 * ��������� � ��������� ���������� ������ ���������,
 * ���� ������ ������ ������ ��� ��� � ����� �������,
 * ������ �� ������ �������, � ������ �� ���� �� ���� �������
 * 
 * @param TRMDataObjectsCollection $Collection
 * @param bool $AddDuplicateFlag - ���� ���� ���� ���������� � false, �� � ��������� �� ��������� ��������� ��������,
 * ���� ���������� � TRUE, �� ����� ��������� ��������� ��� ���� � ������������, �� ����� ����������,
 * ���� ���� ��� ��������� ��� �������������, �� ��������� - false (����� �� �����������)
 */
public function mergeCollection(TRMDataObjectsCollectionInterface $Collection, $AddDuplicateFlag = false )
{
    if( $Collection->ObjectsType !== $this->ObjectsType )
    {
        throw new TRMDataObjectsCollectionWrongTypeException( get_class($this) . "-" . get_class($Collection->ObjectsType) );
    }
    parent::mergeCollection($Collection, $AddDuplicateFlag);
}


} // TRMDataObjectsCollection
