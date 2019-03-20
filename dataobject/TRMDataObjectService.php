<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;

/**
 * класс для работы с объектами данных, 
 * создание и заполнение данными из JSON, массивов и др.,
 * но не из постоянного хранилища, 
 * для этого нужно использовать соответсвующие объекты Repository
 *
 * @author TRM 2018-07-29
 */
class TRMDataObjectService
{
const DefaultDataObjectType = TRMDataObject::class; //"TRMDataObject";
/**
 * @var TRMDataObjectInterface - ссылка на объект данных, с которым в настоящиее время работает сервис
 */    
protected $CurrentObject = null;
/**
 * @return TRMDataObjectInterface - возвращает ссылку на объект данных, 
 * с которым в настоящиее время работает сервис
 */
function getCurrentObject()
{
    return $this->CurrentObject;
}

/**
 * @param TRMDataObjectInterface $CurrentObject - устанавливает внутреннюю ссылку на объект данных, 
 * с которым в настоящиее время будет работать сервис
 */
function setCurrentObject(TRMDataObjectInterface $CurrentObject)
{
    $this->CurrentObject = $CurrentObject;
}

/**
 * создает объект данных, 
 * если не указан тип, 
 * то создается объект общего для всех объектов данных класса - TRMDataObject,
 * проверяется наследование от базового класса TRMDataObject,
 * если запрашиваемый тип не является наследиком, то объект создан не будет
 * 
 * @param string $type - имя типа создаваемого объекта
 */
public function createDataObject($type = self::DefaultDataObjectType)
{
    if( $type === self::DefaultDataObjectType )
    {
        $DefaultType = self::DefaultDataObjectType;
        return new $DefaultType; // self::DefaultDataObjectType; //
    }
    if( class_exists($type) )
    {
        $ParentArray = class_parents($type);
        if( in_array(self::DefaultDataObjectType, $ParentArray) ) { return new $type; }
    }
    return null;
}

/**
 * декодирует JSON-строку в ассоциативный массив (2-й аргумент json_decode в true)
 * и устанавливает данные этого массива в объект данных
 * 
 * @param string $json - JSON-объект для установки из него данных объекта
 * 
 * @return TRMDataObjectInterface
 */
public function setDataObjectFromJSON($json)
{
    // если не задан объект данных, то создаем простой объект типа TRMDataObject
    if( !isset( $this->CurrentObject ) )
    {
        $this->createDataObject();
    }
    // 2-й аргумент json_decode в true для полусения ассоциативного массива, 
    // в противном случае создается экземпляр stdClass с одноименными свойсивами
    $this->CurrentObject->setOwnData( json_decode($json, true) );
    return $this->CurrentObject;
}

/**
 * формирует JSON-строку из данных объекта
 * 
 * @param TRMDataObjectInterface $do - объект данных, из которого нужно получить JSON
 * @return string - возвращает строку JSON
 */
public function getJSONFromDataObject(TRMDataObjectInterface $do)
{
    return json_encode($do->getOwnData());
}


} // TRMDataObjectService
