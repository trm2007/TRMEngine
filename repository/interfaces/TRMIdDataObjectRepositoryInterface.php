<?php

namespace TRMEngine\Repository\Interfaces;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;

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
   * @param TRMDataObjectInterface $DataObject - если задан объект, то новый создаваться не будет,
   * будут заполняться свойства этого объекта
   * 
   * @return TRMDataObjectInterface - объект, заполненный данными из хранилища
   */
  public function getById($id, TRMDataObjectInterface $DataObject = null);

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
