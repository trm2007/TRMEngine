<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;

/**
 * абстрактный класс для объекта данных, которые могут быть сохранены в репозитории-хранилище,
 * так как это данные для одной записи, то у такого объекта может быть ID-идентификатор,
 * а так же возможно обращаться к свойствам объекта через $object->properties (реализованы методы __get и __set)
 *
 * @author TRM

 */
abstract class TRMIdDataObject extends TRMDataObject implements \ArrayAccess, TRMIdDataObjectInterface
{
/**
 * @var array - имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД,
 * должен быть объявлен в каждом дочернем классе!!!
 */
// static protected $IdFieldName;

/**
 * @return array - возвращает имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД,
 * возвращается массив IdFieldName = array( имя объекта, имя ID-поле в объекте )
 */
static public function getIdFieldName()
{
    return static::$IdFieldName;
}

/**
 * @param array $IdFieldName - устанавливает имя свойства для идентификатора объекта, 
 * обычно совпадает с именем ID-поля из БД,
 * передается массив IdFieldName = array( имя объекта, имя ID-поле в объекте )
 */
static public function setIdFieldName( array $IdFieldName ) 
{
    static::$IdFieldName[0] = reset($IdFieldName);
    static::$IdFieldName[1] = next($IdFieldName);
    reset($IdFieldName);
}

/**
 * возвращает значение поля первичного ключа - ID, 
 * для этого первичный ключ должен быт задан в IdFieldName
 *
 * @return mixed|null - ID-объекта
 */
public function getId()
{
    // с 24.03.2019
    // IdFieldName - это массив содержащий array( имя объекта, имя поля )
    if( !isset(static::$IdFieldName[0]) || !isset(static::$IdFieldName[1]) )
    {
        throw new TRMException( __METHOD__ . " - не установлен IdFieldName!");
    }

    $data = $this->getData( static::$IdFieldName[0], static::$IdFieldName[1] );
    
    // проверяем на пустоту, 
    // так как далее приведение этих типов к int будет восприниматься как 0 
    if( false === $data || "" === $data ) { return null; }

    return $data;
}

/**
 * устанавливает значение ID-поля в соответствии с данными из массива IdFieldName!!!
 * имя первичный ключа должено быть задано в IdFieldName
 *
 * @param mixed - ID-объекта
 */
public function setId($id)
{
    $this->setData( static::$IdFieldName[0], static::$IdFieldName[1], $id );
}

/**
 * обнуляет ID-объекта
 * эквивалентен setId(null);
 */
public function resetId()
{
    $this->setData( static::$IdFieldName[0], static::$IdFieldName[1], null );
}


} // TRMIdDataObject