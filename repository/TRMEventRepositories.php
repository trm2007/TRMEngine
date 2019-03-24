<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\EventObserver\TRMCommonEvent;
use TRMEngine\EventObserver\TRMEventManager;
use TRMEngine\Repository\TRMDataObjectsContainerRepository;

/**
 * контейнер репозиториев для объектов-контейнеров данных,
 * работает на основе механизма оповещения, 
 * все репозитории, которые зависят от главного объекта 
 * (его нужно сохранять первым - в нулевой элемент массива-контейнера),
 * должны подписываться на события
 */
abstract class TRMEventRepositories extends TRMDataObjectsContainerRepository
{
/**
 * @var string - имя события, которое генерируется репозиторием при получении объетка
 */
protected $GetEventName = "";
/**
 * @var string - имя события, которое генерируется репозиторием при обновлении объетка
 */
protected $UpdateEventName = "";
/**
 * @var string - имя события, которое генерируется репозиторием при удалении объетка
 */
protected $DeleteEventName = "";
/**
 * @var array - массив с репозиторями для каждого доп.объекта в составном объекте-контейнере
 */
protected $RepositoriesArray = array();


/**
 * при создании конструктор дочеренего объекта должен передать имена событий, 
 * которые будут генерироваться при наступлении 3-х событий - получение/обновление/удаление
 * 
 * @param string $objectclassname - имя класса для объектов, за которые отвечает этот Repository
 * @param string $GetEventName - имя события, которое генерируется репозиторием при получении объетка
 * @param string $UpdateEventName - имя события, которое генерируется репозиторием при обновлении объетка
 * @param string $DeleteEventName - имя события, которое генерируется репозиторием при удалении объетка
 */
public function __construct($objectclassname, $GetEventName, $UpdateEventName, $DeleteEventName)
{
    parent::__construct($objectclassname);
    $this->GetEventName = $GetEventName;
    $this->UpdateEventName = $UpdateEventName;
    $this->DeleteEventName = $DeleteEventName;
}

/**
 * задает текущий объект, с которым будет работать репозиторий, 
 * только ссылка, объект не копируется и все изменения, если произойдет чтение объекта из БД, будут в основном объекте,
 * при этом весь массив доп. репозиториев перестраивается под доп.объекты нового основного объекта!
 * ВНИМАНИЕ! 
 * На момент реализации версии 2018-08-20 нельзя допускать что бы в контейнере были два объекта одинакового типа,
 * подписчик на события обновит данные только в последнем добавленном объекте для двух одинаковых!!!
 * 
 * @param TRMDataObjectInterface $DataObjectsContainer - текущий объект, с которым будет работать репозиторий,
 * должен быть типа - TRMDataObjectsContainerInterface
 */
public function setObject(TRMDataObjectInterface $DataObjectsContainer)
{
    parent::setObject($DataObjectsContainer);
    // при инициализации объектf должны быть созданы все репозитории для дочерних объектов,
    // так как они могут прослушивать события, отправляемые данным репозиторием о получении, 
    // удалении или обновлении всего контейнера
    $this->setRepositoryArrayForContainer();
}

/**
 * весь массив доп. репозиториев перестраивается под доп.объекты нового основного объекта
 */
protected function setRepositoryArrayForContainer()
{
    $this->RepositoriesArray = array();

    foreach( $this->DataObjectsContainer as $DataObject )
    {
        // получаем репозиторий для текущего объекта...
        $rep = TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor($DataObject);
        // устанавливаем текущий объект для полученного репозитория
        $rep->setObject($DataObject);
        // добавляем полученный объект в массив (добавляется только ссылка, поэтому объекты одинаковых типов не могут использоваться в одном составном объекте товара или др.)
        $this->RepositoriesArray[] = $rep;
    }
}

/**
 * Производит выборку главного объекта, удовлетворяющего указанному значению для указанного поля,
 * и оповещает всех подписчиков, что получен новый объект, 
 * передавая ссылку на него через стандартное событие TRMCommonEvent
 * 
 * @param string $fieldname - поле, в котором выбираются значения
 * @param mixed $value - значение для сравнения и поиска
 * @param string $operator - =, > , < , != , LIKE, IN и т.д., поумолчанию "="
 * 
 * @return TRMDataObjectsContainerInterface - объект-контейнер, заполненный данными из хранилища
 */
public function getBy($fieldname, $value, $operator = "=")
{
    // в родительском parent::getBy получаются данные из хранилища для основной части составного объекта
    parent::getBy($fieldname, $value, $operator);

    if( !empty($this->GetEventName) )
    {
        // информируем всех наблюдателей, что получен главный объект из хранилища
        TRMDIContainer::getStatic(TRMEventManager::class)->notifyObservers(
                new TRMCommonEvent( // создается объект события
                        $this, // передаем ссылку на инициатора события, т.е. на себя
                        $this->GetEventName // тип события (его имя)
                    )
                );
    }

    return $this->DataObjectsContainer;
}

/**
 * обновляет объект товара и все зависимости в БД, 
 * если они подписаны на событие updateComplexProductDBEvent
 * 
 * @return boolean
 */
public function update()
{
    if( !$this->getMainRepository()->update() ) { return false; }

    if( !empty($this->UpdateEventName) )
    {
        // информируем всех наблюдателей, что обновлен  объект товара из БД - событие deleteComplexProductDBEvent
        TRMDIContainer::getStatic(TRMEventManager::class)->notifyObservers(
                new TRMCommonEvent( // создается объект события
                        $this, // передаем ссылку на инициатора события, т.е. на себя
                        $this->UpdateEventName // тип события (его имя)
                    )
                );
    }
    return true;
}

/**
 * удаляет объект товара и оповещает все зависимости из контейнера,
 * вызывает событие deleteComplexProductDBEvent
 * @return boolean
 */
public function delete()
{
    if( !empty($this->DeleteEventName) )
    {
        // информируем всех наблюдателей, что объект будет удален из БД - событие deleteComplexProductDBEvent
        TRMDIContainer::getStatic(TRMEventManager::class)->notifyObservers(
                new TRMCommonEvent( // создается объект события
                        $this, // передаем ссылку на инициатора события, т.е. на себя
                        $this->DeleteEventName // тип события (его имя)
                    )
                );
    }

    return $this->getMainRepository()->delete();
}


} // TRMEventRepositoiesContainer