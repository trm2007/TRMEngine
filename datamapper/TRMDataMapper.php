<?php

namespace TRMEngine\DataMapper;

use TRMEngine\DataMapper\Exceptions\TRMDataMapperNotStringFieldNameException;
use TRMEngine\DataMapper\Exceptions\TRMDataMapperRelationException;

/**
 * ����� ��� �������� DataMapper,
 * ������ �� ������� TRMSafetyFields, 
 * ������ TRMSafetyFields ����������� �� TRMDataMapper
 *
 * @author TRM - 2018-08-26
 */
class TRMDataMapper implements \ArrayAccess, \Countable, \Iterator
{
/**
 * @var array(TRMObject) - ������ � ��������� TRMObject
 */
protected $Objects = array();

/**
 * ��������� ��� �������� 
 */
const STATE_INDEX       = "State"; // ������������� ����������� ������/������ ��� ����
const TYPE_INDEX        = "Type"; // ��� ������, ���������� � ����
const NULL_INDEX        = "Null"; // ����� �� ���� ���������� ������
const KEY_INDEX         = "Key"; // ��������� �������� �� � ���� ���� ����-ID, ��������� �������� PRI - ��������� ����, ��� ������������� � MySQL
const DEFAULT_INDEX     = "Default"; // �������� ��������������� �� ��������
const EXTRA_INDEX       = "Extra"; // ������������ ��������, ������� � �������� � ���� ������� - auto_increment, ����� ���� ������� � ����������� ������ SQL, ��� ��������� �������� �������� ���������� ������������ �������
const FIELDALIAS_INDEX  = "FieldAlias"; // ���������, ������������ � �������� ��� ������� ����
const QUOTE_INDEX       = "Quote"; // ��������� ����� �� ����� ��� ������� ���� � ��������� `
const COMMENT_INDEX     = "Comment"; // ����������� � ����, ���������� �������� �� ������� �����
const RELATION_INDEX    = "Relation"; // ������ � ������������� �� ����� ����, �������� � ���� �� ������� �������
const OBJECT_NAME_INDEX = "ObjectName"; // ��� �������, �� ������� ��������� ���� � ������� RELATION
const FIELD_NAME_INDEX  = "FieldName"; // ��� ����, �� ������� ��������� ������ ���� � ������� RELATION
const FIELDS_INDEX      = "Fields"; // ������ ��� ������� � ������ � �� ����������� � �������

/**
 * @var array - ������ �������� ��� FieldState � �������� ��� ���� ���������� �� ���������
 */
protected static $IndexArray = array(
    TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD,
    TRMDataMapper::TYPE_INDEX => "varchar(255)",
    TRMDataMapper::DEFAULT_INDEX => "",
    TRMDataMapper::KEY_INDEX => "",
    TRMDataMapper::EXTRA_INDEX => "",
    TRMDataMapper::FIELDALIAS_INDEX => null,
    TRMDataMapper::QUOTE_INDEX => TRMDataMapper::NEED_QUOTE,
    TRMDataMapper::COMMENT_INDEX => "",
    TRMDataMapper::NULL_INDEX => "NO",
    TRMDataMapper::RELATION_INDEX => null,
);

/** ��������� ������������, ��� ����� ����� ����� ����� � ������� */
const NEED_QUOTE = 32000;
/** ��������� ������������, ��� ����� ����� ����� � ������� �� ����� */
const NOQUOTE = 32001;

/**
 * ��������� ������������ ������� ������� � �����
 */
const READ_ONLY_FIELD = 512;
const UPDATABLE_FIELD = 256;
const FULL_ACCESS_FIELD = 768;

/**
 * ������ �������� �����, �������� ������� ����� ������ � ���������� � ��
 * @var array
 */
protected $SafetyFieldsArray = array();
/**
 * @var integer - ������� ������� ���������, ��� ���������� ���������� ���������
 */
private $Position = 0;

/**
 * @return array - $SafetyFieldsArray
 */
public function getSafetyFieldsArray()
{
    return $this->SafetyFieldsArray;
}
/**
 * @param array $SafetyFieldsArray
 */
public function setSafetyFieldsArray( array $SafetyFieldsArray )
{
    $this->SafetyFieldsArray = array();
    foreach( $SafetyFieldsArray as $ObjectName => $ObjectState )
    {
        $this->setSafetyFieldsFor($ObjectState[TRMDataMapper::FIELDS_INDEX], 
                $ObjectName, 
                isset($ObjectState[TRMDataMapper::STATE_INDEX]) ? $ObjectState[TRMDataMapper::STATE_INDEX] : TRMDataMapper::READ_ONLY_FIELD );
    }
}

/**
 * ������������� �������������� ���� ��� ������� $ObjectName,
 * ���� ���� ���� ����� �����������, �� ������ �������������!!!
 *
 * @param string $FieldName - ��� ������������ ����
 * @param string $ObjectName - ��� �������, ��� �������� ����������� ����
 * @param array $FieldState - ������ �� ���������� ���� array("State", "Type", "Default", "Key", "Extra", "FieldAlias", "Quote", "Comment")
 * @param int $DefaultState - ������ ����, 
 * ������� ����� ���������� ��� ���� �� ���������, 
 * ���� � ���� ���� �� ����� �������� "State",
 * �� ��������� ����������� �������� TRMDataMapper::READ_ONLY_FIELD
 */
public function setSafetyField( $FieldName, $ObjectName, array $FieldState, $DefaultState = TRMDataMapper::READ_ONLY_FIELD )
{
    $this->SafetyFieldsArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName] = array();
    $this->completeSafetyField($FieldName, $ObjectName, $FieldState, $DefaultState);
}

