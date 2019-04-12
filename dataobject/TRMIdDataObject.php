<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;

/**
 * ����������� ����� ��� ������� ������, ������� ����� ���� ��������� � �����������-���������,
 * ��� ��� ��� ������ ��� ����� ������, �� � ������ ������� ����� ���� ID-�������������,
 * � ��� �� �������� ���������� � ��������� ������� ����� $object->properties (����������� ������ __get � __set)
 *
 * @author TRM

 */
abstract class TRMIdDataObject extends TRMDataObject implements \ArrayAccess, TRMIdDataObjectInterface
{
/**
 * @var array - ��� �������� ��� �������������� �������, ������ ��������� � ������ ID-���� �� ��,
 * ������ ���� �������� � ������ �������� ������!!!
 */
// static protected $IdFieldName;

/**
 * @return array - ���������� ��� �������� ��� �������������� �������, ������ ��������� � ������ ID-���� �� ��,
 * ������������ ������ IdFieldName = array( ��� �������, ��� ID-���� � ������� )
 */
static public function getIdFieldName()
{
    return static::$IdFieldName;
}

/**
 * @param array $IdFieldName - ������������� ��� �������� ��� �������������� �������, 
 * ������ ��������� � ������ ID-���� �� ��,
 * ���������� ������ IdFieldName = array( ��� �������, ��� ID-���� � ������� )
 */
static public function setIdFieldName( array $IdFieldName ) 
{
    static::$IdFieldName[0] = reset($IdFieldName);
    static::$IdFieldName[1] = next($IdFieldName);
    reset($IdFieldName);
}

/**
 * ���������� �������� ���� ���������� ����� - ID, 
 * ��� ����� ��������� ���� ������ ��� ����� � IdFieldName
 *
 * @return mixed|null - ID-�������
 */
public function getId()
{
    // � 24.03.2019
    // IdFieldName - ��� ������ ���������� array( ��� �������, ��� ���� )
    if( !isset(static::$IdFieldName[0]) || !isset(static::$IdFieldName[1]) )
    {
        throw new TRMException( __METHOD__ . " - �� ���������� IdFieldName!");
    }

    $data = $this->getData( static::$IdFieldName[0], static::$IdFieldName[1] );
    
    // ��������� �� �������, 
    // ��� ��� ����� ���������� ���� ����� � int ����� �������������� ��� 0 
    if( false === $data || "" === $data ) { return null; }

    return $data;
}

/**
 * ������������� �������� ID-���� � ������������ � ������� �� ������� IdFieldName!!!
 * ��� ��������� ����� ������� ���� ������ � IdFieldName
 *
 * @param mixed - ID-�������
 */
public function setId($id)
{
    $this->setData( static::$IdFieldName[0], static::$IdFieldName[1], $id );
}

/**
 * �������� ID-�������
 * ������������ setId(null);
 */
public function resetId()
{
    $this->setData( static::$IdFieldName[0], static::$IdFieldName[1], null );
}


} // TRMIdDataObject