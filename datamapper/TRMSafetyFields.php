<?php

namespace TRMEngine\DataMapper;

use TRMEngine\DataMapper\Exceptions\TRMDataMapperEmptySafetyFieldsArrayException;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\TRMDBObject;

/**
 * TRMSafetyFields - DataMappaer с возможностью получать свойств полей для таблиц из БД,
 * основная логика работы вынесена в TRMDataMapper
 *
 * @author TRM - 2018-08-26
 */
class TRMSafetyFields extends TRMDataMapper
{
/**
 * индекс в массиве для псевдонима таблицы
 */
const TABLEALIAS_INDEX  = "TableAlias";


/**
 * устанавливает псевдоним для таблицы $TableName, если он установлен
 * 
 * @param string $TableName - имя таблицы
 * @param string $TableAlias - псевдоним для таблицы, используемый в запросах
 */
public function setAliasForTableName($TableName, $TableAlias)
{
    $this->setRow($TableName, array(self::TABLEALIAS_INDEX => $TableAlias) );
}

/**
 * @param string $TableName - имя таблицы
 * 
 * @return возвращает псевдоним для таблицы $TableName, если он установлен,
 * если не задан, то вернет null
 */
public function getAliasForTableName( $TableName )
{

    if( empty( $this->DataArray[$TableName][self::TABLEALIAS_INDEX] ) ) { return null; }

    return  $this->DataArray[$TableName][self::TABLEALIAS_INDEX];
}

/**
 * дополняет уже заполненный мссив $this->DataArray данными из БД,
 * если в массиве не заданы хотя бы ассоциативные ключи, соответвующие именам таблиц в БД, 
 * то будет выброщено исключение
 * 
 * @param boolean $Extends - true - данные из схемы БД, false - данные из show columns
 * 
 * @throws TRMDataMapperEmptySafetyFieldsArrayException - если данные о полях таблицы получить не удалось, то выбрасывается исключение
 */
public function completeSafetyFieldsFromDB($Extends = false)
{
    if( !$this->count() )
    {
        throw new TRMDataMapperEmptySafetyFieldsArrayException( 
                __METHOD__ 
                . " Массив DataArray - пустой, "
                . "необходимо указать хотябы имена таблиц как ключи массива array( TableName => array(...), ... )" );
    }
    foreach( array_keys($this->DataArray) as $TableName )
    {
        $Status = isset( $this->DataArray[$TableName][TRMDataMapper::STATE_INDEX] ) 
                        ?  $this->DataArray[$TableName][TRMDataMapper::STATE_INDEX] 
                        : TRMDataMapper::READ_ONLY_FIELD;
        $this->completeSafetyFieldsFromDBFor(
            $TableName, 
            TRMDBObject::getTableColumnsInfo($TableName), 
            $Status, 
            $Extends
        );
    }
}

/**
 * вспомогательная функция, добавляет параметры полей в массив $this->DataArray[$TableName][TRMDataMapper::FIELDS_INDEX],
 * старые значения перезаписываются, только если ключи совпадают,
 * несовпадающие ключи массива остаются нетронутыми
 * 
 * @param string $TableName - имя таблицы, для которой устанавливается набор полей
 * @param array $Cols - параметры колонок в таблице БД, получается запросом SHOW COLUMNS FROM...
 * @param int $Status - состояние, по умолчанию = TRM_AR_READ_ONLY_FIELD
 * @param boolean $Extends - true - данные из схемы БД, false - данные из show columns
 */
private function completeSafetyFieldsFromDBFor( $TableName, array $Cols, $Status = TRMDataMapper::READ_ONLY_FIELD, $Extends = false )
{
    foreach( $Cols as $Column )
    {
        if( !$Extends ) { $this->completeField( $TableName, $Column["Field"], $Column, $Status); }
        else
        {
            $this->completeField( $TableName, $Column["COLUMN_NAME"], 
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
 * @param string $TableName - имя таблицы, для которй проверяется поле на auto_increment
 * @param string $FieldName - имя поля, проверяемого на auto_increment
 * 
 * @return boolean - в случае, если поле является автоинкрементным вернется true, иначе - false
 */
public function isFieldAutoIncrement($TableName, $FieldName)
{
    if( isset($this->DataArray[$TableName][TRMDataMapper::FIELDS_INDEX][$FieldName][TRMDataMapper::EXTRA_INDEX])
        && $this->DataArray[$TableName][TRMDataMapper::FIELDS_INDEX][$FieldName][TRMDataMapper::EXTRA_INDEX] == "auto_increment" )
    {
        return true;
    }
    return false;
}

/**
 * @param string $TableName - имя таблицы, для которй собирается массив имен полей
 * @param string $KeyStatus - "PRI" или "UNI" или "*" - собрать массив имен полей 
 * первичного или уникального индекса или вернуть все поля таблицы, соответственно
 * 
 * @return array - возвращает массив с именами 
 * первичных или уникальных ключей-индексов таблицы $TableName или все поля
 */
public function getIndexFieldsNames( $TableName, $KeyStatus = "PRI" )
{
    if( $KeyStatus == "*" )
    {
        return array_keys($this->DataArray[$TableName][TRMDataMapper::FIELDS_INDEX]);
    }
    return $this->getAllFieldsNamesForCondition( $TableName, TRMDataMapper::KEY_INDEX, $KeyStatus );
}

/**
 * @param string $TableName - имя таблицы, для которй собирается массив имен полей
 * 
 * @return array - возвращает массив с именами полей таблицы $TableName доступных для записи, 
 * т.е. State которых равен 
 * TRMDataMapper::UPDATABLE_FIELD или TRMDataMapper::FULL_ACCESS_FIELD
 */
public function getUpdatableFieldsNamesFor( $TableName )
{
    $FieldsNames1 = $this->getAllFieldsNamesForCondition( $TableName, TRMDataMapper::STATE_INDEX, TRMDataMapper::UPDATABLE_FIELD );
    $FieldsNames2 = $this->getAllFieldsNamesForCondition( $TableName, TRMDataMapper::STATE_INDEX, TRMDataMapper::FULL_ACCESS_FIELD );
    
    return array_unique( array_merge($FieldsNames1, $FieldsNames2), SORT_REGULAR );
}

/**
 * @param string $TableName - имя таблицы, для которй собирается массив имен полей
 * @param string $State - собирать поля доступные только для чтения/записи или все, в этом случае $State = null,
 * другие возможные варианты - TRMDataMapper::READ_ONLY_FIELD, TRMDataMapper::UPDATABLE_FIELD, TRMDataMapper::FULL_ACCESS_FIELD
 * 
 * @return array - возвращает массив с именами полей таблицы $TableName соответсвуюших условию $State
 */
public function getFieldsNamesForState( $TableName, $State = null )
{
    return $this->getAllFieldsNamesForCondition( $TableName, TRMDataMapper::STATE_INDEX, $State );
}

/**
 * @param string $TableName - имя таблицы, для которй собирается массив имен полей
 * 
 * @return array - возвращает массив с именами AUTO_INCREMENT полей таблицы $TableName
 */
public function getAutoIncrementFieldsNamesFor( $TableName )
{
    return $this->getAllFieldsNamesForCondition( $TableName, TRMDataMapper::EXTRA_INDEX, "auto_increment" );
}

/**
 * @param string $TableName - имя таблицы, для которй собирается массив имен полей
 * @param string $StateName - имя проверяемого статуса поля, 
 * если не установлен, т.е. === null, то вернутся все поля из $TableName
 * @param string $Value - искомое значение статуса поля
 * 
 * @return array - возвращает массив с именами полей таблицы $TableName соответсвуюших условию FieldsState[$StateName] == $Value
 */
private function getAllFieldsNamesForCondition( $TableName, $StateName = null, $Value = null )
{
    if( $StateName === null )
    {
        return array_keys($this->DataArray[$TableName][TRMDataMapper::FIELDS_INDEX]);
    }
    /*
     * убираем проверку, на время пока метод приватен, и вызывают его только внутренние функции с верными аргументами...
    if( !key_exists($StateName, self::$IndexArray) )
    {
        throw new Exception( __METHOD__ . " неверно указан индекс для статуса поля [{$StateName}]");
    }
     * 
     */
    
    $FieldsNames = array();
    foreach ( $this->DataArray[$TableName][TRMDataMapper::FIELDS_INDEX] as $FieldName => $FileldState )
    {
        if( isset($FileldState[$StateName]) && $FileldState[$StateName] == $Value )
        {
            $FieldsNames[] = $FieldName;
        }
    }
    return $FieldsNames;
}


} // TRMSafetyFields