/**
 * ��������� �������������� ���� ��� ������� $ObjectName,
 * ���� ���� ���� ����� �����������, �� ������ �������������, ���� �������� �����,
 * ��������� ������ ��������� �����������!!!
 *
 * @param string $FieldName - ��� ������������ ����
 * @param string $ObjectName - ��� �������, ��� �������� ����������� ����
 * @param array $FieldState - ������ �� ���������� ���� array("State", "Type", "Default", "Key", "Extra", "FieldAlias", "Quote", "Comment")
 * @param int $DefaultState - ������ ����, 
 * ������� ����� ���������� ��� ���� �� ���������, 
 * ���� � ���� ���� �� ����� �������� "State",
 * �� ��������� ����������� �������� TRMDataMapper::READ_ONLY_FIELD
 */
protected function completeSafetyField( $FieldName, $ObjectName, array $FieldState, $DefaultState = TRMDataMapper::READ_ONLY_FIELD )
{
    if( !is_string($FieldName) )
    {
        throw new TRMDataMapperNotStringFieldNameException( " [{$FieldName}] " );
    }
    // ���� ��� ���� ��� �� ���������� ������ ����������, ������� ��� ������
    if(!isset($this->SafetyFieldsArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName]))
    {
        $this->SafetyFieldsArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName] = array();
    }
    // ���������� ���������� ��������� � ��� ������������ ��� ����, 
    // ������� ������ �������� �� �����
    $this->SafetyFieldsArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName] = 
            array_merge(
                    $this->SafetyFieldsArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName], 
                    $FieldState
                    );
    // ���� �����-�� �� ���������� �� �����, 
    // �� ����������� ��� �������� �� ��������� �� ������� self::$IndexArray
    foreach( self::$IndexArray as $Index => $Value)
    {
        if( isset($this->SafetyFieldsArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName][$Index]) )
        {
            continue;
        }
        if( $Index == TRMDataMapper::STATE_INDEX )
        {
            $this->SafetyFieldsArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName][$Index] = $DefaultState;
        }
        elseif( $Index == TRMDataMapper::COMMENT_INDEX )
        {
            $this->SafetyFieldsArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName][$Index] = $FieldName;
        }
        else
        {
            $this->SafetyFieldsArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName][$Index] = $Value;
        }
    }
}

/**
 * ��������� ���� �� ������ ��� ������� $ObjectName � ������� DataMapper-e
 * 
 * @param string $ObjectName - ��� ������������ ������� �������
 * @return boolean
 */
public function hasObject($ObjectName)
{
    return array_key_exists($ObjectName, $this->SafetyFieldsArray);
}

/**
 * ��������� ���� ��������� ��� ������/������ � ������� $ObjectName,
 * ������������� ���������� ������� ��������� SafetyFields � ������!!!
 *
 * @param array $Fields - ������ �������� array( FieldName => array(State...), ... ), ������ ����� � �� ���������, � ��� ����� ����������� ������-������
 * @param string $ObjectName - ��� �������, ��� �������� ����������� ����
 * @param int $DefaultState - ������ ����, 
 * ������� ����� ���������� ��� ���� ��������� ������� �� ���������, 
 * ���� � ��� ���� �� ����� �������� "State",
 * �� ��������� ����������� �������� TRMDataMapper::READ_ONLY_FIELD
 */
