<?php

namespace TRMEngine\DataMapper;

use TRMEngine\DataArray\TRMDataArray;
use TRMEngine\DataMapper\Exceptions\TRMDataMapperNotStringFieldNameException;
use TRMEngine\DataMapper\Interfaces\TRMDataMapperInterface;

/**
 * Класс для объектов DataMapper,
 * фактически описывает структуру данных, т.е. структуру комплексного объекта,
 * содержит массивы с описанием полей каждого sub-объекта,
 * входящего в структуру данных,
 * это простая версия DataMapper-а, он ничего не знает об отношениях
 * между объектами
 *
 * @author Sergey Kolesnikov <trm@mail.ru>
 */
class TRMDataMapper extends TRMDataArray implements TRMDataMapperInterface
{
  /**
   * константы для индексов 
   */
  const STATE_INDEX       = "State"; // устанавливает возможность чтения/записи для поля
  const TYPE_INDEX        = "Type"; // тип данных, храняшихся в поле
  const NULL_INDEX        = "Null"; // может ли поле оставаться пустым
  const KEY_INDEX         = "Key"; // указывает хранится ли в этом поле ключ-ID, принимает значение PRI - перфичный ключ, для совместимости с MySQL
  const DEFAULT_INDEX     = "Default"; // значение устанавливаемое по молчанию
  const EXTRA_INDEX       = "Extra"; // единственное значение, которое я встречал в этом разделе - auto_increment, может быть полезно в наследуемом классе SQL, для получения значения счетчика последнего добавленного объекта
  const ALIAS_INDEX       = "Alias"; // индекс для псевдонима
  const FIELDALIAS_INDEX  = "FieldAlias"; // псевдоним, используемый в запросах для данного поля
  const QUOTE_INDEX       = "Quote"; // показвает нужно ли брать имя данного поля в апосторфы `
  const COMMENT_INDEX     = "Comment"; // комментарий к полю, фактически название на русском языке
  const RELATION_INDEX    = "Relation"; // массив с зависимостями по этому полю, привязка к полю из другого объекта
  const OBJECT_NAME_INDEX = "ObjectName"; // имя объекта, на которое ссылается поле в разделе RELATION
  const FIELD_NAME_INDEX  = "FieldName"; // имя поля, на которое ссылается другое поле в разделе RELATION
  const FIELDS_INDEX      = "Fields"; // индекс для массива с полями и их состояниями в объекте

  /** константа показывающая, что нужно брать имена полей в кавычки */
  const NEED_QUOTE = 32000;
  /** константа показывающая, что брать имена полей в кавычки НЕ нужно */
  const NOQUOTE = 32001;

  /**
   * константы определяющие уровень доступа к полям
   */
  const READ_ONLY_FIELD = 512;
  const UPDATABLE_FIELD = 256;
  const FULL_ACCESS_FIELD = 768;

  /**
   * @var array - массив индексов для FieldState и значений для этих параметров по умолчанию
   */
  protected static $IndexArray = array(
    TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD,
    TRMDataMapper::TYPE_INDEX => "varchar(255)",
    TRMDataMapper::DEFAULT_INDEX => "",
    TRMDataMapper::KEY_INDEX => "",
    TRMDataMapper::EXTRA_INDEX => "",
    TRMDataMapper::FIELDALIAS_INDEX => null,
    TRMDataMapper::QUOTE_INDEX => TRMDataMapper::NEED_QUOTE,
    TRMDataMapper::COMMENT_INDEX => "",
    TRMDataMapper::NULL_INDEX => "NO",
    TRMDataMapper::RELATION_INDEX => null,
  );

  /**
   * добавляет данные из другого объекта $DataMapper,
   * если в массиве текущего объекта уже есть данные (совпадают индексы)
   * об одном из добавляемых sub-объектов,
   * то они будут заменены на новые из $DataMapper
   * 
   * @param self $DataMapper - добавляемый $DataMapper
   */
  public function addDataMapper(TRMDataMapperInterface $DataMapper)
  {
    $this->mergeDataArray($DataMapper->DataArray);
  }

  /**
   * @return array - $SafetyFieldsArray
   */
  public function getFieldsArray()
  {
    return $this->DataArray;
  }

