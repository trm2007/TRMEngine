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
 * @var string - ��� �������� ����������� Id �������� � ���������
 */
protected $ParentIdFieldName;
/**
 * @var TRMIdDataObjectInterface - ������ �� ������ �������� ��� ������ �� ������� ���������...
 */
protected $ParentDataObject = null;


abstract public function __construct();

/**
 * @return string - ��� �������� ����������� Id �������� � ���������
 */
function getParentIdFieldName()
{
    return $this->ParentIdFieldName;
}
/**
 * @param string $ParentIdFieldName - ��� �������� ����������� Id �������� � ���������
 */
function setParentIdFieldName($ParentIdFieldName)
{
    $this->ParentIdFieldName = $ParentIdFieldName;
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
        $this->changeAllValuesFor( $this->ParentIdFieldName, $this->ParentDataObject->getId() );
    }
    else
    {
        $this->changeAllValuesFor( $this->ParentIdFieldName, null );
    }    
}

} // TRMParentedCollectionDataObject