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
        TRMDIContainer::getStatic("TRMRepositoryManager")->getRepositoryFor( $DataObject )
                        ->getById( 
                                    $this->DataObjectsContainer->getMainDataObject()
                                        ->getFieldValue( $this->DataObjectsContainer->getDependence($Index) )
                                );
    }
/*    
    $ObjectsArray = $this->DataObjectsContainer->getDependenciesArray();
    // информируем все объекты в контейнере, что получен главный объект из хранилища,
    // и вызываем у каждого свой репозиторий 
    foreach( $ObjectsArray as $Index => $ObjectInfo )
    {
        // получаем объект из репозитория, только если задано связующее поле - это имя поля в главном объекте, 
        // значение которого можно связать с ID текущего (заправшиваемого) объекта
        if( isset($ObjectInfo["RelationFieldName"]) && isset($ObjectInfo["TypeName"]) )
        {
            // устанавливаем объект с именем-индексом - $Index, полученный из соответсвующего репозитория в контейнер данных
            $this->DataObjectsContainer->setDataObject(
                    $Index, 
                    TRMDIContainer::getStatic("TRMRepositoryManager")->getRepository( $ObjectInfo["TypeName"] )
                        ->getById( $this->DataObjectsContainer->getMainDataObject()->getFieldValue($ObjectInfo["RelationFieldName"]) )
                );
        }
    }
 * 
 */

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
/*    
    $ObjectsArray = $this->DataObjectsContainer->getObjectsArray();
    
    // для каждого объекта, сохраненного в массиве ObjectsArray основного объекта-контейнера данных
    // получаем ссылку на свой объект репозитория
    // и выполняем для него Update, тем самым для каждого объекта будет получен свой репозиторий и связан объект,
    // в контейнере можно сохранять объекты одного типа!!!
    // Фактически массив контейнеров не нужен, если можем определить репозиторий для каждого объекта !!!!
    foreach( $ObjectsArray as $Object )
    {
        $rep = TRMDIContainer::getStatic("TRMRepositoryManager")->getRepositoryFor( $Object );
        $rep->update();
    }
 * 
 */
    
    return true;
}

/**
 * удаляет основной объект и все зависимости из контейнера,
 * 
 * @return boolean
 */
public function delete()
{
/*
    $ObjectsArray = $this->DataObjectsContainer->getObjectsArray();
    
    // для каждого объекта, сохраненного в массиве ObjectsArray основного объекта-контейнера данных
    // получаем ссылку на свой объект репозитория
    // и выполняем для него Update, тем самым для каждого объекта будет получен свой репозиторий и связан объект,
    // в контейнере можно сохранять объекты одного типа!!!
    // Фактически массив контейнеров не нужен, если можем определить репозиторий для каждого объекта !!!!
    foreach( $ObjectsArray as $Object )
    {
        $rep = TRMDIContainer::getStatic("TRMRepositoryManager")->getRepositoryFor( $Object );
        $rep->delete();
    }
 * 
 */

    return $this->getMainRepository()->delete();
}


} // TRMRelationRepositories