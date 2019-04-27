<?php

namespace TRMEngine\TRMDataMapper;

/**
 * Класс для работы с DataMapper одного поля объекта данных,
 * фактически для SQL содержит информацию об одном поле из таблицы,
 *
 * @version 2019-04-27
 */
class TRMField
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
 * @var string - тип поля в таблице БД применяемы поумолчанию, если не задан явно
 */
public $Type = "varchar(1024)";
/**
 * @var string - может ли поле оставаться пустым
 */
public $Null =  "NO";
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
 * может использоваться в качестве <label> к Input-полю в форме на клинте
 */
public $Comment = "";
/**
 * @var array - массив, 
 * который должен содержать array( OBJECT_NAME_INDEX => имя объекта, FIELD_NAME_INDEX => имя поля в объекте)
 */
protected $Relation = array();

public function setRelation($ObjectName, $FieldName)
{
    $this->Relation[self::OBJECT_NAME_INDEX] = $ObjectName;
    $this->Relation[self::FIELD_NAME_INDEX] = $FieldName;
}

public function getRelation()
{
    return $this->Relation;
}


} // TRMField
