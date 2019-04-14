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
 * @return boolean - если найден, возвращает true, если ключ отсутствует - false
 */
public function fieldExists( $objectname, $fieldname );

/**
 * получает данные из конкретной ячейки
 *
 * @param string $objectname - имя sub-объекта в строке с номером $rownum, для которого получаются данные
 * @param string $fieldname - имя поля (столбца), из которого производим чтение значения
 *
 * @retrun mixed|null - если нет записи с таким номером строки или нет поля с таким именем вернется null, если есть, то вернет значение
 */
public function getData( $objectname, $fieldname );
/**
 * записывает данные в конкретную ячейку
 *
 * @param string $objectname - имя sub-объекта в строке с номером $rownum, для которого устанавливаются данные
 * @param string $fieldname - имя поля (столбца), в которое производим запись значения
 * @param mixed $value - само записываемое значение
 */
public function setData( $objectname, $fieldname, $value );

/**
 * проверяет наличие данных в полях с именами из набора $fieldnames в строке с номером $rownum
 *
 * @param string $objectname - имя sub-объекта, для которого проверяется набор данных
 * @param &array $fieldnames - ссылка на массив с именами проверяемых полей
 *
 * @return boolean - если найдены поля и установлены значения, то возвращается true, иначе false
 */
public function presentDataIn( $objectname, array &$fieldnames );


} // TRMDataObjectInterface


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
static public function setIdFieldName( array $IdFieldName ) ;

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

} // TRMIdDataObjectInterface


/**
 * интерфейс для объектов данных, у которых есть родитель (обычно в свойствах есть ссылка на объект родителя),
 * например, у объекта товара может быть ссылка на группу,
 * у коллекции изображений ссылка на товар, к которому он принадлежит и т.д...
 */
interface TRMParentedDataObjectInterface extends TRMDataObjectInterface
{
/**
 * @return array - имя свойства внутри объекта содержащего Id родителя
 */
static public function getParentIdFieldName();
/**
 * @param array $ParentIdFieldName - имя свойства внутри объекта содержащего Id родителя
 */
static public function setParentIdFieldName(array $ParentIdFieldName);
/**
 * @return TRMIdDataObjectInterface - возвращает объект родителя
 */
public function getParentDataObject();
/**
 * @param TRMIdDataObjectInterface $ParentDataObject - устанавливает объект родителя, 
 */
public function setParentDataObject(TRMIdDataObjectInterface $ParentDataObject);

} // TRMParentedDataObjectInterface
