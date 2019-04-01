<?php

namespace TRMEngine\DataObject\Interfaces;

/**
 * общий интерфейс для объектов данных
 */
interface TRMDataObjectInterface extends \Countable, \Iterator
{
/**
 * возвращает весь массив с данными, вернется дубликат,
 * так как массив передается по значению ( версия PHP 5.3 ) !!!
 *
 * @return array
 */
public function getDataArray();

/**
 * задает данные для всего массива DataArray, старые данные стираются.
 * пользоваться прямым присвоение следует осторожно,
 * так как передаваться должен двумерный массив, даже состоящий из одной строки!!!
 *
 * @param array $data - массив с данными, в объекте сохранится дубликат массива, 
 * так как массив передается по значению ( версия PHP 5.3 ) !!! 
 */
public function setDataArray( array $data );

/**
 * "склеивает" два массива с данными, проверка на уникальность не проводится,
 * при использовании этого метода нужно быть осторожным с передаваемым массивом, 
 * он должен быть двумерным и каждая запись-строка должна иметь численный индекс
 *
 * @param array $data - массив для склеивания
 */
public function mergeDataArray( array $data );
/**
 * получает данные из конкретной ячейки
 *
 * @parm integer $rownum - номер строки в массиве (таблице) начиная с 0
 * @param string $objectname - имя объекта в строке с номером $rownum, для которого получаются данные
 * @param string $fieldname - имя поля (столбца), из которого производим чтение значения
 *
 * @retrun mixed|null - если нет записи с таким номером строки или нет поля с таким именем вернется null, если есть, то вернет значение
 */
public function getData( $rownum, $objectname, $fieldname );
/**
 * записывает данные в конкретную ячейку
 *
 * @param integer $rownum - номер строки в массиве (таблице) начиная с 0
 * @param string $objectname - имя объекта в строке с номером $rownum, для которого устанавливаются данные
 * @param string $fieldname - имя поля (столбца), в которое производим запись значения
 * @param mixed $value - само записываемое значение
 */
public function setData( $rownum, $objectname, $fieldname, $value );

/**
 * @return array - возвращает данные, характерные только для данного экземпляра
 */
public function getOwnData();
/**
 * устанавливает данные, характерные только для данного экземпляра, 
 * старые значения все удаляются
 * 
 * @param array $data - массив с данными, в объекте сохранится дубликат массива 
 */
public function setOwnData( array $data );
/**
 * проверяет наличие данных в полях с именами из набора $fieldnames в строке с номером $rownum
 *
 * @param integer $rownum - номер строки, в которой происходит проверка, из локального набора данных, отсчет с 0
 * @param string $objectname - имя объекта в строке с номером $rownum, для которого проверяется набор данных
 * @param &array $fieldnames - ссылка на массив с именами проверяемых полей
 *
 * @return boolean - если найдены поля и установлены значения, то возвращается true, иначе false
 */
public function presentDataIn( $rownum, $objectname, array &$fieldnames );

/**
 * меняет во всех записях значение поля $FieldName на новое значение $FieldValue, если разрешена запись
 *
 * @param string $ObjectName - имя объекта, в котором меняется значение 
 * @param string $FieldName - имя поля-колонки
 * @param mixed $FieldValue - новое значение
 */
public function changeAllValuesFor($ObjectName, $FieldName, $FieldValue);

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

/**
 * возврашает значение хранящееся в поле $fieldname объекта $objectname
 * 
 * @param string $objectname - имя объекта, для которого получаются данные
 * @param string $fieldname - имя поля
 * @return mixed|null - если есть значение в поле $fieldname, то вернется его значение, либо null,
 */
public function getFieldValue( $objectname, $fieldname );
/**
 * устанавливает значение в поле $fieldname объекта $objectname
 * 
 * @param string $objectname - имя объекта, для которого получаются данные
 * @param string $fieldname - имя поля
 * @param mixed -  значение, которое должено быть установлено в поле $fieldname объекта $objectname
 */
public function setFieldValue( $objectname, $fieldname, $value );

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
function getParentDataObject();
/**
 * @param TRMIdDataObjectInterface $ParentDataObject - устанавливает объект родителя, 
 */
function setParentDataObject(TRMIdDataObjectInterface $ParentDataObject);

} // TRMParentedDataObjectInterface


/**
 * интерфейс для составных объектов,
 * у которых есть главный объект данных, и коллекция вспомогательных (дочерних)
 */
interface TRMDataObjectsContainerInterface extends TRMIdDataObjectInterface
{
/**
 * @return TRMIdDataObjectInterface - возвращает главный (сохраненный под 0-м номером в массиве) объект данных
 */
public function getMainDataObject();
/**
 * устанавливает главный объект данных,
 * 
 * @param TRMIdDataObjectInterface $do - главный объект данных
 */
public function setMainDataObject(TRMIdDataObjectInterface $do);
/**
 * помещает объект данных в массив под номером $Index, сохраняется только ссылка, объект не клонируется!!!
 * 
 * @param string $Index - номер-индекс, под которым будет сохранен объект в контейнере
 * @param TRMDataObjectInterface $do - добавляемый объект
 */
//public function setDataObject($Index, TRMDataObjectInterface $do);
/**
 * возвращает объект из контейнера под номером $Index
 * 
 * @param integer $Index - номер объекта в контейнере
 * 
 * @return TRMDataObjectInterface - объект из контейнера
 */
public function getDataObject($Index);
/**
 * @return array - возвращает массив объектов данных, дополняющих основной объект
 */
public function getObjectsArray();

/**
 * помещает объект данных с именем $Index в массив-контейнер зависимостей, 
 * сохраняется только ссылка, объект не клонируется!!!
 * 
 * @param string $Index - имя/номер-индекс, под которым будет сохранен объект в контейнере
 * @param TRMIdDataObjectInterface $do - добавляемый объект
 * @param string $ObjectName - имя суб-объекта в главном объекте, по которому связывается зависимость
 * @param string $FieldName - имя поля основного суб-объекта в главном объекте, 
 * по которому установлена связь зависимостью
 */
public function setDependence($Index, TRMIdDataObjectInterface $do, $ObjectName, $FieldName );

/**
 * возвращает объект с именем $Index из массива-контейнера зависимостей
 * 
 * @param string $Index - имя/номер-индекс объекта в контейнере
 * 
 * @return array - имя суб-объекта и поля в суб-объекте главного объекта, 
 * по которому установлена связь с ID зависимости под индексом $Index
 */
public function getDependence($Index);

/**
 * 
 * @param string $Index - индекс обхекта в контейнере
 * @return bool - если объект в контейнере под этим индексом зафиксирован как зависимый от главного,
 * например, список характеристик для товара, то вернется true, если зависимость не утсанвлена, то - false
 */
public function isDependence($Index);

/**
 * возвращает тип главного объекта в контейнере, 
 * именно для этого тип объектов в дальнейшем должен возвращаться IdFieldName
 */
static public function getMainDataObjectType();

} // TRMDataObjectsContainerInterface


interface TRMRelationDataObjectsContainerInterface extends TRMDataObjectsContainerInterface
{

} // TRMRelationDataObjectsContainerInterface
