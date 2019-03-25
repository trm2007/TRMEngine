<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DataObject\TRMDataObjectsContainer;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\Repository\Interfaces\TRMRepositoryInterface;

/**
 * общий класс для контейнера репозиториев работающих с объектами в контейнере данных,
 *
 * @author TRM
 */
abstract class TRMDataObjectsContainerRepository implements TRMRepositoryInterface
{
/**
 * @var string - имя типа данных, с которыми работает данный экземпляр класса Repository
 */
protected $ObjectTypeName = TRMDataObjectsContainer::class;
/**`
 * @var TRMDataObjectsContainerInterface - контейнер объектов данных
 */
protected $DataObjectsContainer;


/**
 * @param string $objectclassname - имя класса для объектов, за которые отвечает этот контейнер репозиториев
 */
public function __construct($objectclassname)
{
    $this->ObjectTypeName = (string)$objectclassname;
}

/**
 * 
 * @return TRMIdDataObjectRepository - возвращает объект (точнее ссылку) на репозиторий для главного объекта
 */
public function getMainRepository()
{
    return TRMDIContainer::getStatic(TRMRepositoryManager::class)
            ->getRepositoryFor( $this->DataObjectsContainer->getMainDataObject() );
}

/**
 * Возвращает ссылку на текущий контейнер объектов, с которым работает Repository
 * 
 * @return TRMDataObjectsContainerInterface
 */
public function getObject()
{
    return $this->DataObjectsContainer;
}

/**
 * задает текущий объект, с которым будет работать репозиторий, 
 * только ссылка, объект не копируется и все изменения, если произойдет чтение объекта из БД, будут в основном объекте,
 * при этом весь массив доп. репозиториев перестраивается под доп.объекты нового основного объекта!
 * 
 * @param TRMDataObjectInterface $DataObjectsContainer - текущий объект, с которым будет работать репозиторий, должен быть типа - TRMDataObjectsContainerInterface
 */
public function setObject(TRMDataObjectInterface $DataObjectsContainer)
{
    $this->DataObjectsContainer = $DataObjectsContainer;
}

/**
 * обнуляет указательна на объект данных, сам объект не изменяяется, рвется только связь с репозиторием!!!
 */
public function unlinkObject()
{
    $this->DataObjectsContainer = null;
}

/**
 * Производит выборку главного объекта, удовлетворяющего указанному значению для указанного поля,
 * 
 * @param string $objectname - имя объекта для поиска по значению поля
 * @param string $fieldname - поле, в котором выбираются значения
 * @param mixed $value - значение для сравнения и поиска
 * @param string $operator - =, > , < , != , LIKE, IN и т.д., поумолчанию "="
 * 
 * @return TRMDataObjectsContainerInterface - объект-контейнер, заполненный данными из хранилища
 */
public function getBy( $objectname, $fieldname, $value, $operator = "=" )
{
    // если объект контейнера данных еще не ассоциирован с этим репозиторием,
    // то создаем новый и работаем с ним
    if( !$this->DataObjectsContainer )
    {
        $this->setObject(new $this->ObjectTypeName);
    }

    // получаем основные данные для главной части составного объекта
    // без главного объекта нет смысла продолжать работу, поэтому проверям, 
    // что он получен родительским getBy,
    // там же вызывается метод setObject, который связывает все зависимости
    $this->getMainRepository()->getBy( $objectname, $fieldname, $value, $operator );

    return $this->DataObjectsContainer;
}

/**
 * сохраняет составной объект с главным объектом и вспомогательными в виде коллекции
 * 
 * @param TRMDataObjectInterface $object - сохраняемый объект, на самом деле должен быть тип TRMDataObjectsContainerInterface
 * будет установлен как текущий объект обрабатываемырепозиторием
 */
public function save(TRMDataObjectInterface $object = null)
{
    if( null !== $object )
    {
        $this->setObject($object);
    }
    if( null === $this->DataObjectsContainer )
    {
        throw new Exception( "Не установлен объект с данными в репозитории " . get_class($this) );
    }
    return $this->update();
}


} // TRMRepositoiesContainer