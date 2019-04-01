<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Exceptions\TRMDataObjectSCollectionWrongIndexException;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;

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
 * @throws TRMDataObjectSCollectionWrongTypeException
 */
public function validateObject(TRMDataObjectInterface $DataObject)
{
    if( get_class($DataObject) !== static::$ObjectsType )
    {
        throw new TRMDataObjectSCollectionWrongTypeException( get_class($this) . "-" . get_class($DataObject) );
    }
}

/**
 * @param int $Index - ������������� ������ ������� � ��������� ��������
 * @param TRMDataObjectInterface $DataObject - ������ ��� ��������� � ���������
 * 
 * @throws TRMDataObjectSCollectionWrongIndexException
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
    return parent::addDataObject($Index, $DataObject);
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
    if( $Collection::$ObjectsType !== static::$ObjectsType )
    {
        throw new TRMDataObjectSCollectionWrongTypeException( get_class($this) . "-" . get_class($Collection::$ObjectsType) );
    }
    parent::mergeCollection($Collection, $AddDuplicateFlag);
}


} // TRMDataObjectsCollection
