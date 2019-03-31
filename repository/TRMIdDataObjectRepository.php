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
 * @var array - array(��� ���-�������, ��� ����), ����������� ID ������
 */
protected $IdFieldName;

/**
 * @var array(TRMIdDataObjectInterface) - ������ ��������, ���������� � ����������� ����� ������ �����������, 
 * ������ �� ��� ������� �������� � ���� �������, 
 * � ��� ������� ��� ���������� �� �� (��� ������� ���������) ������� �� �������� �� �������
 */
protected static $IdDataObjectContainer = array();


/**
 * @return array - array(��� ���-�������, ��� ����) ��� ID � �������������� ������ ������������ ��������
 */
public function getIdFieldName()
{
    return $this->IdFieldName;
}

/**
 * @param array $IdFieldName - array(��� ���-�������, ��� ����) 
 * ��� ID � �������������� ������ ������������ ��������
 */
public function setIdFieldName( array $IdFieldName )
{
    $this->IdFieldName[0] = reset($IdFieldName);
    $this->IdFieldName[1] = next($IdFieldName);
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
        static::$IdDataObjectContainer[$this->ObjectTypeName][$id] = $DataObject;
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
 * @param TRMDataObjectInterface $DataObject - ���� ����� ������, �� ����� ����������� �� �����,
 * ����� ����������� �������� ����� �������
 * 
 * @return TRMIdDataObjectInterface
 */
public function getOneBy($objectname, $fieldname, $value, TRMDataObjectInterface $DataObject = null)
{
    // ���� ������ ������� �� Id-����
    if( $objectname === $this->IdFieldName[0] && $fieldname === $this->IdFieldName[1] )
    {
        // ���������, ���� ������ � ���� Id ��� ���� � ��������� �������, �� 
        if( isset( static::$IdDataObjectContainer[$this->ObjectTypeName][$value] ) ) 
        {
            // ������ ���
            return $DataObject = static::$IdDataObjectContainer[$this->ObjectTypeName][$value];
        }
    }
    // ����� ���������� ����� � ���������� ��������� DataSource, � ������ ���������� � ��
    $NewDataObject = parent::getOneBy( $objectname, $fieldname, $value, $DataObject);
    
    // ���� �� �� �������� ������ �� �������, �� getId ������ null
    if( $NewDataObject === null ) { return null; }
    
    // ���� ���������� ������ ��� ���� � ��������� ���������, 
    // �� ����� ������� ������, 
    // ��������� �� ������� �������, ��� ��� � ��������� ������� ����� ���� �� ���������� ���������,
    // �� ������ ������
    $id = $NewDataObject->getId();
    if( null !== $id && isset(static::$IdDataObjectContainer[$this->ObjectTypeName][$id]) )
    {
        return static::$IdDataObjectContainer[$this->ObjectTypeName][$id];
    }
    
    // ��������� ������ �� ������� ������ � ��������� �������
    $this->addIdDataObjectToContainer($NewDataObject);

    return $NewDataObject;
}

/**
 * @param array $DataArray - ������ � �������, �� ������� ����� ������ ������
 * @param TRMDataObjectInterface $DataObject - ���� ����� ������, �� ����� ����������� �� �����,
 * ����� ����������� �������� ����� �������
 * 
 * @return TRMDataObjectInterface - ���� ������ ��� ����������� � ����� ID � ��������� ���������, 
 * �� �������� ��,
 * ����� ��������� ������ ������, ������� ������������ ���� ��������� �����������
 */
protected function getDataObjectFromDataArray(array $DataArray, TRMDataObjectInterface $DataObject = null)
{
    $IdArr = $this->getIdFieldName();
    // ���������, ���� �� ������ � ���� � ID ��� ������� �������
    // ���� ��� ����� ������, �� � ���� ��� ID 
    if( isset($DataArray[$IdArr[0]][$IdArr[1]]) )
    {
        // ���� ����, �������� ID
        $id = $DataArray[$IdArr[0]][$IdArr[1]];
        // ���� � ��������� ������������ ��� ���� ������ � ����� Id, �� ������� ���...
        if( isset( static::$IdDataObjectContainer[$this->ObjectTypeName][$id] ) )
        {
            return $DataObject = static::$IdDataObjectContainer[$this->ObjectTypeName][$id];
        }
    }
    // ���� �� ������ � ��������� ���������, �� �������� ������������ �����,
    // ��� ����� ������ ����� ������ � ��� �������
    $NewDataObject = parent::getDataObjectFromDataArray($DataArray, $DataObject);
    
    $this->addIdDataObjectToContainer($NewDataObject);
    
    return $NewDataObject;
}

/**
 * �������� ������ ������� �� ��������� �� ID,
 * ������� ������� ����� ������� �� ID �� ����������� � ���������!
 * 
 * @param scalar $id - ������������� (Id) �������
 * @param TRMDataObjectInterface $DataObject - ���� ����� ������, �� ����� ����������� �� �����,
 * ����� ����������� �������� ����� �������
 * 
 * @return TRMDataObjectInterface - ������, ����������� ������� �� ���������
 */
public function getById($id, TRMDataObjectInterface $DataObject = null)
{
    $IdArr = $this->getIdFieldName();
    return $this->getOneBy( $IdArr[0], $IdArr[1], $id, $DataObject );
}

/**
 * ���������� ��������� ������ �� �������������� ��������� ���������
 * � ���������� ���������,
 * ���� ������ ������ ������� �� ��������� ��� � ���������� ���������, �� ��������� �����,
 * ��� ���� ��������� ����� ������� � ID � ��������� ���������
 * 
 * @param bool $ClearCollectionFlag - ���� ����� ����� ���������� ��������� ��������� ����������� ��������, 
 * �� ���� ���� ������� ���������� � false, ��� ����� ������������ �������� �������,
 * �� ����� ����������� ��������� doUpdate ����� �������� ���������,
 * ��� �� �� ��������� ���������� � ������� 2 ����!
 * 
 * @return void
 */
public function doUpdate( $ClearCollectionFlag = true )
{
    parent::doUpdate( false );
    if( !$this->CollectionToUpdate->count() ) { return; }
    // ���� ���� ��������� ����� �������, �� � ��� �������� ����� ID,
    // ��������� ������� ���� ����������� ������� �� ������� � ��������� ID-��������� 
    foreach( $this->CollectionToUpdate as $CurrentDataObject )
    {
        $id = $CurrentDataObject->getId();
        if( !isset( static::$IdDataObjectContainer[$this->ObjectTypeName][$id] ) )
        {
            static::$IdDataObjectContainer[$this->ObjectTypeName][$id] = $CurrentDataObject;
        }
        
    }
    if( $ClearCollectionFlag ) { $this->CollectionToUpdate->clearCollection(); }
}

/**
 * ����������� ���������� �������� ������� ������ ��������� �� ����������� ��������� DataSource
 * ��� ���� ������� ��������� ������� �� ID �� ���������� ���������
 * 
 * @param bool $ClearCollectionFlag - ���� ����� ����� �������� ��������� ��������� ��������� ��������, 
 * �� ���� ���� ������� ���������� � false, ��� ����� ������������ �������� �������,
 * �� ����� ����������� ��������� doDelete ����� �������� ���������,
 * ��� �� �� ��������� �������� � ������� 2 ����!
 */
public function doDelete( $ClearCollectionFlag = true )
{
    if( !$this->CollectionToDelete->count() ) { return; }

    // ���� ���� ��������� ����� �������, �� � ��� �������� ����� ID,
    // ��������� ������� ���� ����������� ������� �� ������� � ��������� ID-��������� 
    foreach( $this->CollectionToDelete as $CurrentDataObject )
    {
        $id = $CurrentDataObject->getId();
        if( $id && isset( static::$IdDataObjectContainer[$this->ObjectTypeName][$id] ) )
        {
            unset(static::$IdDataObjectContainer[$this->ObjectTypeName][$id]);
        }
        
    }
    parent::doDelete();
}

} // TRMIdDataObjectRepository
