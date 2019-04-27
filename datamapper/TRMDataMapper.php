<?php

namespace TRMEngine\DataMapper;

use TRMEngine\DataArray\TRMDataArray;
use TRMEngine\DataMapper\Exceptions\TRMDataMapperNotStringFieldNameException;
use TRMEngine\DataMapper\Exceptions\TRMDataMapperRelationException;

/**
 * Класс для объектов DataMapper,
 * сделан из старого TRMSafetyFields, 
 * теперь TRMSafetyFields наследуется от TRMDataMapper
 *
 * @author TRM - 2019-04-27
 */
class TRMDataMapper extends TRMDataArray
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
const FIELDALIAS_INDEX  = "FieldAlias"; // псевдоним, используемый в запросах для данного поля
const QUOTE_INDEX       = "Quote"; // показвает нужно ли брать имя данного поля в апосторфы `
const COMMENT_INDEX     = "Comment"; // комментарий к полю, фактически название на русском языке
const RELATION_INDEX    = "Relation"; // массив с зависимостями по этому полю, привязка к полю из другого объекта
const OBJECT_NAME_INDEX = "ObjectName"; // имя объекта, на которое ссылается поле в разделе RELATION
const FIELD_NAME_INDEX  = "FieldName"; // имя поля, на которое ссылается другое поле в разделе RELATION
const FIELDS_INDEX      = "Fields"; // индекс для массива с полями и их состояниями в объекте

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
 * @return array - $SafetyFieldsArray
 */
public function getFieldsArray()
{
    return $this->DataArray;
}
/**
 * @param array $SafetyFieldsArray
 */
public function setFieldsArray( array $SafetyFieldsArray )
{
    $this->DataArray = array();
    foreach( $SafetyFieldsArray as $ObjectName => $ObjectState )
    {
        $this->setFieldsFor(
            $ObjectName, 
            $ObjectState[TRMDataMapper::FIELDS_INDEX], 
            isset($ObjectState[TRMDataMapper::STATE_INDEX]) ? $ObjectState[TRMDataMapper::STATE_INDEX] : TRMDataMapper::READ_ONLY_FIELD 
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
public function setField( $ObjectName, $FieldName, array $FieldState, $DefaultState = TRMDataMapper::READ_ONLY_FIELD )
{
    $this->DataArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName] = array();
    $this->completeField($ObjectName, $FieldName, $FieldState, $DefaultState);
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
protected function completeField( $ObjectName, $FieldName, array $FieldState, $DefaultState = TRMDataMapper::READ_ONLY_FIELD )
{
    if( !is_string($FieldName) )
    {
        throw new TRMDataMapperNotStringFieldNameException( " [{$FieldName}] " );
    }
    // если для поля еще не установлен массив параметров, создаем как пустой
    if(!isset($this->DataArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName]))
    {
        $this->DataArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName] = array();
    }
    // объединяем переданные параметры и уже существующие для поля, 
    // заменяя старые значения на новый
    $this->DataArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName] = 
            array_merge(
                    $this->DataArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName], 
                    $FieldState
                    );
    // если какой-то из параметров не задан, 
    // то присваиваем ему значение по умолчанию из массива self::$IndexArray
    foreach( self::$IndexArray as $Index => $Value)
    {
        if( isset($this->DataArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName][$Index]) )
        {
            continue;
        }
        if( $Index == TRMDataMapper::STATE_INDEX )
        {
            $this->DataArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName][$Index] = $DefaultState;
        }
        elseif( $Index == TRMDataMapper::COMMENT_INDEX )
        {
            $this->DataArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName][$Index] = $FieldName;
        }
        else
        {
            $this->DataArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName][$Index] = $Value;
        }
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
public function setFieldsFor( $ObjectName, array $Fields, $DefaultState = TRMDataMapper::READ_ONLY_FIELD )
{
    if( !isset($this->DataArray[$ObjectName]) )
    {
        $this->DataArray[$ObjectName] = array( 
            TRMDataMapper::STATE_INDEX => $DefaultState, 
            TRMDataMapper::FIELDS_INDEX => array() 
        );
    }

    foreach( $Fields as $FieldName => $FieldState )
    {
        $this->completeField($ObjectName, $FieldName, $FieldState, $DefaultState);
    }
    $this->rewind();
}

