<?php

namespace TRMEngine\DataMapper;

use TRMEngine\DataArray\Interfaces\InitializibleFromArray;
use TRMEngine\DataMapper\TRMDataMapper;

/**
 * Класс для работы с DataMapper одного поля объекта данных,
 * фактически для SQL содержит информацию об одном поле из таблицы,
 *
 * @version 2019-04-27
 */
class TRMFieldMapper implements InitializibleFromArray
{
/** статус поля - доступно только для чтения */
const TRM_FIELD_STATE_READ_ONLY = 512;
/** статус поля - доступно для записи и чтения  */
const TRM_FIELD_STATE_UPDATABLE = 256;

/** имя объекта, на которое ссылается поле в разделе RELATION */
const OBJECT_NAME_INDEX = "ObjectName";
/** имя поля, на которое ссылается другое поле в разделе RELATION */
const FIELD_NAME_INDEX  = "FieldName";

/** константа показывающая, что нужно брать имена полей в кавычки */
const TRM_FIELD_NEED_QUOTE = 32000;
/** константа показывающая, что брать имена полей в кавычки НЕ нужно */
const TRM_FIELD_NOQUOTE = 32001;

/**
 * @var string - имя поля
 */
public $Name;
/**
 * @var int - устанавливает возможность чтения/записи для поля
 */
public $State = self::TRM_FIELD_STATE_READ_ONLY;
/**
 * @var string - тип поля в таблице БД применяемый по умолчанию, если не задан явно
 */
public $Type = "varchar(1024)";
/**
 * @var string - может ли поле оставаться пустым
 */
public $Null = "NO";
/**
 * @var string - указывает хранится ли в этом поле ключ-ID, 
 * принимает значение PRI - перфичный ключ, для совместимости с MySQL
 */
public $Key = "";
/**
 * @var string - значение устанавливаемое по молчанию для этого поля
 */
public $Default = "";
/**
 * @var string - единственное значение, которое я встречал в этом разделе - auto_increment, 
 * может быть полезно в наследуемом классе SQL, для получения значения счетчика последнего добавленного объекта
 */
public $Extra = "";
/**
 * @var string - псевдоним, используемый в запросах для данного поля
 */
public $Alias = "";
/**
 * @var int - показвает нужно ли брать имя данного поля в апосторфы, 
 * по умолчанию нужно TRM_FIELD_NEED_QUOTE
 */
public $Quote = self::TRM_FIELD_NEED_QUOTE;
/**
 * @var string - комментарий к полю, фактически название,
 * может использоваться в качестве <label> к Input-полю в форме на клиенте
 */
public $Comment = "";
/**
 * @var array - массив, 
 * который должен содержать array( OBJECT_NAME_INDEX => имя объекта, FIELD_NAME_INDEX => имя поля в объекте)
 */
public $Relation = array();

public function setRelation($ObjectName, $FieldName)
{
    $this->Relation[self::OBJECT_NAME_INDEX] = $ObjectName;
    $this->Relation[self::FIELD_NAME_INDEX] = $FieldName;
}

public function getRelation()
{
    return $this->Relation;
}

/**
 * устанавливает все свойства поля в значения по умолчанию,
 * имя остается не тронутым
 */
public function setDefaultValue()
{
    $this->State = self::TRM_FIELD_STATE_READ_ONLY;
    $this->Type = "varchar(1024)";
    $this->Null = "NO";
    $this->Key = "";
    $this->Default = "";
    $this->Extra = "";
    $this->Alias = "";
    $this->Quote = TRMFieldMapper::TRM_FIELD_NEED_QUOTE;
    $this->Comment = "";
    $this->Relation = array();
}

/**
 * 
 * @param array $Array - массив из которого будут установлены занчения свойств поля
 * @param boolean $ClearFlag - если этот флаг установлен в TRUE, 
 * то все старые значения перед инициализацией стираются,
 * если нужно сохранить отсутсвующие в $Array свойства со старыми значениями, 
 * то этот флаг нужно установить в FALSE
 */
public function initializeFromArray( array $Array, $ClearFlag = true )
{
    if($ClearFlag)
    {
        $this->setDefaultValue();
    }
    
    if( isset( $Array[TRMDataMapper::STATE_INDEX] ) )
    {
        $this->State = $Array[TRMDataMapper::STATE_INDEX];
    }
    
    if( isset( $Array[TRMDataMapper::TYPE_INDEX] ) )
    {
        $this->Type = $Array[TRMDataMapper::TYPE_INDEX];
    }
    
    if( isset( $Array[TRMDataMapper::NULL_INDEX] ) )
    {
        $this->Null = $Array[TRMDataMapper::NULL_INDEX];
    }
    
    if( isset( $Array[TRMDataMapper::KEY_INDEX] ) )
    {
        $this->Key = $Array[TRMDataMapper::KEY_INDEX];
    }
    
    if( isset( $Array[TRMDataMapper::DEFAULT_INDEX] ) )
    {
        $this->Default = $Array[TRMDataMapper::DEFAULT_INDEX];
    }
    
    if( isset( $Array[TRMDataMapper::EXTRA_INDEX] ) )
    {
        $this->Extra = $Array[TRMDataMapper::EXTRA_INDEX];
    }
    
    if( isset( $Array[TRMDataMapper::ALIAS_INDEX] ) )
    {
        $this->Alias = $Array[TRMDataMapper::ALIAS_INDEX];
    }
    
    if( isset( $Array[TRMDataMapper::QUOTE_INDEX] ) )
    {
        $this->Quote = $Array[TRMDataMapper::QUOTE_INDEX];
    }
    
    if( isset( $Array[TRMDataMapper::COMMENT_INDEX] ) )
    {
        $this->Comment = $Array[TRMDataMapper::COMMENT_INDEX];
    }
    
    if( isset( $Array[TRMDataMapper::RELATION_INDEX] ) )
    {
        $this->setRelation(
                $Array[TRMDataMapper::RELATION_INDEX][TRMDataMapper::OBJECT_NAME_INDEX], 
                $Array[TRMDataMapper::RELATION_INDEX][TRMDataMapper::FIELD_NAME_INDEX]
            );
    }

}


} // TRMField
