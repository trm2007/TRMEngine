<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DiContainer\TRMDIContainer;

/**
 * контейнер репозиториев для объектов-контейнеров данных,
 */
abstract class TRMRelationRepositories extends TRMDataObjectsContainerRepository 
{
/**
 * @var array - массив с названиями типов объектов, которые будет хранится в контейнере данных,
 * за получение и обработку которых будет отвечать данный экземпляр контейнера-репозиториев,
 * на данный момент эти данные хранятся в объектах-данных
 */
protected $ObjectTypesArray = array();


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
    if( !parent::getBy($fieldname, $value, $operator) )
    {
        return null;
    }
    
    foreach( $this->DataObjectsContainer as $Index => $DataObject )
    {
        TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor( $DataObject )
                        ->getById( 
                                    $this->DataObjectsContainer->getMainDataObject()
                                        ->getFieldValue( $this->DataObjectsContainer->getDependence($Index) )
                                );
    }

    return $this->DataObjectsContainer;
}

/**
 * обновляет основной объект и все зависимости, 
 * 
 * @return boolean
 */
public function update()
{
    if( !$this->getMainRepository()->update() ) { return false; }
    
    return true;
}

/**
 * удаляет основной объект и все зависимости из контейнера,
 * 
 * @return boolean
 */
public function delete()
{
    return $this->getMainRepository()->delete();
}


} // TRMRelationRepositories