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
abstract class TRMParentedDataObject extends TRMDataObject implements TRMParentedDataObjectInterface
{
/**
 * @var array - ������ = (��� �������, ��� ��������) ����������� Id �������� � ���������,
 * ������ ������������ � ������ �������� ������ �� ������ �������
 */
// static protected $ParentIdFieldName;
/**
 * @var TRMIdDataObjectInterface - ������ �� ������ �������� ��� ������ �� ������� ���������...
 */
protected $ParentDataObject = null;


/**
 * @return array - ��� �������� ����������� Id �������� � ���������
 */
static public function getParentIdFieldName()
{
    return static::$ParentIdFieldName;
}
/**
 * @param array $ParentIdFieldName - ��� �������� ����������� Id �������� � ���������
 */
static public function setParentIdFieldName(array $ParentIdFieldName)
{
    static::$ParentIdFieldName[0] = reset($ParentIdFieldName);
    static::$ParentIdFieldName[1] = next($ParentIdFieldName);
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