public function setSafetyFieldsFor( array $Fields, $ObjectName, $DefaultState = TRMDataMapper::READ_ONLY_FIELD )
{
    if( !isset($this->SafetyFieldsArray[$ObjectName]) )
    {
        $this->SafetyFieldsArray[$ObjectName] = array( 
            TRMDataMapper::STATE_INDEX => $DefaultState, 
            TRMDataMapper::FIELDS_INDEX => array() 
        );
    }

    foreach( $Fields as $FieldName => $FieldState )
    {
        $this->completeSafetyField($FieldName, $ObjectName, $FieldState, $DefaultState);
    }
    $this->rewind();
}

/**
 * ������� ���� �� ������� ��������� ��� ����� ���������
 *
 * @param string $FieldName - ��� ����, ������� ����� ���������
 * @param string $ObjectName - ��� �������, �� �������� ��������� ����, �� ��������� �� �������
 */
public function removeSafetyField( $FieldName, $ObjectName )
{
    if( isset($this->SafetyFieldsArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName]) )
    {
        unset($this->SafetyFieldsArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName]);
    }
}

/**
 * ������� ������ ��������� � ������ ������� �� ������� ����� ��� ���������
 *
 * @param string $ObjectName - ��� �������, ��� �������� ��������� ����
 */
public function removeSafetyFieldsForObject( $ObjectName  )
{
    if( isset($this->SafetyFieldsArray[$ObjectName]) )
    {
        unset($this->SafetyFieldsArray[$ObjectName]);
    }
}

/**
 * ������������� ������ ���� - �������� ��� ������/������ TRMDataMapper::READ_ONLY_FIELD / TRMDataMapper::UPDATABLE_FIELD,
 * ��� ��� ������ = TRMDataMapper::FULL_ACCESS_FIELD,
 * ����� �������� ��� �������������� � ������� ����,
 * ���� ������ ���� � ������� $ObjectName ���, �� ��������� �����
 * � ������������� � ���� ������ ������ ������-������,
 * ��� ��������� �������� ���� ��������������� �� ���������
 *
 * @param string $FieldName - ��� ����
 * @param string $ObjectName - ��� �������, ��� �������� ��������������� ����
 * @param int $State - ���������, �� ��������� = READ_ONLY_FIELD
 */
public function setSafetyFieldState( $FieldName, $ObjectName, $State = TRMDataMapper::READ_ONLY_FIELD )
{
    if( isset($this->SafetyFieldsArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName]) )
    {
        $this->SafetyFieldsArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName][TRMDataMapper::STATE_INDEX] = $State;
    }
    else
    {
        $this->setSafetyField($FieldName, $ObjectName, array( TRMDataMapper::STATE_INDEX => $State ) );
    }
}

/**
 * @param string $FieldName - ��� ����, ��� �������� ����� �������� ������
 * @param string $ObjectName - ��� �������, �������� ����������� ���� $FieldName
 * @return int|null - ���������� ������ ���� $FieldName � ������� $ObjectName - �������� ��� ������/������,
 * TRMDataMapper::READ_ONLY_FIELD ��� 
 * TRMDataMapper::FULL_ACCESS_FIELD ��� 
 * TRMDataMapper::UPDATABLE_FIELD
 */
public function getSafetyFieldState( $FieldName, $ObjectName )
{
    if( !isset( $this->SafetyFieldsArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName] ) )
    {
        return null;
    }
    return $this->SafetyFieldsArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName][TRMDataMapper::STATE_INDEX];
}

/**
 * @param string $LookingObjectName - ��� ������������ �������
 * @param string $LookingFieldName - ��� ������������ ���� �� ������� ����������� �� ���� ������ �����
 * 
 * @return array - ���������� ������ ���������� ����������� ���� �� ����������� ���� $LookingObjectName => $LookingFieldName,
 * ������ ���� array( $ObjectName1 => array(0=>$FieldName1, 1=>$FieldName2, ...), $ObjectName2 => ... )
 */
