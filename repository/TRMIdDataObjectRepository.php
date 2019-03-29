<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;
use TRMEngine\Repository\Interfaces\TRMIdDataObjectRepositoryInterface;

/**
 * класс репозитория, предназначенного для работы с объектом-данных реализующим TRMIdDataObjectInterface
 * т.е. с объектами у которых есть уникальный идентификатор - ID,
 * для таких объектов этот репозиторий создает локальный массив-контейнер, в котором хранятся объекты с Id,
 * и они во всем приложении будут представленны в одном экземпляре!!!
 * getById - вернет ссылку всегда на один и тот же объект, поэтому все зависимости будут рабтать только с одним экземпляром..
 * 
 * @author TRM - 2018-07-28
 */
abstract class TRMIdDataObjectRepository extends TRMRepository implements TRMIdDataObjectRepositoryInterface
{
/**
 * @var array - имя поля, содержащего ID записи
 */
protected $IdFieldName;
/**
 * @var string - имя объекта, в котором есть поле, содержащее ID записи
 */
protected $IdObjectName;

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
 * @return array - array(имя суб-объекта, имя поля) для ID у обрабатываемых данным репозиторием объектов
 */
public function getIdFieldName()
{
    return array( $this->IdObjectName, $this->IdFieldName);
}

/**
 * @param array $IdFieldName - array(имя суб-объекта, имя поля) 
 * для ID у обрабатываемых данным репозиторием объектов
 */
public function setIdFieldName( array $IdFieldName )
{
    $this->IdObjectName = reset($IdFieldName);
    $this->IdFieldName = next($IdFieldName);
    reset($IdFieldName);
}

/**
 * добавляет объект, который обрабатывает этот Repository, в локальный контейнер, 
 * если только у объекта установлен Id
 * 
 * @param TRMIdDataObjectInterface $DataObject - добавляемы объект
 */
private function addIdDataObjectToContainer(TRMIdDataObjectInterface $DataObject)
{
    $id = $DataObject->getId();
    if( null !== $id )
    {
        self::$IdDataObjectContainer[$this->ObjectTypeName][$id] = $DataObject;
    }
}

/**
 * переопределяет getOne для поиска значения ШВ сначала в локальном контейнере объектов данных,
 * если там еще нет объекта по запрашиваемым условиям, то вернется результат запроса из основного хранилища 
 * методом getOne(...) родительского класса
 * 
 * @param string $objectname - имя объекта для поиска по значению
 * @param string $fieldname - поле для поиска по значению
 * @param mixed $value - значение для проверки 
 * @param string $operator - оператор, по которому будет сравниваться значение $value со значением находящимся в поле $fieldname объекта $do
 * 
 * @return TRMIdDataObjectInterface
 */
public function getOne($objectname, $fieldname, $value, $operator = "=")
{
    // если запрос объекта по Id-полю
    if( $objectname === $this->IdFieldName[0] && $fieldname === $this->IdFieldName[1] )
    {
        // проверяем, если объект с таки Id уже есть в локальном массиве, то 
        if( isset( self::$IdDataObjectContainer[$this->ObjectTypeName][$value] ) ) 
        {
            // и вернем его
            return self::$IdDataObjectContainer[$this->ObjectTypeName][$value];
        }
    }
    // будет произведен поиск в постоянном (Persist) хранилище, в данной реализации в БД
    $DataObject = parent::getOne( $objectname, $fieldname, $value, $operator);
    
    // если из БД получить объект не удалось, то getId вернет null
    if( $DataObject === null ) { return null; }
    
    // Если полученный объект уже есть в локальном хранилище, 
    // то нужно вернуть оттуда, 
    // приоритет на стороне клинета, так как в локальном объетке могут быть на записанные изменения,
    // их нельзя тереть
    $id = $DataObject->getId();
    if( null !== $id && isset(self::$IdDataObjectContainer[$this->ObjectTypeName][$id]) )
    {
        return self::$IdDataObjectContainer[$this->ObjectTypeName][$id];
    }
    
    // сохраняем ссылку на текущий объект в локальном массиве
    $this->addIdDataObjectToContainer($DataObject);

    return $DataObject;
}

/**
 * @param array $DataArray - массив с данными, из которых будет создан объект
 * 
 * @return TRMDataObjectInterface - созданный объект данных, который обрабатывает этот экземпляр репозитория
 */
protected function getDataObjectFromDataArray(array $DataArray)
{
    $IdArr = $this->getIdFieldName();
    // проверяем, есть ли данные в поле с ID для данного объекта
    // если это новый объект, то у него нет ID 
    if( isset($DataArray[$IdArr[0]][$IdArr[1]]) )
    {
        // если есть, получаем ID
        $id = $DataArray[$IdArr[0]][$IdArr[1]];
        // если в локальном реползитории уже есть объект с таким Id, то веренем его...
        if( isset( self::$IdDataObjectContainer[$this->ObjectTypeName][$id] ) )
        {
            return self::$IdDataObjectContainer[$this->ObjectTypeName][$id];
        }
    }
    // если не найден в локальном хранилище, то вызываем родительский метод,
    // где будет создан новый объект с эти данными
    $DataObject = parent::getDataObjectFromDataArray($DataArray);
    
    $this->addIdDataObjectToContainer($DataObject);
    
    return $DataObject;
}

/**
 * получает данные объекта из хранилища по ID,
 * никакие условия кроме выборки по ID не срабатывают и удаляются!
 * 
 * @param scalar $id - идентификатор (Id) объекта
 * 
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
 */
public function getById($id)
{
    $IdArr = $this->getIdFieldName();
    return $this->getOneBy( $IdArr[0], $IdArr[1], $id );
}

/**
 * фактически обновляет данные из подготовленной коллекции хранилища
 * в постоянном хранилище,
 * если данных какого объекта из коллекции нет в постоянном хранилище, то добавляет новые,
 * при этом сохраняет новые объекты с ID в локальном хранилище
 */
public function doUpdate()
{
// Не можем вызвать родительский метод, мотому что там очищается коллекция!
//    parent::doUpdate();
    if( !$this->CollectionToUpdate->count() ) { return; }

    $this->DataSource->update( $this->CollectionToUpdate );
    // если были добавлены новые объекты, то у них появился новый ID,
    // проверяем наличие всех обновленных записей на наличие в локальном ID-хранилище 
    foreach( $this->CollectionToUpdate as $CurrentDataObject )
    {
        $id = $CurrentDataObject->getId();
        if( !isset( self::$IdDataObjectContainer[$this->ObjectTypeName][$id] ) )
        {
            self::$IdDataObjectContainer[$this->ObjectTypeName][$id] = $CurrentDataObject;
        }
        
    }
    $this->CollectionToUpdate->clearCollection();
}

/**
 * производите фактичесое удаление данных объетов коллекции из постоянного хранилища DataSource
 * при этом убирает удаляемые объекты по ID из локального хранилища
 */
public function doDelete()
{
    if( !$this->CollectionToUpdate->count() ) { return; }

    // если были добавлены новые объекты, то у них появился новый ID,
    // проверяем наличие всех обновленных записей на наличие в локальном ID-хранилище 
    foreach( $this->CollectionToDelete as $CurrentDataObject )
    {
        $id = $CurrentDataObject->getId();
        if( $id && isset( self::$IdDataObjectContainer[$this->ObjectTypeName][$id] ) )
        {
            unset(self::$IdDataObjectContainer[$this->ObjectTypeName][$id]);
        }
        
    }
    parent::doDelete();
}

} // TRMIdDataObjectRepository
