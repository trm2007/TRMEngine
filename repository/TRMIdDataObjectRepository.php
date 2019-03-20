<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;

/**
 * класс репоpитория, предназначенного для работы с объектом-данных реализующим TRMIdDataObjectInterface
 * т.е. с объектами у которых есть уникальный идентификатор - ID,
 * для таких объектов этот репозиторий создает локальный массив-контейнер, в котором хранятся объекты с Id,
 * и они во всем приложении будут представленны в одном экземпляре!!!
 * getById - вернет ссылку всегда на один и тот же объект, поэтому все зависимости будут рабтать только с одним экземпляром..
 * 
 * @author TRM - 2018-07-28
 */
abstract class TRMIdDataObjectRepository extends TRMRepository
{
/**
 * @var string - имя поля, содержащего ID записи
 */
protected $IdFieldName;
/**
 * @var array(TRMIdDataObjectInterface) - массив объектов, получаемых и создаваемых через данный репозиторий, 
 * ссылки на все объекты хранятся в этом массиве, 
 * и при запросе уже считанного из БД (или другого хранилища) объекта он вернется из массива
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
 * @return string - имя поля, содержащего ID записи
 */
public function getIdFieldName()
{
    return $this->IdFieldName;
}

/**
 * @param string $IdFieldName - имя поля, содержащего ID записи
 */
public function setIdFieldName($IdFieldName)
{
    $this->IdFieldName = $IdFieldName;
}

/**
 * добавляет текущий объект, который обрабатывает этот Repository, в локальный контейнер, 
 * если только у объекта установлен Id
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
 * переопределяет родительский метод, добавляет ссылку на объект в локальный массив, 
 * если только у этого объекта есть Id
 * 
 * @param TRMDataObjectInterface $object - должен быть типа - TRMIdDataObjectInterface
 */
public function setObject(TRMDataObjectInterface $object)
{
    parent::setObject($object);
    $this->addCurrentObjectToContainer();
}

/**
 * проверяет объект $do на наличие нужного значения $value в поле $fieldname
 * 
 * @param TRMIdDataObjectInterface $do - объект с данными для проверки условия
 * @param string $fieldname - поле для поиска по значению
 * @param mixed $value - значение для проверки 
 * @param string $operator - оператор, по которому будет сравниваться значение $value со значением находящимся в поле $fieldname объекта $do
 * 
 * @return boolean - если у объекта поле $fieldname удовлетворяет значению $value по оператору $operator, 
 * то вернется true, иначе false
 */
private function checkDataObject(TRMIdDataObjectInterface $do, $fieldname, $value, $operator)
{
    $res = $do->getFieldValue($fieldname);
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
 * переопределяет getBy для поиска значения сначала в локальном контейнере объектов данных,
 * если там еще нет объекта по запрашиваемым условиям, то вернется результат запроса из основного хранилища 
 * методом getBy(...) родительского класса
 * 
 * @param string $fieldname - поле для поиска по значению
 * @param mixed $value - значение для проверки 
 * @param string $operator - оператор, по которому будет сравниваться значение $value со значением находящимся в поле $fieldname объекта $do
 * @param boolean $getfromdatasourceflag - если этот флаг установлен в true - поумолчанию, то поиск по локальному контейнеру производится не будет,
 * сразу произойдет запрос к основному хранилищу (в данной реализации к БД)
 * 
 * @return TRMIdDataObjectInterface
 */
public function getBy($fieldname, $value, $operator = "=", $getfromdatasourceflag = true)
{
    // если запрос объекта по Id-полю
    if( $fieldname === $this->IdFieldName )
    {
        // проверяем, если объект с таки Id уже есть в локальном массиве, то 
        if( isset( self::$IdDataObjectContainer[$this->ObjectTypeName][$value] ) ) 
        {
            // устанавливаем указательна на найденный объект как на обрабатываемый в данное время
            $this->setObject(self::$IdDataObjectContainer[$this->ObjectTypeName][$value]);
            // и вернет его
            return self::$IdDataObjectContainer[$this->ObjectTypeName][$value];
        }
    }
    // если не установлен флаг брать из источника данных - $getfromdatasourceflag,
    // то пытаемся найти по заданным параметрам в локальном массиве
    elseif( !$getfromdatasourceflag )
    {
        // перебираем все уже хранящиеся в контейнере ссылки на объекты данных
        foreach( self::$IdDataObjectContainer[$this->ObjectTypeName] as $do )
        {
            // если был найден объект с заданными параметрами поля в контейнере, то возвращаем его 
            if( true === $this->checkDataObject($do, $fieldname, $value, $operator) )
            {
                $this->setObject($do);
                return $do;
            }
        }
    }
    // иначе будет произведен поиск по хранилищу, в данной реализации в БД
    // в getBy устанавливается $this->CurrentObject
    if( parent::getBy($fieldname, $value, $operator) === null ) { return null; }
    // сохраняем ссылку на текущий объект в локальном массиве
    $this->addCurrentObjectToContainer();

    return $this->CurrentObject;
}

/**
 * получает данные объекта из хранилища, например из БД
 * 
 * @param integer $id - идентификатор объекта
 * 
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
 */
public function getById($id)
{
    if( is_numeric($id) || preg_match("#^[0-9]+$#", $id) )
    {
        return $this->getBy( $this->getIdFieldName(), (int)$id );
    }
    return null;
}

/**
 * обновляет данные связанного объекта в хранилище,
 * если данных нет в хранилище, то добавляет,
 * при этом устанавливает вновь записанный Id. если он является AUTO_INCREMENT
 * 
 * @return boolean
 */
public function update()
{
    if( false === parent::update() ) { return false; }

    // пытаемся получить LastId, он будет установлен, 
    // если произведено добавление и увеличилось значение AUTO_INCREMENT поля
    //if( ($id = $this->DataSource->getLastId()) )
//    {
        // В алгоритме 01.09.2018 года все ID для автоинкрементных полей устанавливаются автоматом в SQLDataSource
        //$this->CurrentObject->setId( $id );
        // сохраняем ссылку на текущий объект в локальном массиве
        $this->addCurrentObjectToContainer();
//    }
    return true;
}


} // TRMIdDataObjectRepository
