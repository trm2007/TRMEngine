<?php

namespace TRMEngine\DataObject\Interfaces;

use TRMEngine\DataArray\Interfaces\TRMDataArrayInterface;

/**
 * общий интерфейс для объектов данных
 */
interface TRMDataObjectInterface extends TRMDataArrayInterface
{
  /**
   * проверяет наличие поля с именем fieldname в sub-объекте $objectname
   * 
   * @param string $objectname - имя sub-объекта, для которого проверяется наличие поля $fieldname
   * @param string $fieldname - имя искомого поля
   * 
   * @return bool - если найден, возвращает true, если ключ отсутствует - false
   */
  public function fieldExists($objectname, $fieldname);

  /**
   * получает данные из конкретной ячейки
   *
   * @param string $objectname - имя sub-объекта в строке с номером $rownum, для которого получаются данные
   * @param string $fieldname - имя поля (столбца), из которого производим чтение значения
   *
   * @retrun mixed|null - если нет записи с таким номером строки или нет поля с таким именем вернется null, если есть, то вернет значение
   */
  public function getData($objectname, $fieldname);
  /**
   * записывает данные в конкретную ячейку
   *
   * @param string $objectname - имя sub-объекта в строке с номером $rownum, для которого устанавливаются данные
   * @param string $fieldname - имя поля (столбца), в которое производим запись значения
   * @param mixed $value - само записываемое значение
   */
  public function setData($objectname, $fieldname, $value);

  /**
   * проверяет наличие данных в полях с именами из набора $fieldnames 
   * в sub-объекте $objectname
   *
   * @param string $objectname - имя sub-объекта, для которого проверяется набор данных
   * @param &array $fieldnames - ссылка на массив с именами проверяемых полей
   *
   * @return bool - если найдены поля и установлены значения, то возвращается true, иначе false
   */
  public function presentDataIn($objectname, array &$fieldnames);
}
