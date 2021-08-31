<?php

namespace TRMEngine\DataObject\Interfaces;

/**
 * интерфейс, который должны реализовывать все объекты данных,
 * у которых есть какой-либо идентификатор, как правило это ID-объекта
 *
 * @author TRM

 */
interface TRMIdDataObjectInterface extends TRMDataObjectInterface
{
  /**
   * @return array - возвращает имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД,
   * возвращается массив IdFieldName = array( имя объекта, имя ID-поле в объекте )
   */
  static public function getIdFieldName();

  /**
   * @param array $IdFieldName - устанавливает имя свойства для идентификатора объекта, 
   * обычно совпадает с именем ID-поля из БД,
   * передается массив IdFieldName = array( имя объекта, имя ID-поле в объекте )
   */
  static public function setIdFieldName(array $IdFieldName);

  /**
   * возвращает для объекта значение идентификатора - Id
   * для этого имя первичного ключа должно быт получено getIdFieldName()
   *
   * @return int|null - ID-объекта
   */
  public function getId();

  /**
   * устанавливает для объекта значение поля первого первичного ключа!!!
   * для этого имя первичного ключа должно быт получено getIdFieldName()
   *
   * @param mixed - ID-объекта
   */
  public function setId($id);

  /**
   * обнуляет ID-объекта
   * эквивалентен setId(null);
   */
  public function resetId();
}
