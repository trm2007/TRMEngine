<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;

/**
 * ����� �����������, ���������������� ��� ������ � ��������-������ ����������� TRMIdDataObjectInterface
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
 * @var TRMIdDataObjectInterface - ������ �� ������� ������
 */
protected $CurrentObject = null;
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
 * @return array - ��� ����, ����������� ID ������
 */
public function getIdFieldName()
{
    return array( $this->IdObjectName, $this->IdFieldName);
}

/**
 * @param array $IdFieldName - ��� ����, ����������� ID ������
 */
public function setIdFieldName( array $IdFieldName )
{
    $this->IdObjectName = reset($IdFieldName);
    $this->IdFieldName = next($IdFieldName);
    reset($IdFieldName);
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
 * @param string $objectname - ��� ������� ��� �������� ����
 * @param string $fieldname - ��� ���� ��� �������� ��������
 * @param mixed $value - �������� ��� �������� 
 * @param string $operator - ��������, �� �������� ����� ������������ �������� $value �� ��������� ����������� � ���� $fieldname ������� $do
 * 
 * @return boolean - ���� � ������� ���� $fieldname ������������� �������� $value �� ��������� $operator, 
 * �� �������� true, ����� false
 */
private function checkDataObject(TRMIdDataObjectInterface $do, $objectname, $fieldname, $value, $operator)
{
    $res = $do->getFieldValue($objectname, $fieldname);
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
 * @param string $objectname - ��� ������� ��� ������ �� ��������
 * @param string $fieldname - ���� ��� ������ �� ��������
 * @param mixed $value - �������� ��� �������� 
 * @param string $operator - ��������, �� �������� ����� ������������ �������� $value �� ��������� ����������� � ���� $fieldname ������� $do
 * @param boolean $getfromdatasourceflag - ���� ���� ���� ���������� � true - �����������, �� ����� �� ���������� ���������� ������������ �� �����,
 * ����� ���������� ������ � ��������� ��������� (� ������ ���������� � ��)
 * 
 * @return TRMIdDataObjectInterface
 */
public function getBy($objectname, $fieldname, $value, $operator = "=", $getfromdatasourceflag = true)
{
    // ���� ������ ������� �� Id-����
    if( $objectname === $this->IdFieldName[0] && $fieldname === $this->IdFieldName[1] )
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
            if( true === $this->checkDataObject($do, $objectname, $fieldname, $value, $operator) )
            {
                $this->setObject($do);
                return $do;
            }
        }
    }
    // ����� ����� ���������� ����� � ���������� (Persist) ���������, � ������ ���������� � ��
    // ���� CurrentObject ��� �� ���������� (null),
    // �� ����� ������ � ��������� � getBy
    parent::getBy( $objectname, $fieldname, $value, $operator);
    
    // ���� �� �� �������� ������ �� �������, �� getId ������ null
    if( $this->CurrentObject->getId() === null ) { return null; }
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
        $IdArr = $this->getIdFieldName();
        return $this->getBy( $IdArr[0], $IdArr[1], (int)$id );
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
