<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMParentedDataObjectInterface;

/**
 * ����� ��� ������ � ���������� �������� ������, � ������� ���� ID-��������
 * ���������� ������ ������������ �������� � ���� ���������� �������
 *
 * @author TRM
 */
abstract class TRMParentedCollectionDataObject extends TRMCollectionDataObject implements TRMParentedDataObjectInterface
{
/**
 * @var array - ������ = (��� �������, ��� ��������) ����������� Id �������� � ���������
 */
private $ParentIdFieldName;
/**
 * @var TRMIdDataObjectInterface - ������ �� ������ �������� ��� ������ �� ������� ���������...
 */
protected $ParentDataObject = null;


abstract public function __construct();

/**
 * @return array - ��� �������� ����������� Id �������� � ���������
 */
function getParentIdFieldName()
{
    return $this->ParentIdFieldName;
}
/**
 * @param array $ParentIdFieldName - ��� �������� ����������� Id �������� � ���������
 */
function setParentIdFieldName(array $ParentIdFieldName)
{
    $this->ParentIdFieldName[0] = reset($ParentIdFieldName);
    $this->ParentIdFieldName[1] = next($ParentIdFieldName);
    reset($ParentIdFieldName);
}

/**
 * @return TRMIdDataObjectInterface - ���������� ������ ��������
 */
function getParentDataObject()
{
    return $this->ParentDataObject;
}

/**
 * @param TRMIdDataObjectInterface $ParentDataObject - ������������� ������ ��������, 
 * ��� ���� �������� ��� ������������ Id � ���������
 */
function setParentDataObject(TRMIdDataObjectInterface $ParentDataObject)
{
    $this->ParentDataObject = $ParentDataObject;
    $this->changeParentIdForCurrentParent();
}

/**
 * ��������������� �������, ������ ��� �������� ���� ������������� ID ��� ��������� 
 * �� �������� ID �� ������������� �������, 
 * ������������ ������ � �������� �����������, ��������� ���� ��������� �� ������� �������, � ����� ��������.
 * ���� ������������ ������ ��� �� ����������, �� ��� �������� ������������� Id ����� ����������� � null
 */
private function changeParentIdForCurrentParent()
{
    if( $this->ParentDataObject )
    {
        $this->changeAllValuesFor( $this->ParentIdFieldName[0], $this->ParentIdFieldName[1], $this->ParentDataObject->getId() );
    }
    else
    {
        $this->changeAllValuesFor( $this->ParentIdFieldName[0], $this->ParentIdFieldName[1], null );
    }
}


} // TRMParentedCollectionDataObject