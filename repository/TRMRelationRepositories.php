<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectsContainerInterface;
use TRMEngine\DiContainer\TRMDIContainer;

/**
 * репозиторий для объекта-контейнера данных,
 * в котором есть главный объект и зависимости,
 * с которыми главный объект связан через их ID.
 * Зависимости только получаются вместе с главным объектом,
 * если они еще не получены.
 * Удаляться и обновляться этим репозиторием они не могут, 
 * так как являются независимыми! и должны это делать самостоятельно...
 */
abstract class TRMRelationRepositories extends TRMDataObjectsContainerRepository 
{

/**
 * Производит выборку главного объекта, удовлетворяющего указанному значению для указанного поля,
 * и поочередно вызывает метод getBy для репозиториев всех зависимых объектов,
 * передавая ссылку в getBy через getDependence().
 * зависимые объекты должны быть наслдениками TRMIdDataObjectInterface
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
    // в родительском parent::getBy получаются данные из хранилища для основной части составного объекта
    if( !parent::getBy( $objectname, $fieldname, $value, $operator ) )
    {
        return null;
    }
    // в цикле получаются все зависимости для главного объекта, 
    // которые связаны и есть в контейнее (массиве зависимостей)
    foreach( $this->DataObjectsContainer as $Index => $DataObject )
    {
        $DependIndex = $this->DataObjectsContainer->getDependence($Index);
        
        TRMDIContainer::getStatic(TRMRepositoryManager::class)->getRepositoryFor( $DataObject )
                        ->getById( $this->DataObjectsContainer->getMainDataObject()
                                        ->getFieldValue( $DependIndex[0], $DependIndex[1] )
                                );
    }

    return $this->DataObjectsContainer;
}

/**
 * обновляет основной объект, без зависимостей!!!
 * зависимости - это отдельные независимые сущности, обновляются отдельно,
 * либо должен использоваться репозиторий TRMEventRepositories с подпиской на события
 * 
 * @return boolean
 */
public function update()
{
    if( !$this->getMainRepository()->update() ) { return false; }
    
    return true;
}

/**
 * удаляет основной объект, без зависимостей!!!
 * зависимости - отдельные независимые сущности, удаляются отдельно,
 * либо должен использоваться репозиторий TRMEventRepositories с подпиской на события
 * 
 * @return boolean
 */
public function delete()
{
    return $this->getMainRepository()->delete();
}


} // TRMRelationRepositories