/**
 * убираем поле из массива доступных для любой обработки
 *
 * @param string $ObjectName - имя объекта, из которого удаляется поле, по умолчанию из главной
 * @param string $FieldName - имя поля, которое нужно исключить
 */
public function removeField( $ObjectName, $FieldName )
{
    if( isset($this->DataArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName]) )
    {
        unset($this->DataArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName]);
    }
}

/**
 * убираем раздел связанный с именем объекта из массива полей для обработки
 *
 * @param string $ObjectName - имя объекта, для которого удаляются поля
 */
public function removeFieldsForObject( $ObjectName  )
{
    if( isset($this->DataArray[$ObjectName]) )
    {
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
public function setFieldState( $ObjectName, $FieldName, $State = TRMDataMapper::READ_ONLY_FIELD )
{
    if( isset($this->DataArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName]) )
    {
        $this->DataArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName][TRMDataMapper::STATE_INDEX] = $State;
    }
    else
    {
        $this->setField($ObjectName, $FieldName, array( TRMDataMapper::STATE_INDEX => $State ) );
    }
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
public function getSafetyFieldState( $ObjectName, $FieldName )
{
    if( !isset( $this->DataArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName] ) )
    {
        return null;
    }
    return $this->DataArray[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName][TRMDataMapper::STATE_INDEX];
}

/**
 * @param string $LookingObjectName - имя проверяемого объекта
 * @param string $LookingFieldName - имя проверяемого поля на предмет ссылающихся на него других полей
 * 
 * @return array - возвращает массив содержащий ссылающиеся поля на проверяемое поле $LookingObjectName => $LookingFieldName,
 * массив вида array( $ObjectName1 => array(0=>$FieldName1, 1=>$FieldName2, ...), $ObjectName2 => ... )
 */
public function getBackRelationFor($LookingObjectName, $LookingFieldName)
{
    $FieldsArray = array();
    foreach( $this->DataArray as $ObjectName => $ObjectState )
    {
        foreach( $ObjectState[TRMDataMapper::FIELDS_INDEX] as $FieldName => $FieldState )
        {
            // если у очередного поля есть секция Relatin (RELATION_INDEX)
            // проверяем ссылается ли она на проверяемое поле
            if( isset($FieldState[TRMDataMapper::RELATION_INDEX])
                && $FieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::OBJECT_NAME_INDEX] == $LookingObjectName
                && $FieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::FIELD_NAME_INDEX] == $LookingFieldName
                )
            {
                $FieldsArray[$ObjectName][] = $FieldName;
            }
        }
    }
    return $FieldsArray;
}

/**
 * сортирует порядок объектов в массиве $this->DataArray,
 * таким образом, что сначала идут объекты, на которые есть ссылки, но которые ни на кого не ссылаются,
 * и дальше в такой последоватенльности, 
 * что бы ссылающиеся объекты располагались дальше, чем те, на которые они ссылаются
 */
public function sortObjectsForRelationOrder()
{
    return uksort( $this->DataArray, array($this, "compareTwoTablesRelation") );
}

