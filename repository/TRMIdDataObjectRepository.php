<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;

/**
 * ����� ����p������, ���������������� ��� ������ � ��������-������ ����������� TRMIdDataObjectInterface
 * �.�. � ��������� � ������� ���� ���������� ������������� - ID,
 * ��� ����� �������� ���� ����������� ������� ��������� ������-���������, � ������� �������� ������� � Id,
 * � ��� �� ���� ���������� ����� ������������� � ����� ����������!!!
 * getById - ������ ������ ������ �� ���� � ��� �� ������, ������� ��� ����������� ����� ������� ������ � ����� �����������..
 * 
 * @author TRM - 2018-07-28
 */
abstract class TRMIdDataObjectRepository extends TRMRepository
{
/**
 * @var string - ��� ����, ����������� ID ������
 */
protected $IdFieldName;
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
 * @return string - ��� ����, ����������� ID ������
 */
public function getIdFieldName()
{
    return $this->IdFieldName;
}

/**
 * @param string $IdFieldName - ��� ����, ����������� ID ������
 */
public function setIdFieldName($IdFieldName)
{
    $this->IdFieldName = $IdFieldName;
}

/**
 * ��������� ������� ������, ������� ������������ ���� Repository, � ��������� ���������, 
 * ���� ������ � ������� ���������� Id
 */
private function addCurrentObjectToContainer()
{
    $id = $this->CurrentObject->getId();
    if( null !== $id )
    {
        self::$IdDataObjectContainer[$this->ObjectTypeName][$id] = $this->CurrentObject;
    }
}

/**
 * �������������� ������������ �����, ��������� ������ �� ������ � ��������� ������, 
 * ���� ������ � ����� ������� ���� Id
 * 
 * @param TRMDataObjectInterface $object - ������ ���� ���� - TRMIdDataObjectInterface
 */
public function setObject(TRMDataObjectInterface $object)
{
    parent::setObject($object);
    $this->addCurrentObjectToContainer();
}

/**
 * ��������� ������ $do �� ������� ������� �������� $value � ���� $fieldname
 * 
 * @param TRMIdDataObjectInterface $do - ������ � ������� ��� �������� �������
 * @param string $fieldname - ���� ��� ������ �� ��������
 * @param mixed $value - �������� ��� �������� 
 * @param string $operator - ��������, �� �������� ����� ������������ �������� $value �� ��������� ����������� � ���� $fieldname ������� $do
 * 
 * @return boolean - ���� � ������� ���� $fieldname ������������� �������� $value �� ��������� $operator, 
 * �� �������� true, ����� false
 */
private function checkDataObject(TRMIdDataObjectInterface $do, $fieldname, $value, $operator)
{
    $res = $do->getFieldValue($fieldname);
    if( null === $res ) { return false; }
    
    switch ( strtoupper(trim($operator))  )
    {
        case "IS":
        case "=": if( $res === $value ) { return true; }
        case ">": if( $res > $value ) { return true; }
        case ">=": if( $res >= $value ) { return true; }
        case "<": if( $res < $value ) { return true; }
        case "<=": if( $res <= $value ) { return true; }
        case "NOT": 
        case "!=": 
        case "<>": if( $res !== $value ) { return true; }
        case "LIKE": return ( strpos($res, $value) !== false );
        case "NOT LIKE": return ( strpos($res, $value) === false );
    }
    
    return fasle;
}

/**
 * �������������� getBy ��� ������ �������� ������� � ��������� ���������� �������� ������,
 * ���� ��� ��� ��� ������� �� ������������� ��������, �� �������� ��������� ������� �� ��������� ��������� 
 * ������� getBy(...) ������������� ������
 * 
 * @param string $fieldname - ���� ��� ������ �� ��������
 * @param mixed $value - �������� ��� �������� 
 * @param string $operator - ��������, �� �������� ����� ������������ �������� $value �� ��������� ����������� � ���� $fieldname ������� $do
 * @param boolean $getfromdatasourceflag - ���� ���� ���� ���������� � true - �����������, �� ����� �� ���������� ���������� ������������ �� �����,
 * ����� ���������� ������ � ��������� ��������� (� ������ ���������� � ��)
 * 
 * @return TRMIdDataObjectInterface
 */
public function getBy($fieldname, $value, $operator = "=", $getfromdatasourceflag = true)
{
    // ���� ������ ������� �� Id-����
    if( $fieldname === $this->IdFieldName )
    {
        // ���������, ���� ������ � ���� Id ��� ���� � ��������� �������, �� 
        if( isset( self::$IdDataObjectContainer[$this->ObjectTypeName][$value] ) ) 
        {
            // ������������� ����������� �� ��������� ������ ��� �� �������������� � ������ �����
            $this->setObject(self::$IdDataObjectContainer[$this->ObjectTypeName][$value]);
            // � ������ ���
            return self::$IdDataObjectContainer[$this->ObjectTypeName][$value];
        }
    }
    // ���� �� ���������� ���� ����� �� ��������� ������ - $getfromdatasourceflag,
    // �� �������� ����� �� �������� ���������� � ��������� �������
    elseif( !$getfromdatasourceflag )
    {
        // ���������� ��� ��� ���������� � ���������� ������ �� ������� ������
        foreach( self::$IdDataObjectContainer[$this->ObjectTypeName] as $do )
        {
            // ���� ��� ������ ������ � ��������� ����������� ���� � ����������, �� ���������� ��� 
            if( true === $this->checkDataObject($do, $fieldname, $value, $operator) )
            {
                $this->setObject($do);
                return $do;
            }
        }
    }
    // ����� ����� ���������� ����� �� ���������, � ������ ���������� � ��
    // � getBy ��������������� $this->CurrentObject
    if( parent::getBy($fieldname, $value, $operator) === null ) { return null; }
    // ��������� ������ �� ������� ������ � ��������� �������
    $this->addCurrentObjectToContainer();

    return $this->CurrentObject;
}

/**
 * �������� ������ ������� �� ���������, �������� �� ��
 * 
 * @param integer $id - ������������� �������
 * 
 * @return TRMDataObjectInterface - ������, ����������� ������� �� ���������
 */
public function getById($id)
{
    if( is_numeric($id) || preg_match("#^[0-9]+$#", $id) )
    {
        return $this->getBy( $this->getIdFieldName(), (int)$id );
    }
    return null;
}

/**
 * ��������� ������ ���������� ������� � ���������,
 * ���� ������ ��� � ���������, �� ���������,
 * ��� ���� ������������� ����� ���������� Id. ���� �� �������� AUTO_INCREMENT
 * 
 * @return boolean
 */
public function update()
{
    if( false === parent::update() ) { return false; }

    // �������� �������� LastId, �� ����� ����������, 
    // ���� ����������� ���������� � ����������� �������� AUTO_INCREMENT ����
    //if( ($id = $this->DataSource->getLastId()) )
//    {
        // � ��������� 01.09.2018 ���� ��� ID ��� ���������������� ����� ��������������� ��������� � SQLDataSource
        //$this->CurrentObject->setId( $id );
        // ��������� ������ �� ������� ������ � ��������� �������
        $this->addCurrentObjectToContainer();
//    }
    return true;
}


} // TRMIdDataObjectRepository
