<?php

namespace TRMEngine\Repository\Interfaces;

use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;

/**
 * интерфейс для объектов репозитория, используемых в системе TRMEngine
 */
interface TRMIdDataObjectRepositoryInterface extends TRMRepositoryInterface
{
  /**
   * получает данные объекта из хранилища по ID,
   * никакие условия кроме выборки по ID не срабатывают и удаляются!
   * 
   * @param scalar $id - идентификатор (Id) объекта
   * @param TRMIdDataObjectInterface $DataObject - если задан объект, то новый создаваться не будет,
   * будут заполняться свойства этого объекта
   * 
   * @return TRMIdDataObjectInterface - объект, заполненный данными из хранилища
   */
  public function getById($id, TRMIdDataObjectInterface $DataObject = null);

  /**
   * @return array - array(имя суб-объекта, имя поля) для ID у обрабатываемых данным репозиторием объектов
   */
  public function getIdFieldName();

  /**
   * @param array $IdFieldName - array(имя суб-объекта, имя поля) 
   * для ID у обрабатываемых данным репозиторием объектов
   */
  //public function setIdFieldName( array $IdFieldName );
}