/**
 * функция для сортировка ключей массива $this->DataArray,
 * т.е. для сортировка по именам таблиц, основываясь на наличии Relation и ссылок одной таблицы на другу,
 * если одна таблица ссылается на другую, значит она больше другой, 
 * и другая должна идти в порядке обработки первее...
 * в данном случае:
 * если из $Table1Name есть ссылка на $Table2Name, то вернется +1, т.е. $Table1Name > $Table2Name
 * если на $Table1Name есть ссылка из $Table2Name, то вернется -1, т.е. $Table1Name < $Table2Name
 * еслии таблицы не связаны друг с другом, то вернется 0,  т.е. $Table1Name == $Table2Name
 * 
 * @param string $Table1Name - первый сравниваемый ключ - имя таблицы 1
 * @param string $Table2Name - второй сравниваемый ключ - имя таблицы 1
 * @return int - 0 - порядок одинаковый, 
 * +1 $Table1Name больше $Table2Name, и $Table2Name должна идти раньше (сортировка по возрастснию),
 * -1 $Table2Name больше $Table1Name, и $Table1Name должна идти раньше
 */
private function compareTwoTablesRelation( $Table1Name, $Table2Name )
{
    // проверяем ссылается ли таблица 1 на таблицу 2
    foreach( $this->DataArray[$Table1Name][TRMDataMapper::FIELDS_INDEX] as $FieldName => $FieldState )
    {
        if( isset($FieldState[TRMDataMapper::RELATION_INDEX]) 
                && $FieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::OBJECT_NAME_INDEX] == $Table2Name
                )
        {
            // число >0, 1-я таблица сссылается на 2-ю, $Table1Name > $Table2Name, 
            // таблица 2 должна обновляться раньше, что бы обновились поля для связи
            // это нужно, например, когда добавляется новая запись с автоинкрементным полем, на которое есть ссылка,
            // перед добавлением записи поле пустое и у ссылающейся таблицы, естественно, тоже!
            // а после добавления мы уже имеем inserted_id и новое значение поля auto_increment,
            // значение которого должны занести в Relation-поле ссылающейся таблицы
            return +1; 
        }
    }
    // если ссылок из Т1 на Т2 не нйдено проверяем наоборот, ссылки из Т2 на Т1
    // проверяем ссылается ли таблица 1 на таблицу 2
    foreach( $this->DataArray[$Table2Name][TRMDataMapper::FIELDS_INDEX] as $FieldName => $FieldState )
    {
        if( isset($FieldState[TRMDataMapper::RELATION_INDEX]) 
                && $FieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::OBJECT_NAME_INDEX] == $Table1Name
                )
        {
            // число <0, 2-я таблица сссылается на 1-ю, $Table1Name < $Table2Name, 
            // таблица 1 должна обновляться раньше, что бы обновились поля для связи
            return -1; 
        }
    }

    // если ничего не найдено, значит таблицы идентичны
    // с точки зрения порядка обнавления
    return 0;
}

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
public function getObjectsNamesWithoutBackRelations()
{
    // получаем все имена объектов внутри SafetyFields
    // меняем ключи со значением местами, 
    // таким образом получаем пустой массив с ключами как у SafetyFieldsArray
    $ObjectsNamesArray = array_flip( array_keys( $this->DataArray ) );
    
    foreach( $this->DataArray as $ObjectState )
    {
        foreach( $ObjectState[TRMDataMapper::FIELDS_INDEX] as $FieldState )
        {
            // если у очередного поля есть секция Relation (ссылка на другое поле другого объекта)
            // то удаляем элемента массива $ObjectsNamesArray с именем объекта, на который идет ссылка
            if( isset($FieldState[TRMDataMapper::RELATION_INDEX]) 
                && isset($ObjectsNamesArray[ $FieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::OBJECT_NAME_INDEX] ]))
            {
                unset($ObjectsNamesArray[ $FieldState[TRMDataMapper::RELATION_INDEX][TRMDataMapper::OBJECT_NAME_INDEX] ]);
                if(empty($ObjectsNamesArray))
                {
                    throw new TRMDataMapperRelationException( __METHOD__ );
                }
            }
        }
        // 
    }

    // возвращаем массив из оставшихся ключей. т.е. из оставшихся имен объектов!!!
    return array_keys($ObjectsNamesArray);
}


} // TRMDataMapper
