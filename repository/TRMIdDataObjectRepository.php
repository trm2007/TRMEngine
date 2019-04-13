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
 * @var array(TRMIdDataObjectInterface) - массив объектов, получаемых и создаваемых через данный репозиторий, 
 * ссылки на все объекты хранятся в этом массиве, 
 * и при запросе уже считанного из БД (или другого хранилища) объекта он вернется из массива
 */
protected static $IdDataObjectContainer = array();


/**
 * {@inheritDoc}
 */
public function getIdFieldName()
{
    $type = $this->ObjectTypeName;
    return $type::getIdFieldName();
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
        static::$IdDataObjectContainer[$this->ObjectTypeName][$id] = $DataObject;
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
 * @param TRMDataObjectInterface $DataObject - если задан объект, то новый создаваться не будет,
 * будут заполняться свойства этого объекта
 * 
 * @return TRMIdDataObjectInterface
 */
public function getOneBy($objectname, $fieldname, $value, TRMDataObjectInterface $DataObject = null)
{
    $IdArr = $this->getIdFieldName();
    // если запрос объекта по Id-полю
    if( $objectname === $IdArr[0] && $fieldname === $IdArr[1] )
    {
        // проверяем, если объект с таки Id уже есть в локальном массиве, то 
        if( isset( static::$IdDataObjectContainer[$this->ObjectTypeName][$value] ) ) 
        {
            // вернем его
            return $DataObject = static::$IdDataObjectContainer[$this->ObjectTypeName][$value];
        }
    }
    // будет произведен поиск в постоянном хранилище DataSource, в данной реализации в БД
    $NewDataObject = parent::getOneBy( $objectname, $fieldname, $value, $DataObject);
    
    // если из БД получить объект не удалось, то getId вернет null
    if( $NewDataObject === null ) { return null; }
    
    // Если полученный объект уже есть в локальном хранилище, 
    // то нужно вернуть оттуда, 
    // приоритет на стороне клинета, так как в локальном объетке могут быть на записанные изменения,
    // их нельзя тереть
    $id = $NewDataObject->getId();
    if( null !== $id && isset(static::$IdDataObjectContainer[$this->ObjectTypeName][$id]) )
    {
        return static::$IdDataObjectContainer[$this->ObjectTypeName][$id];
    }
    
    // сохраняем ссылку на текущий объект в локальном массиве
    $this->addIdDataObjectToContainer($NewDataObject);

    return $NewDataObject;
}

/**
 * @param array $DataArray - массив с данными, из которых будет создан объект
 * @param TRMDataObjectInterface $DataObject - если задан объект, то новый создаваться не будет,
 * будут заполняться свойства этого объекта
 * 
 * @return TRMDataObjectInterface - если объект уже присутсвует с таким ID в локальном хранилище, 
 * то вернется он,
 * иначе созданный объект данных, который обрабатывает этот экземпляр репозитория
 */
protected function getDataObjectFromDataArray(array $DataArray, TRMDataObjectInterface $DataObject = null)
{
    $IdArr = $this->getIdFieldName();
    // проверяем, есть ли данные в поле с ID для данного объекта
    // если это новый объект, то у него нет ID 
    if( isset($DataArray[$IdArr[0]][$IdArr[1]]) )
    {
        // если есть, получаем ID
        $id = $DataArray[$IdArr[0]][$IdArr[1]];
        // если в локальном реползитории уже есть объект с таким Id, то веренем его...
        if( isset( static::$IdDataObjectContainer[$this->ObjectTypeName][$id] ) )
        {
            return $DataObject = static::$IdDataObjectContainer[$this->ObjectTypeName][$id];
        }
    }
    // если не найден в локальном хранилище, то вызываем родительский метод,
    // где будет создан новый объект с эти данными
    $NewDataObject = parent::getDataObjectFromDataArray($DataArray, $DataObject);
    
    $this->addIdDataObjectToContainer($NewDataObject);
    
    return $NewDataObject;
}

/**
 * получает данные объекта из хранилища по ID,
 * никакие условия кроме выборки по ID не срабатывают и удаляются!
 * 
 * @param scalar $id - идентификатор (Id) объекта
 * @param TRMDataObjectInterface $DataObject - если задан объект, то новый создаваться не будет,
 * будут заполняться свойства этого объекта
 * 
 * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
 */
public function getById($id, TRMDataObjectInterface $DataObject = null)
{
    $IdArr = $this->getIdFieldName();
    return $this->getOneBy( $IdArr[0], $IdArr[1], $id, $DataObject );
}

/**
 * фактически обновляет данные из подготовленной коллекции хранилища
 * в постоянном хранилище,
 * если данных какого объекта из коллекции нет в постоянном хранилище, то добавляет новые,
 * при этом сохраняет новые объекты с ID в локальном хранилище
 * 
 * @param bool $ClearCollectionFlag - если нужно после обновления сохранить коллекцию обновленных объектов, 
 * то этот флаг следует утсановить в false, это может понадобиться дочерним методам,
 * но перед завершением дочернего doUpdate нужно очистить коллекцию,
 * что бы не повторять обновление в будущем 2 раза!
 * 
 * @return void
 */
public function doUpdate( $ClearCollectionFlag = true )
{
    if( !$this->CollectionToUpdate->count() ) { return; }

    parent::doUpdate( false );
    // если были добавлены новые объекты, то у них появился новый ID,
    // проверяем наличие всех обновленных записей на наличие в локальном ID-хранилище 
    foreach( $this->CollectionToUpdate as $CurrentDataObject )
    {
        $id = $CurrentDataObject->getId();
        if( !isset( static::$IdDataObjectContainer[$this->ObjectTypeName][$id] ) )
        {
            static::$IdDataObjectContainer[$this->ObjectTypeName][$id] = $CurrentDataObject;
        }
        
    }
    if( $ClearCollectionFlag ) { $this->CollectionToUpdate->clearCollection(); }
}

/**
 * производите фактичесое удаление объетов данных коллекции из постоянного хранилища DataSource
 * при этом убирает удаляемые объекты по ID из локального хранилища
 * 
 * @param bool $ClearCollectionFlag - если нужно после удаления сохранить коллекцию удаленных объектов, 
 * то этот флаг следует утсановить в false, это может понадобиться дочерним методам,
 * но перед завершением дочернего doDelete нужно очистить коллекцию,
 * что бы не повторять удаление в будущем 2 раза!
 */
public function doDelete( $ClearCollectionFlag = true )
{
    if( !$this->CollectionToDelete->count() ) { return; }

    // если были добавлены новые объекты, то у них появился новый ID,
    // проверяем наличие всех обновленных записей на наличие в локальном ID-хранилище 
    foreach( $this->CollectionToDelete as $CurrentDataObject )
    {
        $id = $CurrentDataObject->getId();
        if( $id && isset( static::$IdDataObjectContainer[$this->ObjectTypeName][$id] ) )
        {
            unset(static::$IdDataObjectContainer[$this->ObjectTypeName][$id]);
        }
        
    }
    parent::doDelete();
}

} // TRMIdDataObjectRepository
