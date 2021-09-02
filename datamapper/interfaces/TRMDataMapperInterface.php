<?php

namespace TRMEngine\DataMapper\Interfaces;

use TRMEngine\DataMapper\TRMDataMapper;

/**
 * Description of TRMDataMapperInterface
 *
 * @author Sergey
 */
interface TRMDataMapperInterface
{
  /**
   * добавляет данные из другого объекта $DataMapper,
   * если в массиве текущего объекта уже есть данные (совпадают индексы)
   * об одном из добавляемых sub-объектов,
   * то они будут заменены на новые из $DataMapper
   * 
   * @param self $DataMapper - добавляемый $DataMapper
   */
  public function addDataMapper(self $DataMapper);

  /**
   * @return array - $SafetyFieldsArray
   */
  public function getFieldsArray();
  /**
   * Формирует DataMapper из массива $FieldsArray, 
   * в котором указана информация для всех полей, всех объектов
   * 
   * @param array $FieldsArray
   * @param int $DefaultState - статус доступа будет установлен по умолчанию, 
   * если не задан для каждого объекта и каждого поля
   */
  public function setFieldsArray(array &$SafetyFieldsArray, $DefaultState);

  /**
   * устанавливает характеристики поля для объекта $ObjectName,
   * если поле было ранее установлено, то данные перезапишутся!!!
   *
   * @param string $ObjectName - имя объекта, для которого добавляется поле
   * @param string $FieldName - имя добавляемого поля
   * @param array $FieldState - массив со свойствами поля array("State", "Type", "Default", "Key", "Extra", "FieldAlias", "Quote", "Comment")
   * @param int $DefaultState - статус поля, 
   * который будет установлен для поля по умолчанию, 
   * если у него явно не задан параметр "State",
   * по умолчанию установлено значение TRMDataMapper::READ_ONLY_FIELD
   */
  public function setField($ObjectName, $FieldName, array &$FieldState, $DefaultState = TRMDataMapper::READ_ONLY_FIELD);

  /**
   * Проверяет есть ли данные для объекта $ObjectName в текущем DataMapper-e
   * 
   * @param string $ObjectName - имя проверяемого объекта объекта
   * @return boolean
   */
  public function hasObject($ObjectName);

  /**
   * добавляет поля доступные для записи/чтения к объекту $ObjectName,
   * устанавливает внутренний счетчик итератора SafetyFields в начало!!!
   *
   * @param string $ObjectName - имя объекта, для которого добавляются поля
   * @param array $Fields - массив массивов array( FieldName => array(State...), ... ), список полей и их параметры, в том числе возможность записи-чтения
   * @param int $DefaultState - статус поля, 
   * который будет установлен для всех элементов массива по умолчанию, 
   * если у них явно не задан параметр "State",
   * по умолчанию установлено значение TRMDataMapper::READ_ONLY_FIELD
   */
  public function setFieldsFor($ObjectName, array $Fields, $DefaultState = TRMDataMapper::READ_ONLY_FIELD);

  /**
   * убираем поле из массива доступных для любой обработки
   *
   * @param string $ObjectName - имя объекта, из которого удаляется поле, по умолчанию из главной
   * @param string $FieldName - имя поля, которое нужно исключить
   */
  public function removeField($ObjectName, $FieldName);

  /**
   * убираем раздел связанный с именем объекта из массива полей для обработки
   *
   * @param string $ObjectName - имя объекта, для которого удаляются поля
   */
  public function removeFieldsForObject($ObjectName);

  /**
   * устанавливает статус поля - доступен для чтений/записи TRMDataMapper::READ_ONLY_FIELD / TRMDataMapper::UPDATABLE_FIELD,
   * или все вместе = TRMDataMapper::FULL_ACCESS_FIELD,
   * менят значение уже присутсвующего в массиве поля,
   * если такого поля у объекта $ObjectName нет, то добавляет новое
   * и устанавливает у него только статус чтения-записи,
   * все остальные свойства поля устанавливаются по умолчанию
   *
   * @param string $ObjectName - имя объекта, для которого устанавливается поле
   * @param string $FieldName - имя поля
   * @param int $State - состояние, по умолчанию = READ_ONLY_FIELD
   */
  public function setFieldState($ObjectName, $FieldName, $State = TRMDataMapper::READ_ONLY_FIELD);

  /**
   * @param string $ObjectName - имя объекта, которому принадлежит поле $FieldName
   * @param string $FieldName - имя поля, для которого нужно получить статус 
   * 
   * @return int|null - возвращает статус поля $FieldName в объекте $ObjectName - доступен для чтений/записи,
   * TRMDataMapper::READ_ONLY_FIELD или 
   * TRMDataMapper::FULL_ACCESS_FIELD или 
   * TRMDataMapper::UPDATABLE_FIELD
   */
  public function getFieldState($ObjectName, $FieldName);
}
