<?php

namespace TRMEngine\DataMapper;

use TRMEngine\DataArray\TRMDataArray;
use TRMEngine\DataMapper\TRMDataMapper;

/**
 * класс для работы с информацией об объекте, 
 * фактически для SQL содержит данные о таблице, 
 * содержит $State - состояние (возмоность записи чтения для всего объект),
 * и массив $Fields - с объектами полей для данного объекта-таблицы
 */
class TRMObjectMapper extends TRMDataArray
{
/**
 * статус объекта - доступен только для чтения
 */
const TRM_OBJECT_STATE_READ_ONLY = 512;
/**
 * статус объекта - доступен для записи и чтения
 */
const TRM_OBJECT_STATE_UPDATABLE = 256;

/**
 * @var string - имя объекта-таблицы
 */
public $Name;
/**
 * @var string - псевдоним объекта, как правило применяется в SQL-запросах
 */
public $Alias = "";

/**
 * @var int - состояние - возмоность записи или только чтения для всего объект
 */
public $State = self::TRM_OBJECT_STATE_READ_ONLY;

/**
 * @return int - состояние - возмоность записи или только чтения для всего объект
 */
public function getState()
{
    return $this->State;
}
/**
 * 
 * @param int $State - состояние - возмоность записи или только чтения для всего объект
 */
public function setState($State)
{
    $this->State = $State;
}

/**
 * возвращает объект для поля с именем $FieldName,
 * если поля с таким именем нет, то возвращаетс null
 * 
 * @param string $FieldName - имя поля
 * 
 * @return TRMFieldMapper $Field - объект с информацией о поле
 */
public function getField( $FieldName )
{
    if( array_key_exists( $FieldName, $this->DataArray) )
    {
        return $this->DataArray[$FieldName];
    }
    return null;
}

/**
 * устанавливает объект поля с именем $Field->Name,
 * если ранее было установлено поле с таким именем, то оно перезапищется
 * 
 * @param TRMFieldMapper $Field - объект с информацией о поле
 */
public function setField( TRMFieldMapper $Field )
{
    $this->DataArray[$Field->Name] = $Field;
}

/**
 * 
 * @return array -  возвращает массив с объектами TRMField
 */
public function getFields()
{
    return $this->DataArray;
}

/**
 * устанавливает объекты полей из массива $Fields,
 * существующие данные удаляются
 * 
 * @param array(TRMField) $Fields - массив объектов TRMField с информацией о поле
 */
public function setFields( array $Fields )
{
    $this->DataArray = array();
    $this->addFields($Fields);
}

/**
 * @return array - массив с именами полей содержащих первичные ключи (PRI)
 */
public function getPriFields()
{
    $FieldsArr = array();
    foreach( $this->DataArray as $FieldName => $Field )
    {
        if( $Field->isPri() )
        {
            $FieldsArr[] = $FieldName;
        }
    }
    
    return $FieldsArr;
}

/**
 * @return array - массив с именами полей содержащих уникальные ключи (UNI)
 */
public function getUniFields()
{
    $FieldsArr = array();
    foreach( $this->DataArray as $FieldName => $Field )
    {
        if( $Field->isUni() )
        {
            $FieldsArr[] = $FieldName;
        }
    }
    
    return $FieldsArr;
}

/**
 * устанавливает объекты полей из массива $Fields,
 * существующие данные удаляются
 * 
 * @param array $Fields - массив с массивами с информацией о поле
 */
public function setFieldsArray( array &$Fields )
{
    $this->DataArray = array();
    foreach( $Fields as $FieldName => $Field )
    {
        $FieldObject = new TRMFieldMapper($FieldName);
        $FieldObject->initializeFromArray($Field);
        $this->setField($FieldObject);
    }
}

/**
 * добавляет объекты полей из массива $Fields,
 * если ранее было установлено поле с таким именем, то оно перезапищется
 * 
 * @param array(TRMField) $Fields - объект с информацией о поле
 */
public function addFields( array $Fields )
{
    foreach( $Fields as $Field )
    {
        $this->setField($Field);
    }
}

/**
 * убираеТ поле из массива $Fields
 *
 * @param string $FieldName - имя поля, которое нужно исключить
 */
public function removeField( $FieldName )
{
    $this->removeRow($FieldName);
}

/**
 * убираеТ поле из массива $Fields
 *
 * @param string $FieldName - имя поля, которое нужно исключить
 */
public function hasField( $FieldName )
{
    return $this->keyExists($FieldName);
}


/**
 * @param string $StateName - имя проверяемого статуса поля, такое как:
 * возможность чтения или записи - TRMDataMapper::STATE_INDEX,
 * имя поля - TRMDataMapper::FIELD_NAME_INDEX,
 * ключемое или нет - TRMDataMapper::KEY_INDEX 
 * и др...
 * @param string $Value - проверяемое значение статуса поля
 * 
 * @return array - возвращает массив с именами полей таблицы $TableName
 *  соответсвуюших условию FieldsState[$StateName] == $Value
 */
public function getAllFieldsNamesForCondition( $StateName = null, $Value = null )
{
    if( null === $StateName )
    {
        return array_keys($this->DataArray);
    }
    
    $FieldsNames = array();
    foreach ( $this->DataArray as $FieldName => $Fileld )
    {
        if( $StateName === TRMDataMapper::STATE_INDEX && $Fileld->State == $Value )
        {
            $FieldsNames[] = $FieldName; continue;
        }
        if( $StateName === TRMDataMapper::FIELDALIAS_INDEX && $Fileld->Alias == $Value )
        {
            $FieldsNames[] = $FieldName; continue;
        }
        if( $StateName === TRMDataMapper::FIELD_NAME_INDEX && $Fileld->Name == $Value )
        {
            $FieldsNames[] = $FieldName; continue;
        }
        if( $StateName === TRMDataMapper::KEY_INDEX && $Fileld->Key == $Value )
        {
            $FieldsNames[] = $FieldName; continue;
        }
        if( $StateName === TRMDataMapper::EXTRA_INDEX && $Fileld->Extra == $Value )
        {
            $FieldsNames[] = $FieldName; continue;
        }
        if( $StateName === TRMDataMapper::NULL_INDEX && $Fileld->Null == $Value )
        {
            $FieldsNames[] = $FieldName; continue;
        }
        if( $StateName === TRMDataMapper::COMMENT_INDEX && $Fileld->Comment == $Value )
        {
            $FieldsNames[] = $FieldName; continue;
        }

    }
    return $FieldsNames;
}


} // TRMObjectMapper


