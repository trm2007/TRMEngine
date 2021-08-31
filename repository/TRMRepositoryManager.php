<?php

namespace TRMEngine\Repository;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DiContainer\TRMDIContainer;
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
  protected $RepositoryNamesArray = array();
  /**
   * @var TRMDIContainer 
   */
  protected $DIC;

  /**
   * @param TRMDIContainer $DIC
   */
  public function __construct(TRMDIContainer $DIC)
  {
    $this->DIC = $DIC;
  }

  /**
   * устанавливает массив соответсвий типов объектов (сущностей) их калассам хранлищ (Repository) -
   * array( $objectclassname => $repositoryclassname, ... )
   * 
   * @param array $arr - массив соответсвий типов объектов (сущностей) их калассам хранлищ (Repository)
   */
  public function setRepositoryNamesArray(array &$arr)
  {
    foreach ($arr as $objectclassname => $repositoryclassname) {
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
    if (!class_exists($repositoryclassname)) {
      throw new TRMRepositoryGetObjectException("Не найден класс репозитория {$repositoryclassname} для объектов тип {$objectclassname}!");
    }
    $this->RepositoryNamesArray[$objectclassname] = $repositoryclassname;
  }

  /**
   * Возвращает объект Repository для объектов тип $objectclassname
   * 
   * @param string $objectclassname - имя типа объектов, для которых нужно получить объект хранилища
   * @return TRMRepositoryInterface
   * 
   * @throws TRMRepositoryGetObjectException
   */
  public function getRepository($objectclassname)
  {
    if (!is_string($objectclassname)) {
      throw new TRMRepositoryGetObjectException("Неправильно указан тип объектов для создания репозитория!");
    }
    if (!isset($this->RepositoryNamesArray[$objectclassname])) {
      throw new TRMRepositoryGetObjectException(" Не указан класс репозитория для объектов типа {$objectclassname}!");
    }

    $Rep = $this->DIC->get($this->RepositoryNamesArray[$objectclassname]);

    if (!$Rep) {
      unset($this->RepositoryNamesArray[$objectclassname]);
      throw new TRMRepositoryGetObjectException("Репозиторий для объектов типа {$objectclassname} создать не удалось!");
    }

    return $Rep;
  }

  /**
   * Возвращает объект Repository для объекта данных $object,
   * 
   * @param TRMDataObjectInterface $object - объект, для которого нужно получить объект хранилища
   * @return TRMRepositoryInterface
   */
  public function getRepositoryFor(TRMDataObjectInterface $object)
  {
    $r = $this->getRepository(get_class($object));
    return $r;
  }
}
