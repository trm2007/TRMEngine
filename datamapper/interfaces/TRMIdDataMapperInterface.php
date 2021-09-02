<?php

namespace TRMEngine\DataMapper\Interfaces;

use TRMEngine\DataArray\Interfaces\TRMDataArrayInterface;

interface TRMIdDataMapperInterface extends TRMDataArrayInterface
{
  /**
   * @return array - массив array("имя главного объекта", "имя его ID-поля")
   */
  public function getIdFieldName();

  /**
   * @param array $IdFieldName - массив array("имя главного объекта", "имя его ID-поля")
   */
  public function setIdFieldName(array $IdFieldName);

  /**
   * @param string $LookingObjectName - имя проверяемого объекта
   * @param string $LookingFieldName - имя проверяемого поля на предмет ссылающихся на него других полей
   * 
   * @return array - возвращает массив содержащий ссылающиеся поля на проверяемое поле $LookingObjectName => $LookingFieldName,
   * массив вида array( $ObjectName1 => array(0=>$FieldName1, 1=>$FieldName2, ...), $ObjectName2 => ... )
   */
  public function getBackRelationFor($LookingObjectName, $LookingFieldName);

  /**
   * сортирует порядок объектов в массиве $this->DataArray,
   * таким образом, что сначала идут объекты, на которые есть ссылки, но которые ни на кого не ссылаются,
   * и дальше в такой последоватенльности, 
   * что бы ссылающиеся объекты располагались дальше, чем те, на которые они ссылаются
   */
  public function sortObjectsForRelationOrder();
  /**
   * сортирует порядок объектов в массиве $this->DataArray,
   * таким образом, что бы ссылающиеся объекты располагались раньше, чем те, на которые они ссылаются,
   * обратная сортировка функции sortObjectsForRelationOrder
   */
  public function sortObjectsForReverseRelationOrder();

  /**
   * Как правило в объекте данных один внутренний объект (таблица для случая с БД) играет роль главного,
   * например, товар - главный, а производитель, единица измерения - это вспомогательные объекты,
   * главный объект использует, т.е. ссылается на вспомогательные, 
   * но вспомогательные не могут использовать - ссылаться на главный объект,
   * таких объектов (главных без ссылок на них) может быть несколько,
   * эта функция возвращает массив со всеми именами объектов без обратных ссылок на них
   * 
   * @return array - возвращает массив, содержащий имена объектов, на которые нет ссылок внутри DataMapper
   * @throws TRMDataMapperRelationException - если таких объектов не обнаружится, 
   * то выбрасывается исключение, в данной версии циклические ссылки не допустимы!
   */
  public function getObjectsNamesWithoutBackRelations();
}
