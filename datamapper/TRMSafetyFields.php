<?php

namespace TRMEngine\DataMapper;

use TRMEngine\DataMapper\Exceptions\TRMDataMapperEmptySafetyFieldsArrayException;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\TRMDBObject;

/**
 * TRMSafetyFields - DataMappaer � ������������ �������� ������� ����� ��� ������ �� ��,
 * �������� ������ ������ �������� � TRMDataMapper
 *
 * @author TRM - 2018-08-26
 */
class TRMSafetyFields extends TRMDataMapper
{
/**
 * ������ � ������� ��� ���������� �������
 */
const TABLEALIAS_INDEX  = "TableAlias";


/**
 * ������������� ��������� ��� ������� $TableName, ���� �� ����������
 * 
 * @param string $TableName - ��� �������
 * @param string $TableAlias - ��������� ��� �������, ������������ � ��������
 */
public function setAliasForTableName($TableName, $TableAlias)
{
    if( !isset($this->SafetyFieldsArray[$TableName]) ) { $this->SafetyFieldsArray[$TableName] = array(); }
    $this->SafetyFieldsArray[$TableName][self::TABLEALIAS_INDEX] = $TableAlias;
}

/**
 * @param string $TableName - ��� �������
 * 
 * @return ���������� ��������� ��� ������� $TableName, ���� �� ����������,
 * ���� �� �����, �� ������ null
 */
public function getAliasForTableName( $TableName )
{
    if( empty( $this->SafetyFieldsArray[$TableName][self::TABLEALIAS_INDEX] ) ) { return null; }

    return  $this->SafetyFieldsArray[$TableName][self::TABLEALIAS_INDEX];
}

/**
 * ���������� ������ ���������� ��� ������ � ������ ����� �� ��������� ������� ������ � ������� �� ��,
 * ��������� ����� ����� � ��������� � ��������� ��� ������� ��� ����, ���� �����������,
 * ����� �������� ����� ��������� � ��� ������������� ������� � ������,
 * ����� ��������� ��������� ������ � ���������� ���������� ��� �������� �� �����,
 * ����� ������� ������ � ����� ������� ������ ���������� ������� clear, 
 * ����� �������� ������� ����� ��������� �����
 *
 * @param string $TableName - ��� �������, ��� ������� ��������������� ����� �����
 * @param int $State - ���������, �� ��������� = TRM_AR_READ_ONLY_FIELD
 * @param boolean $Extends - true - ������ �� ����� ��, false - ������ �� show columns
 */
public function generateSafetyFromDB($TableName, $State = TRMDataMapper::READ_ONLY_FIELD, $Extends = false )
{
    $this->setSafetyFromDB( $TableName, TRMDBObject::getTableColumnsInfo($TableName), $State, $Extends );
}

/**
 * ��������� ��� ����������� ����� $this->SafetyFieldsArray ������� �� ��,
 * ���� ������ �� ������ ���� �� ������������� �����, ������������� ������ ������ � ��, 
 * �� ����� ��������� ����������
 * 
 * @param boolean $Extends - true - ������ �� ����� ��, false - ������ �� show columns
 * 
 * @throws TRMDataMapperEmptySafetyFieldsArrayException - ���� ������ � ����� ������� �������� �� �������, �� ������������� ����������
 */
public function completeSafetyFieldsFromDB($Extends = false)
{
    if( empty($this->SafetyFieldsArray) )
    {
        throw new TRMDataMapperEmptySafetyFieldsArrayException( __METHOD__ . " ������ SafetyFieldsArray - ������, "
                . "���������� ������� ������ ����� ������ ��� ����� ������� array( TableName => array(...), ... )" );
    }
    foreach( array_keys($this->SafetyFieldsArray) as $TableName )
    {
        $Status = isset( $this->SafetyFieldsArray[$TableName]["State"] ) ?  $this->SafetyFieldsArray[$TableName]["State"] : TRMDataMapper::READ_ONLY_FIELD;
        $this->completeSafetyFieldsFromDBFor($TableName, TRMDBObject::getTableColumnsInfo($TableName), $Status, $Extends);
    }
}

/**
 * ��������������� �������, ������������� ��������� ����� � ������ $this->SafetyFieldsArray[$TableName]["Fields"],
 * ��� ������ �������� ��� ����� ���� ���������
 * 
 * @param string $TableName - ��� �������, ��� ������� ��������������� ����� �����
 * @param array $Cols - ��������� ������� � ������� ��, ���������� �������� SHOW COLUMNS FROM...
 * @param int $Status - ���������, �� ��������� = TRM_AR_READ_ONLY_FIELD
 * @param boolean $Extends - true - ������ �� ����� ��, false - ������ �� show columns
 */
private function setSafetyFromDB( $TableName, array $Cols, $Status = TRMDataMapper::READ_ONLY_FIELD, $Extends = false )
{
    $this->SafetyFieldsArray[$TableName] = array();
    $this->completeSafetyFieldsFromDBFor($TableName, $Cols, $Status, $Extends);
}

/**
 * ��������������� �������, ��������� ��������� ����� � ������ $this->SafetyFieldsArray[$TableName]["Fields"],
 * ������ �������� ����������������, ������ ���� ����� ���������,
 * ������������� ����� ������� �������� �����������
 * 
 * @param string $TableName - ��� �������, ��� ������� ��������������� ����� �����
 * @param array $Cols - ��������� ������� � ������� ��, ���������� �������� SHOW COLUMNS FROM...
 * @param int $Status - ���������, �� ��������� = TRM_AR_READ_ONLY_FIELD
 * @param boolean $Extends - true - ������ �� ����� ��, false - ������ �� show columns
 */
private function completeSafetyFieldsFromDBFor( $TableName, array $Cols, $Status = TRMDataMapper::READ_ONLY_FIELD, $Extends = false )
{
    foreach( $Cols as $Column )
    {
        if( !$Extends ) { $this->completeSafetyField( $Column["Field"], $TableName, $Column, $Status); }
        else
        {
            $this->completeSafetyField( $Column["COLUMN_NAME"], $TableName, 
                array(
                    TRMDataMapper::COMMENT_INDEX => $Column['COLUMN_COMMENT'],
                    TRMDataMapper::DEFAULT_INDEX => $Column['COLUMN_DEFAULT'],
                    TRMDataMapper::EXTRA_INDEX => $Column['EXTRA'],
                    TRMDataMapper::KEY_INDEX => $Column['COLUMN_KEY'],
                    TRMDataMapper::NULL_INDEX => $Column['IS_NULLABLE'],
                    TRMDataMapper::TYPE_INDEX => $Column['COLUMN_TYPE'],
                ),    
//    `COLUMN_NAME`,`COLUMN_DEFAULT`,`IS_NULLABLE`,`DATA_TYPE`,`CHARACTER_MAXIMUM_LENGTH`,`NUMERIC_PRECISION`,`CHARACTER_SET_NAME`,`COLUMN_TYPE`,`COLUMN_KEY`,`EXTRA`,`COLUMN_COMMENT``        . `);                
            $Status);
        }
    }
}

/**
 * @param string $TableName - ��� �������, ��� ������ ����������� ���� �� auto_increment
 * @param string $FieldName - ��� ����, ������������ �� auto_increment
 * 
 * @return boolean - � ������, ���� ���� �������� ���������������� �������� true, ����� - false
 */
public function isFieldAutoIncrement($TableName, $FieldName)
{
    if( isset($this->SafetyFieldsArray[$TableName]["Fields"][$FieldName][TRMDataMapper::EXTRA_INDEX])
        && $this->SafetyFieldsArray[$TableName]["Fields"][$FieldName][TRMDataMapper::EXTRA_INDEX] == "auto_increment" )
    {
        return true;
    }
    return false;
}

/**
 * @param string $TableName - ��� �������, ��� ������ ���������� ������ ���� �����
 * @param string $KeyStatus - "PRI" ��� "UNI" ��� "*" - ������� ������ ���� ����� ���������� ��� ����������� ������� ��� ������� ��� ���� �������, ��������������
 * 
 * @return array - ���������� ������ � ������� ��������� ��� ���������� ������-�������� ������� $TableName ��� ��� ����
 */
public function getIndexFieldsNames( $TableName, $KeyStatus = "PRI" )
{
    if( $KeyStatus == "*" )
    {
        return array_keys($this->SafetyFieldsArray[$TableName]["Fields"]);
    }
    return $this->getAllFieldsNamesForCondition( $TableName, TRMDataMapper::KEY_INDEX, $KeyStatus );
}

/**
 * @param string $TableName - ��� �������, ��� ������ ���������� ������ ���� �����
 * 
 * @return array - ���������� ������ � ������� ����� ������� $TableName ��������� ��� ������, 
 * �.�. State ������� ����� 
 * TRMDataMapper::UPDATABLE_FIELD ��� TRMDataMapper::FULL_ACCESS_FIELD
 */
public function getUpdatableFieldsNamesFor( $TableName )
{
    $FieldsNames1 = $this->getAllFieldsNamesForCondition( $TableName, TRMDataMapper::STATE_INDEX, TRMDataMapper::UPDATABLE_FIELD );
    $FieldsNames2 = $this->getAllFieldsNamesForCondition( $TableName, TRMDataMapper::STATE_INDEX, TRMDataMapper::FULL_ACCESS_FIELD );
    
    return array_unique( array_merge($FieldsNames1, $FieldsNames2), SORT_REGULAR );
}

/**
 * @param string $TableName - ��� �������, ��� ������ ���������� ������ ���� �����
 * @param string $State - �������� ���� ��������� ������ ��� ������/������ ��� ���, � ���� ������ $State = null,
 * ������ ��������� �������� - TRMDataMapper::READ_ONLY_FIELD, TRMDataMapper::UPDATABLE_FIELD, TRMDataMapper::FULL_ACCESS_FIELD
 * 
 * @return array - ���������� ������ � ������� ����� ������� $TableName �������������� ������� $State
 */
public function getFieldsNamesForState( $TableName, $State = null )
{
    return $this->getAllFieldsNamesForCondition( $TableName, TRMDataMapper::STATE_INDEX, $State );
}

/**
 * @param string $TableName - ��� �������, ��� ������ ���������� ������ ���� �����
 * 
 * @return array - ���������� ������ � ������� AUTO_INCREMENT ����� ������� $TableName
 */
public function getAutoIncrementFieldsNamesFor( $TableName )
{
    return $this->getAllFieldsNamesForCondition( $TableName, TRMDataMapper::EXTRA_INDEX, "auto_increment" );
}

/**
 * @param string $TableName - ��� �������, ��� ������ ���������� ������ ���� �����
 * @param string $StateName - ��� ������������ ������� ����
 * @param string $Value - ������� �������� ������� ����
 * 
 * @return array - ���������� ������ � ������� ����� ������� $TableName �������������� ������� FieldsState[$StateName] == $Value
 */
private function getAllFieldsNamesForCondition( $TableName, $StateName = null, $Value = null )
{
    if( $StateName == null )
    {
        return array_keys($this->SafetyFieldsArray[$TableName]["Fields"]);
    }
    /*
     * ������� ��������, �� ����� ���� ����� ��������, � �������� ��� ������ ���������� ������� � ������� �����������...
    if( !key_exists($StateName, self::$IndexArray) )
    {
        throw new Exception( __METHOD__ . " ������� ������ ������ ��� ������� ���� [{$StateName}]");
    }
     * 
     */
    
    $FieldsNames = array();
    foreach ( $this->SafetyFieldsArray[$TableName]["Fields"] as $FieldName => $FileldState )
    {
        if( isset($FileldState[$StateName]) && $FileldState[$StateName] == $Value )
        {
            $FieldsNames[] = $FieldName;
        }
    }
    return $FieldsNames;
}


} // TRMSafetyFields