public function getBackRelationFor($LookingObjectName, $LookingFieldName)
{
    $FieldsArray = array();
    foreach( $this->SafetyFieldsArray as $ObjectName => $ObjectState )
    {
        foreach( $ObjectState[TRMDataMapper::FIELDS_INDEX] as $FieldName => $FieldState )
        {
            // ���� � ���������� ���� ���� ������ Relatin (RELATION_INDEX)
            // ��������� ��������� �� ��� �� ����������� ����
            if( isset($FieldState[TRMDataMapper::RELATION_INDEX])
                && $FieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::OBJECT_NAME_INDEX] == $LookingObjectName
                && $FieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::FIELD_NAME_INDEX] == $LookingFieldName
                )
            {
                $FieldsArray[$ObjectName][] = $FieldName;
            }
        }
    }
    return $FieldsArray;
}

/**
 * ��������� ������� �������� � ������� $this->SafetyFieldsArray,
 * ����� �������, ��� ������� ���� �������, �� ������� ���� ������, �� ������� �� �� ���� �� ���������,
 * � ������ � ����� �������������������, 
 * ��� �� ����������� ������� ������������� ������, ��� ��, �� ������� ��� ���������
 */
public function sortObjectsForRelationOrder()
{
    return uksort( $this->SafetyFieldsArray, array($this, "compareTwoTablesRelation") );
}

/**
 * ������� ��� ���������� ������ ������� $this->SafetyFieldsArray,
 * �.�. ��� ���������� �� ������ ������, ����������� �� ������� Relation � ������ ����� ������� �� �����,
 * ���� ���� ������� ��������� �� ������, ������ ��� ������ ������, 
 * � ������ ������ ���� � ������� ��������� ������...
 * � ������ ������:
 * ���� �� $Table1Name ���� ������ �� $Table2Name, �� �������� +1, �.�. $Table1Name > $Table2Name
 * ���� �� $Table1Name ���� ������ �� $Table2Name, �� �������� -1, �.�. $Table1Name < $Table2Name
 * ����� ������� �� ������� ���� � ������, �� �������� 0,  �.�. $Table1Name == $Table2Name
 * 
 * @param string $Table1Name - ������ ������������ ���� - ��� ������� 1
 * @param string $Table2Name - ������ ������������ ���� - ��� ������� 1
 * @return int - 0 - ������� ����������, 
 * +1 $Table1Name ������ $Table2Name, � $Table2Name ������ ���� ������ (���������� �� �����������),
 * -1 $Table2Name ������ $Table1Name, � $Table1Name ������ ���� ������
 */
private function compareTwoTablesRelation( $Table1Name, $Table2Name )
{
    // ��������� ��������� �� ������� 1 �� ������� 2
    foreach( $this->SafetyFieldsArray[$Table1Name][TRMDataMapper::FIELDS_INDEX] as $FieldName => $FieldState )
    {
        if( isset($FieldState[TRMDataMapper::RELATION_INDEX]) 
                && $FieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::OBJECT_NAME_INDEX] == $Table2Name
                )
        {
            // ����� >0, 1-� ������� ���������� �� 2-�, $Table1Name > $Table2Name, 
            // ������� 2 ������ ����������� ������, ��� �� ���������� ���� ��� �����
            // ��� �����, ��������, ����� ����������� ����� ������ � ���������������� �����, �� ������� ���� ������,
            // ����� ����������� ������ ���� ������ � � ����������� �������, �����������, ����!
            // � ����� ���������� �� ��� ����� inserted_id � ����� �������� ���� auto_increment,
            // �������� �������� ������ ������� � Relation-���� ����������� �������
            return +1; 
        }
    }
    // ���� ������ �� �1 �� �2 �� ������ ��������� ��������, ������ �� �2 �� �1
    // ��������� ��������� �� ������� 1 �� ������� 2
    foreach( $this->SafetyFieldsArray[$Table2Name][TRMDataMapper::FIELDS_INDEX] as $FieldName => $FieldState )
    {
        if( isset($FieldState[TRMDataMapper::RELATION_INDEX]) 
                && $FieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::OBJECT_NAME_INDEX] == $Table1Name
                )
        {
            // ����� <0, 2-� ������� ���������� �� 1-�, $Table1Name < $Table2Name, 
            // ������� 1 ������ ����������� ������, ��� �� ���������� ���� ��� �����
            return -1; 
        }
    }

    // ���� ������ �� �������, ������ ������� ���������
    // � ����� ������ ������� ����������
    return 0;
}

