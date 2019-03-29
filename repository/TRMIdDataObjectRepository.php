<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;
use TRMEngine\Repository\Interfaces\TRMIdDataObjectRepositoryInterface;

/**
 * ����� �����������, ���������������� ��� ������ � ��������-������ ����������� TRMIdDataObjectInterface
 * �.�. � ��������� � ������� ���� ���������� ������������� - ID,
 * ��� ����� �������� ���� ����������� ������� ��������� ������-���������, � ������� �������� ������� � Id,
 * � ��� �� ���� ���������� ����� ������������� � ����� ����������!!!
 * getById - ������ ������ ������ �� ���� � ��� �� ������, ������� ��� ����������� ����� ������� ������ � ����� �����������..
 * 
 * @author TRM - 2018-07-28
 */
abstract class TRMIdDataObjectRepository extends TRMRepository implements TRMIdDataObjectRepositoryInterface
{
/**
 * @var array - ��� ����, ����������� ID ������
 */
protected $IdFieldName;
/**
 * @var string - ��� �������, � ������� ���� ����, ���������� ID ������
 */
protected $IdObjectName;

/**
 * @var array(TRMIdDataObjectInterface) - ������ ��������, ���������� � ����������� ����� ������ �����������, 
 * ������ �� ��� ������� �������� � ���� �������, 
 * � ��� ������� ��� ���������� �� �� (��� ������� ���������) ������� �� �������� �� �������
 */
protected static $IdDataObjectContainer = array();


public function __construct($objectclassname)
{
    parent::__construct($objectclassname);
    if( !isset(self::$IdDataObjectContainer[$objectclassname]) )
    {
        self::$IdDataObjectContainer[$objectclassname] = array();
    }
}

/**
 * @return array - array(��� ���-�������, ��� ����) ��� ID � �������������� ������ ������������ ��������
 */
public function getIdFieldName()
{
    return array( $this->IdObjectName, $this->IdFieldName);
}

/**
 * @param array $IdFieldName - array(��� ���-�������, ��� ����) 
 * ��� ID � �������������� ������ ������������ ��������
 */
public function setIdFieldName( array $IdFieldName )
{
    $this->IdObjectName = reset($IdFieldName);
    $this->IdFieldName = next($IdFieldName);
    reset($IdFieldName);
}

/**
 * ��������� ������, ������� ������������ ���� Repository, � ��������� ���������, 
 * ���� ������ � ������� ���������� Id
 * 
 * @param TRMIdDataObjectInterface $DataObject - ���������� ������
 */
private function addIdDataObjectToContainer(TRMIdDataObjectInterface $DataObject)
{
    $id = $DataObject->getId();
    if( null !== $id )
    {
        self::$IdDataObjectContainer[$this->ObjectTypeName][$id] = $DataObject;
    }
}

/**
 * �������������� getOne ��� ������ �������� �� ������� � ��������� ���������� �������� ������,
 * ���� ��� ��� ��� ������� �� ������������� ��������, �� �������� ��������� ������� �� ��������� ��������� 
 * ������� getOne(...) ������������� ������
 * 
 * @param string $objectname - ��� ������� ��� ������ �� ��������
 * @param string $fieldname - ���� ��� ������ �� ��������
 * @param mixed $value - �������� ��� �������� 
 * @param string $operator - ��������, �� �������� ����� ������������ �������� $value �� ��������� ����������� � ���� $fieldname ������� $do
 * 
 * @return TRMIdDataObjectInterface
 */
public function getOne($objectname, $fieldname, $value, $operator = "=")
{
    // ���� ������ ������� �� Id-����
    if( $objectname === $this->IdFieldName[0] && $fieldname === $this->IdFieldName[1] )
    {
        // ���������, ���� ������ � ���� Id ��� ���� � ��������� �������, �� 
        if( isset( self::$IdDataObjectContainer[$this->ObjectTypeName][$value] ) ) 
        {
            // � ������ ���
            return self::$IdDataObjectContainer[$this->ObjectTypeName][$value];
        }
    }
    // ����� ���������� ����� � ���������� (Persist) ���������, � ������ ���������� � ��
    $DataObject = parent::getOne( $objectname, $fieldname, $value, $operator);
    
    // ���� �� �� �������� ������ �� �������, �� getId ������ null
    if( $DataObject === null ) { return null; }
    
    // ���� ���������� ������ ��� ���� � ��������� ���������, 
    // �� ����� ������� ������, 
    // ��������� �� ������� �������, ��� ��� � ��������� ������� ����� ���� �� ���������� ���������,
    // �� ������ ������
    $id = $DataObject->getId();
    if( null !== $id && isset(self::$IdDataObjectContainer[$this->ObjectTypeName][$id]) )
    {
        return self::$IdDataObjectContainer[$this->ObjectTypeName][$id];
    }
    
    // ��������� ������ �� ������� ������ � ��������� �������
    $this->addIdDataObjectToContainer($DataObject);

    return $DataObject;
}

/**
 * @param array $DataArray - ������ � �������, �� ������� ����� ������ ������
 * 
 * @return TRMDataObjectInterface - ��������� ������ ������, ������� ������������ ���� ��������� �����������
 */
protected function getDataObjectFromDataArray(array $DataArray)
{
    $IdArr = $this->getIdFieldName();
    // ���������, ���� �� ������ � ���� � ID ��� ������� �������
    // ���� ��� ����� ������, �� � ���� ��� ID 
    if( isset($DataArray[$IdArr[0]][$IdArr[1]]) )
    {
        // ���� ����, �������� ID
        $id = $DataArray[$IdArr[0]][$IdArr[1]];
        // ���� � ��������� ������������ ��� ���� ������ � ����� Id, �� ������� ���...
        if( isset( self::$IdDataObjectContainer[$this->ObjectTypeName][$id] ) )
        {
            return self::$IdDataObjectContainer[$this->ObjectTypeName][$id];
        }
    }
    // ���� �� ������ � ��������� ���������, �� �������� ������������ �����,
    // ��� ����� ������ ����� ������ � ��� �������
    $DataObject = parent::getDataObjectFromDataArray($DataArray);
    
    $this->addIdDataObjectToContainer($DataObject);
    
    return $DataObject;
}

/**
 * �������� ������ ������� �� ��������� �� ID,
 * ������� ������� ����� ������� �� ID �� ����������� � ���������!
 * 
 * @param scalar $id - ������������� (Id) �������
 * 
 * @return TRMDataObjectInterface - ������, ����������� ������� �� ���������
 */
public function getById($id)
{
    $IdArr = $this->getIdFieldName();
    return $this->getOneBy( $IdArr[0], $IdArr[1], $id );
}

/**
 * ���������� ��������� ������ �� �������������� ��������� ���������
 * � ���������� ���������,
 * ���� ������ ������ ������� �� ��������� ��� � ���������� ���������, �� ��������� �����,
 * ��� ���� ��������� ����� ������� � ID � ��������� ���������
 */
public function doUpdate()
{
// �� ����� ������� ������������ �����, ������ ��� ��� ��������� ���������!
//    parent::doUpdate();
    if( !$this->CollectionToUpdate->count() ) { return; }

    $this->DataSource->update( $this->CollectionToUpdate );
    // ���� ���� ��������� ����� �������, �� � ��� �������� ����� ID,
    // ��������� ������� ���� ����������� ������� �� ������� � ��������� ID-��������� 
    foreach( $this->CollectionToUpdate as $CurrentDataObject )
    {
        $id = $CurrentDataObject->getId();
        if( !isset( self::$IdDataObjectContainer[$this->ObjectTypeName][$id] ) )
        {
            self::$IdDataObjectContainer[$this->ObjectTypeName][$id] = $CurrentDataObject;
        }
        
    }
    $this->CollectionToUpdate->clearCollection();
}

/**
 * ����������� ���������� �������� ������ ������� ��������� �� ����������� ��������� DataSource
 * ��� ���� ������� ��������� ������� �� ID �� ���������� ���������
 */
public function doDelete()
{
    if( !$this->CollectionToUpdate->count() ) { return; }

    // ���� ���� ��������� ����� �������, �� � ��� �������� ����� ID,
    // ��������� ������� ���� ����������� ������� �� ������� � ��������� ID-��������� 
    foreach( $this->CollectionToDelete as $CurrentDataObject )
    {
        $id = $CurrentDataObject->getId();
        if( $id && isset( self::$IdDataObjectContainer[$this->ObjectTypeName][$id] ) )
        {
            unset(self::$IdDataObjectContainer[$this->ObjectTypeName][$id]);
        }
        
    }
    parent::doDelete();
}

} // TRMIdDataObjectRepository
