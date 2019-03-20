<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;

/**
 * ����� ��� ������ � ��������� ������, 
 * �������� � ���������� ������� �� JSON, �������� � ��.,
 * �� �� �� ����������� ���������, 
 * ��� ����� ����� ������������ �������������� ������� Repository
 *
 * @author TRM 2018-07-29
 */
class TRMDataObjectService
{
const DefaultDataObjectType = TRMDataObject::class; //"TRMDataObject";
/**
 * @var TRMDataObjectInterface - ������ �� ������ ������, � ������� � ���������� ����� �������� ������
 */    
protected $CurrentObject = null;
/**
 * @return TRMDataObjectInterface - ���������� ������ �� ������ ������, 
 * � ������� � ���������� ����� �������� ������
 */
function getCurrentObject()
{
    return $this->CurrentObject;
}

/**
 * @param TRMDataObjectInterface $CurrentObject - ������������� ���������� ������ �� ������ ������, 
 * � ������� � ���������� ����� ����� �������� ������
 */
function setCurrentObject(TRMDataObjectInterface $CurrentObject)
{
    $this->CurrentObject = $CurrentObject;
}

/**
 * ������� ������ ������, 
 * ���� �� ������ ���, 
 * �� ��������� ������ ������ ��� ���� �������� ������ ������ - TRMDataObject,
 * ����������� ������������ �� �������� ������ TRMDataObject,
 * ���� ������������� ��� �� �������� ����������, �� ������ ������ �� �����
 * 
 * @param string $type - ��� ���� ������������ �������
 */
public function createDataObject($type = self::DefaultDataObjectType)
{
    if( $type === self::DefaultDataObjectType )
    {
        $DefaultType = self::DefaultDataObjectType;
        return new $DefaultType; // self::DefaultDataObjectType; //
    }
    if( class_exists($type) )
    {
        $ParentArray = class_parents($type);
        if( in_array(self::DefaultDataObjectType, $ParentArray) ) { return new $type; }
    }
    return null;
}

/**
 * ���������� JSON-������ � ������������� ������ (2-� �������� json_decode � true)
 * � ������������� ������ ����� ������� � ������ ������
 * 
 * @param string $json - JSON-������ ��� ��������� �� ���� ������ �������
 * 
 * @return TRMDataObjectInterface
 */
public function setDataObjectFromJSON($json)
{
    // ���� �� ����� ������ ������, �� ������� ������� ������ ���� TRMDataObject
    if( !isset( $this->CurrentObject ) )
    {
        $this->createDataObject();
    }
    // 2-� �������� json_decode � true ��� ��������� �������������� �������, 
    // � ��������� ������ ��������� ��������� stdClass � ������������ ����������
    $this->CurrentObject->setOwnData( json_decode($json, true) );
    return $this->CurrentObject;
}

/**
 * ��������� JSON-������ �� ������ �������
 * 
 * @param TRMDataObjectInterface $do - ������ ������, �� �������� ����� �������� JSON
 * @return string - ���������� ������ JSON
 */
public function getJSONFromDataObject(TRMDataObjectInterface $do)
{
    return json_encode($do->getOwnData());
}


} // TRMDataObjectService