/**
 * ��� ������� � ������� ������ ���� ���������� ������ (������� ��� ������ � ��) ������ ���� ��������,
 * ��������, ����� - �������, � �������������, ������� ��������� - ��� ��������������� �������,
 * ������� ������ ����������, �.�. ��������� �� ���������������, 
 * �� ��������������� �� ����� ������������ - ��������� �� ������� ������,
 * ����� �������� (������� ��� ������ �� ���) ����� ���� ���������,
 * ��� ������� ���������� ������ �� ����� ������� �������� ��� �������� ������ �� ���
 * 
 * @return array - ���������� ������, ���������� ����� ��������, �� ������� ��� ������ ������ DataMapper
 * @throws TRMDataMapperRelationException - ���� ����� �������� �� �����������, 
 * �� ������������� ����������, � ������ ������ ����������� ������ �� ���������!
 */
public function getObjectsNamesWithoutBackRelations()
{
    // �������� ��� ����� �������� ������ SafetyFields
    // ������ ����� �� ��������� �������, 
    // ����� ������� �������� ������ ������ � ������� ��� � SafetyFieldsArray
    $ObjectsNamesArray = array_flip( array_keys( $this->SafetyFieldsArray ) );
    
    foreach( $this->SafetyFieldsArray as $ObjectState )
    {
        foreach( $ObjectState[TRMDataMapper::FIELDS_INDEX] as $FieldState )
        {
            // ���� � ���������� ���� ���� ������ Relation (������ �� ������ ���� ������� �������)
            // �� ������� �������� ������� $ObjectsNamesArray � ������ �������, �� ������� ���� ������
            if( isset($FieldState[TRMDataMapper::RELATION_INDEX]) 
                && isset($ObjectsNamesArray[ $FieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::OBJECT_NAME_INDEX] ]))
            {
                unset($ObjectsNamesArray[ $FieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::OBJECT_NAME_INDEX] ]);
                if(empty($ObjectsNamesArray))
                {
                    throw new TRMDataMapperRelationException( __METHOD__ );
                }
            }
        }
        // 
    }

    // ���������� ������ �� ���������� ������. �.�. �� ���������� ���� ��������!!!
    return array_keys($ObjectsNamesArray);
}

/**
 * ������� ���� ������ � ����������� �� �������� � �� �����
 */
public function clear()
{
    $this->Position = 0;
    $this->SafetyFieldsArray = array();
}


/**
 * ����������� �������� ��������� �������� - ���������� ���������� ArrayAccess
 * 
 * @param int $offset
 * @param array $value
 */
public function offsetSet($offset, $value)
{
    if (is_null($offset)) {
        $this->SafetyFieldsArray[] = $value;
    } else {
        $this->SafetyFieldsArray[$offset] = $value;
    }
}

/**
 * ����������, ���������� �� �������� �������� (����) - ���������� ���������� ArrayAccess
 * 
 * @param int $offset
 * @return array
 */
public function offsetExists($offset)
{
    return isset($this->SafetyFieldsArray[$offset]);
}

/**
 * ������� ��������, �.�. ������ �� ������� �� ��������� �������� - ���������� ���������� ArrayAccess
 * 
 * @param int $offset
 */
public function offsetUnset($offset)
{
    unset($this->SafetyFieldsArray[$offset]);
}

/**
 * ���������� �������� �������� (����) - ���������� ���������� ArrayAccess
 * 
 * @param int $offset
 * @return array
 */
public function offsetGet($offset)
{
    return isset($this->SafetyFieldsArray[$offset]) ? $this->SafetyFieldsArray[$offset] : null;
}

/**
 *  ���������� ���������� �������� � �������
 */
public function count()
{
    return count($this->SafetyFieldsArray);
}


/**
 * ������������� ���������� ������� ������� � ������ - ���������� ���������� Iterator
 */
public function rewind()
{
    reset($this->SafetyFieldsArray);
    $this->Position = 0;
}

public function current()
{
    return current($this->SafetyFieldsArray);
}

public function key()
{
    return key($this->SafetyFieldsArray);
}

public function next()
{
    next($this->SafetyFieldsArray);
    ++$this->Position;
}
/**
 * ���� ������� ��������� ��� ����� ������� �������, ������ � ���� �������� ��� ������ ���,
 * $this->Position ������ ������ ���� < count($this->SafetyFieldsArray)
 * 
 * @return boolean
 */
public function valid()
{
    return ($this->Position < count($this->SafetyFieldsArray));
}


} // TRMDataMapper