  /**
   * Формирует DataMapper из массива $FieldsArray, 
   * в котором указана информация для всех полей, всех объектов
   * 
   * @param array $FieldsArray
   * @param int $DefaultState - статус доступа будет установлен по умолчанию, 
   * если не задан для каждого объекта и каждого поля
   */
  public function setFieldsArray(array &$FieldsArray, $DefaultState = TRMDataMapper::READ_ONLY_FIELD)
  {
    $this->DataArray = array();
    foreach ($FieldsArray as $ObjectName => $ObjectFieldsArray) {
      $this->setFieldsArrayFor(
        $ObjectName,
        $ObjectFieldsArray[TRMDataMapper::FIELDS_INDEX],
        isset($ObjectFieldsArray[TRMDataMapper::STATE_INDEX]) ? $ObjectFieldsArray[TRMDataMapper::STATE_INDEX] : $DefaultState
      );
    }
  }

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
  public function setField($ObjectName, $FieldName, array &$FieldState, $DefaultState = TRMDataMapper::READ_ONLY_FIELD)
  {
    $this->validateAndCreateObjectField($ObjectName, $FieldName, $DefaultState);
    $Field = $this->DataArray[$ObjectName]->getField($FieldName);
    $Field->State = $DefaultState;
    // будет создан новый объект, если каких-т значений не будет в $FieldState,
    // то установятся значения атрибутов поля по умолчанию
    $Field->initializeFromArray($FieldState);
  }

  /**
   * дополняет характеристики поля для объекта $ObjectName,
   * если поле было ранее установлено, то данные перезапишутся, если совпадут ключи,
   * остальные данные останутся нетронутыми!!!
   *
   * @param string $ObjectName - имя объекта, для которого добавляется поле
   * @param string $FieldName - имя добавляемого поля
   * @param array $FieldState - массив со свойствами поля array("State", "Type", "Default", "Key", "Extra", "FieldAlias", "Quote", "Comment")
   * @param int $DefaultState - статус поля, 
   * который будет установлен для поля по умолчанию, 
   * если у него явно не задан параметр "State",
   * по умолчанию установлено значение TRMDataMapper::READ_ONLY_FIELD
   */
  protected function completeField($ObjectName, $FieldName, array &$FieldState, $DefaultState = TRMDataMapper::READ_ONLY_FIELD)
  {
    $this->validateAndCreateObjectField($ObjectName, $FieldName, $DefaultState);
    $Field = $this->DataArray[$ObjectName]->getField($FieldName);
    $Field->State = $DefaultState;
    // второй аргумент - false - означает, что нужно сохранить старые значения, 
    // если их не будет в массиве $FieldState
    $Field->initializeFromArray($FieldState, false);
  }

  /**
   * если нет объекта или поля, то создает новые объекты!!!
   *
   * @param string $ObjectName - имя объекта, для которого добавляется поле
   * @param string $FieldName - имя добавляемого поля
   * @param int $DefaultState - статус поля, 
   * который будет установлен для поля по умолчанию, 
   * если у него явно не задан параметр "State",
   * по умолчанию установлено значение TRMDataMapper::READ_ONLY_FIELD
   * 
   * @return TRMFieldMapper - объект с данными поля TRMFieldMapper
   * @throws TRMDataMapperNotStringFieldNameException
   */
  protected function validateAndCreateObjectField($ObjectName, $FieldName, $DefaultState = TRMDataMapper::READ_ONLY_FIELD)
  {
    if (!is_string($ObjectName)) {
      throw new TRMDataMapperNotStringFieldNameException(" [{$ObjectName}] ");
    }
    if (!is_string($FieldName)) {
      throw new TRMDataMapperNotStringFieldNameException(" [{$FieldName}] ");
    }
    if (!isset($this->DataArray[$ObjectName])) {
      $this->DataArray[$ObjectName] = new TRMObjectMapper();
      $this->DataArray[$ObjectName]->Name = $ObjectName;
    }
    // если для поля еще не установлен объект параметров, создаем новый объект
    if (!$this->DataArray[$ObjectName]->hasField($FieldName)) {
      $Field = new TRMFieldMapper($FieldName);
      $Field->State = $DefaultState;
      $this->DataArray[$ObjectName]->setField($Field);
    }
  }

  /**
   * Проверяет есть ли данные для объекта $ObjectName в текущем DataMapper-e
   * 
   * @param string $ObjectName - имя проверяемого объекта объекта
   * @return boolean
   */
  public function hasObject($ObjectName)
  {
    return $this->keyExists($ObjectName);
  }