/**
 * расширяет класс TRMObjectMapper для применения к таблицам SQL,
 * может сам генерировать строку с именами полей объекта
 */
class TRMSQLObject extends TRMObjectMapper
{
/**
 * формирует часть SQL-запроса со списком полей, которые выбираются из таблиц
 *
 * @param boolean $AddTableNameFlag - флаг, показывающий, что нужно к именам полей через точку добавлять имя таблицы
 * 
 * @return string - строка со списком полей
 */
private function generateFieldsString( $AddTableNameFlag = true )
{
    if( empty($this->DataArray) )
    {
        return "";
    }
    $FieldStr = "";
    $TableName = empty($this->Alias) ? $this->Name : $this->Alias;
    foreach( $this->DataArray as $FieldName => $Field )
    {
        // если установлен флаг, показывающий, что нужно к именам полей через точку добавлять имя таблицы
        if( $AddTableNameFlag )
        {
            $FieldStr .= "`" . $TableName . "`.";
        }

        if( $Field->Quote == TRMFieldMapper::TRM_FIELD_NEED_QUOTE )
        {
            $FieldStr .= "`" . $FieldName . "`";
        }
        else { $FieldStr .= $FieldName; }

        if( !empty($Field->Alias) )
        {
            $FieldStr .= " AS " . $Field->Alias;
        }
        $FieldStr .= ",";
    }
    return rtrim($FieldStr, ",");
}


} // TRMObjectMapper