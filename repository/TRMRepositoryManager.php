<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\Helpers\TRMLib;
use TRMEngine\Repository\Exceptions\TRMRepositoryGetObjectException;
use TRMEngine\Repository\Interfaces\TRMRepositoryInterface;

/**
 * управляет объектами Repository
 */
class TRMRepositoryManager
{
/**
 * @var array - массив соответствий типов объектов (сущностей) их классам репозиториев (Repository)
 */
protected $RepositoryNameArray = array();


/**
 * устанавливает массив соответсвий типов объектов (сущностей) их калассам хранлищ (Repository) -
 * array( $objectclassname => $repositoryclassname, ... )
 * 
 * @param array $arr - массив соответсвий типов объектов (сущностей) их калассам хранлищ (Repository)
 */
public function setRepositoryNameArray(array $arr)
{
    foreach($arr as $objectclassname => $repositoryclassname)
    {
        $this->addRepositoryName($objectclassname, $repositoryclassname);
    }
}

/**
 * добавляет соответсвующий объект репозитория для объектов класса $objectclassname,
 * если для $objectclassname ранее был установлен рпозиторий, он будет удален и установлен новый!
 * 
 * @param string $objectclassname - имя класса объектов, для которыйх устанавливает репозиторий
 * @param string $repositoryclassname - имя класса объекта Repository
 */
public function addRepositoryName($objectclassname, $repositoryclassname)
{
    if( !class_exists($repositoryclassname) )
    {
        throw new TRMRepositoryGetObjectException( "Не найден класс репозитория {$repositoryclassname} для объектов тип {$objectclassname}!");
    }
    $this->RepositoryNameArray[$objectclassname] = $repositoryclassname;
}

/**
 * Возвращает объект Repository для объектов тип $objectclassname
 * 
 * @param string $objectclassname - имя типа объектов, для которых нужно получить объект хранилища
 * @return TRMRepositoryInterface
 */
public function getRepository($objectclassname)
{
    if( !$objectclassname )
    {
        throw new TRMRepositoryGetObjectException("Неправильно указан тип объектов {$objectclassname}!");
    }
    if( !isset($this->RepositoryNameArray[$objectclassname]) )
    {
        if( !class_exists($objectclassname."Repository") )
        {
            ob_start();
            TRMLib::ap($this->RepositoryNameArray);
            $debinf = ob_get_clean();
            throw new TRMRepositoryGetObjectException( $debinf . "Не найден класс репозитория для объектов тип {$objectclassname}!");
        }
        $this->RepositoryNameArray[$objectclassname] = $objectclassname."Repository";
    }
    return TRMDIContainer::getStatic($this->RepositoryNameArray[$objectclassname], array($objectclassname));
}

/**
 * Возвращает объект Repository для объекта данных $object,
 * 
 * @param TRMDataObjectInterface $object - объект, для которого нужно получить объект хранилища
 * @return TRMRepositoryInterface
 */
public function getRepositoryFor(TRMDataObjectInterface $object)
{
    $r = $this->getRepository( get_class($object) );
    return $r;
}


} // TRMRepositoryManager