  /**
   * добавляет описание поля доступные для записи/чтения к объекту $ObjectName
   *
   * @param string $ObjectName - имя объекта, для которого добавляются поля
   * @param array(TRMFields) $Fields - массив объектов TRMFields
   * @param int $DefaultState - статус поля, 
   * который будет установлен для всех элементов массива по умолчанию, 
   * если у них явно не задан параметр "State",
   * по умолчанию установлено значение TRMDataMapper::READ_ONLY_FIELD
   */
  public function setFieldsFor($ObjectName, array $Fields, $DefaultState = TRMDataMapper::READ_ONLY_FIELD)
  {
    if (!isset($this->DataArray[$ObjectName])) {
      $this->DataArray[$ObjectName] = new TRMObjectMapper();
      $this->DataArray[$ObjectName]->Name = $ObjectName;
      $this->DataArray[$ObjectName]->State = $DefaultState;
    }

    $this->DataArray[$ObjectName]->setFields($Fields);
  }
  /**
   * добавляет описание поля доступные для записи/чтения к объекту $ObjectName
   *
   * @param string $ObjectName - имя объекта, для которого добавляются поля
   * @param array $Fields - массив массивов array( FieldName => array(State...), ... ), список полей и их параметры, в том числе возможность записи-чтения
   * @param int $DefaultState - статус поля, 
   * который будет установлен для всех элементов массива по умолчанию, 
   * если у них явно не задан параметр "State",
   * по умолчанию установлено значение TRMDataMapper::READ_ONLY_FIELD
   */
  public function setFieldsArrayFor($ObjectName, array &$Fields, $DefaultState = TRMDataMapper::READ_ONLY_FIELD)
  {
    if (!isset($this->DataArray[$ObjectName])) {
      $this->DataArray[$ObjectName] = new TRMObjectMapper();
      $this->DataArray[$ObjectName]->Name = $ObjectName;
      $this->DataArray[$ObjectName]->State = $DefaultState;
    }

    $this->DataArray[$ObjectName]->setFieldsArray($Fields);
  }

  /**
   * убираем поле из массива доступных для любой обработки
   *
   * @param string $ObjectName - имя объекта, из которого удаляется поле, по умолчанию из главной
   * @param string $FieldName - имя поля, которое нужно исключить
   */
  public function removeField($ObjectName, $FieldName)
  {
    $this->DataArray[$ObjectName]->removeField($FieldName);
  }

  /**
   * убираем раздел связанный с именем объекта из массива полей для обработки
   *
   * @param string $ObjectName - имя объекта, для которого удаляются поля
   */
  public function removeFieldsForObject($ObjectName)
  {
    if (isset($this->DataArray[$ObjectName])) {
      unset($this->DataArray[$ObjectName]);
    }
  }

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
  public function setFieldState($ObjectName, $FieldName, $State = TRMDataMapper::READ_ONLY_FIELD)
  {
    if (!isset($this->DataArray[$ObjectName])) {
      $this->DataArray[$ObjectName] = new TRMObjectMapper();
      $this->DataArray[$ObjectName]->Name = $ObjectName;
      $this->DataArray[$ObjectName]->State = $State;
    }
    if (!$this->DataArray[$ObjectName]->hasField($FieldName)) {
      $Field = new TRMFieldMapper($FieldName);
      $this->DataArray[$ObjectName]->setField($Field);
    } else {
      $Field =  $this->DataArray[$ObjectName]->getField($FieldName);
    }

    $Field->State = $State;
  }

  /**
   * @param string $ObjectName - имя объекта, которому принадлежит поле $FieldName
   * @param string $FieldName - имя поля, для которого нужно получить статус 
   * 
   * @return int|null - возвращает статус поля $FieldName в объекте $ObjectName - доступен для чтений/записи,
   * TRMDataMapper::READ_ONLY_FIELD или 
   * TRMDataMapper::FULL_ACCESS_FIELD или 
   * TRMDataMapper::UPDATABLE_FIELD
   */
  public function getFieldState($ObjectName, $FieldName)
  {
    if (!isset($this->DataArray[$ObjectName])) {
      return null;
    }
    if (!$this->DataArray[$ObjectName]->hasField($FieldName)) {
      return null;
    }
    return $this->DataArray[$ObjectName]->getField($FieldName)->State;
  }
}
