<?php

namespace TRMEngine\DataMapper;

use TRMEngine\DataArray\TRMDataArray;
use TRMEngine\DataMapper\Exceptions\TRMDataMapperEmptyIdFieldException;
use TRMEngine\DataMapper\Exceptions\TRMDataMapperEmptyMainObjectException;
use TRMEngine\DataMapper\Exceptions\TRMDataMapperNotStringFieldNameException;
use TRMEngine\DataMapper\Exceptions\TRMDataMapperRelationException;
use TRMEngine\DataMapper\Exceptions\TRMDataMapperTooManyMainObjectException;
use TRMEngine\DataMapper\Interfaces\TRMDataMapperInterface;


/**
 * Класс для объектов DataMapper,
 * сделан из старого TRMSafetyFields, 
 * теперь TRMSafetyFields наследуется от TRMDataMapper
 *
 * @author TRM - 2019-04-27
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
 * @var array - массив с ID-полем для "главеого" объекта (на который нет ссылок)
 */
protected $IdFieldName = array();


/**
 * @return array - массив array("имя главного объекта", "имя его ID-поля")
 */
public function getIdFieldName()
{
    if(empty($this->IdFieldName))
    {
        $this->generateIdFieldName();
    }
    return $this->IdFieldName;
}

/**
 * @param array $IdFieldName - массив array("имя главного объекта", "имя его ID-поля")
 */
public function setIdFieldName(array $IdFieldName)
{
    $this->IdFieldName = $IdFieldName;
}

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
public function setFieldsArray( array &$FieldsArray, $DefaultState = TRMDataMapper::READ_ONLY_FIELD )
{
    $this->DataArray = array();
    foreach( $FieldsArray as $ObjectName => $ObjectFieldsArray )
    {
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
public function setField( $ObjectName, $FieldName, array &$FieldState, $DefaultState = TRMDataMapper::READ_ONLY_FIELD )
{
    $this->validateAndCreateObjectField($ObjectName, $FieldName, $DefaultState);
    $Field = $this->DataArray[$ObjectName]->getField( $FieldName ) ;
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
protected function completeField( $ObjectName, $FieldName, array &$FieldState, $DefaultState = TRMDataMapper::READ_ONLY_FIELD )
{
    $this->validateAndCreateObjectField($ObjectName, $FieldName, $DefaultState);
    $Field = $this->DataArray[$ObjectName]->getField( $FieldName );
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
protected function validateAndCreateObjectField( $ObjectName, $FieldName, $DefaultState = TRMDataMapper::READ_ONLY_FIELD )
{
    if( !is_string($ObjectName) )
    {
        throw new TRMDataMapperNotStringFieldNameException( " [{$ObjectName}] " );
    }
    if( !is_string($FieldName) )
    {
        throw new TRMDataMapperNotStringFieldNameException( " [{$FieldName}] " );
    }
    if( !isset($this->DataArray[$ObjectName]) )
    {
        $this->DataArray[$ObjectName] = new TRMObjectMapper();
        $this->DataArray[$ObjectName]->Name = $ObjectName;
    }
    // если для поля еще не установлен объект параметров, создаем новый объект
    if( !$this->DataArray[$ObjectName]->hasField($FieldName) )
    {
        $Field = new TRMFieldMapper($FieldName);
        $Field->State = $DefaultState;
        $this->DataArray[$ObjectName]->setField( $Field ) ;
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
public function setFieldsFor( $ObjectName, array $Fields, $DefaultState = TRMDataMapper::READ_ONLY_FIELD )
{
    if( !isset($this->DataArray[$ObjectName]) )
    {
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
public function setFieldsArrayFor( $ObjectName, array &$Fields, $DefaultState = TRMDataMapper::READ_ONLY_FIELD )
{
    if( !isset($this->DataArray[$ObjectName]) )
    {
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
public function removeField( $ObjectName, $FieldName )
{
    $this->DataArray[$ObjectName]->removeField($FieldName);
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
    if( !isset($this->DataArray[$ObjectName]) )
    {
        $this->DataArray[$ObjectName] = new TRMObjectMapper();
        $this->DataArray[$ObjectName]->Name = $ObjectName;
        $this->DataArray[$ObjectName]->State = $State;
    }
    if( !$this->DataArray[$ObjectName]->hasField($FieldName) )
    {
        $Field = new TRMFieldMapper($FieldName);
        $this->DataArray[$ObjectName]->setField( $Field ) ;
    }
    else
    {
        $Field =  $this->DataArray[$ObjectName]->getField( $FieldName ) ;
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
public function getFieldState( $ObjectName, $FieldName )
{
    if( !isset( $this->DataArray[$ObjectName] ) )
    {
        return null;
    }
    if( !$this->DataArray[$ObjectName]->hasField($FieldName) )
    {
        return null;
    }
    return $this->DataArray[$ObjectName]->getField($FieldName)->State;
}

/**
 * @return array - возвращает массив array(имя объекта, имя поля) 
 * для поля содержащего ID главного объекта, т.е. объекта без обратных ссылок на него
 * 
 * @throws TRMDataMapperEmptyMainObjectException
 * @throws TRMDataMapperTooManyMainObjectException
 * @throws TRMDataMapperEmptyIdFieldException
 */
public function generateIdFieldName()
{
    $this->IdFieldName = array();
    // получаем массив объектов без ссылок на них, т.е. главные объекты
    $MainObjects = $this->getObjectsNamesWithoutBackRelations();
    // если массив пуст или таких объектов больше 1, то выбрасываем исключение
    if( empty($MainObjects) )
    {
        throw new TRMDataMapperEmptyMainObjectException();
    }
    if( count($MainObjects) > 1 )
    {
        throw new TRMDataMapperTooManyMainObjectException();
    }

    $MainObject = $MainObjects[0];
    
    $ObjectsIds = $this->DataArray[$MainObject]->getPriFields();
    if( empty($ObjectsIds ) )
    {
        throw new TRMDataMapperEmptyIdFieldException("Объект-таблица: {$MainObject}...");
    }
    $ObjectId = $ObjectsIds[0];

    // сохраняем информацию об ID для объекта
    $this->IdFieldName = array( $MainObject, $ObjectId );
    return $this->IdFieldName;
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
    foreach( $this->DataArray as $ObjectName => $Object )
    {
        foreach( $Object as $FieldName => $Field )
        {
           // если у очередного поля есть секция Relatin (RELATION_INDEX)
            // проверяем ссылается ли она на проверяемое поле
            if( !empty($Field->Relation) 
                && $Field->Relation[TRMDataMapper::OBJECT_NAME_INDEX] == $LookingObjectName
                && $Field->Relation[TRMDataMapper::FIELD_NAME_INDEX] == $LookingFieldName
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
 * что бы ссылающиеся объекты располагались дальше, чем те, на которые они ссылаются,
 * обратная сортировка функция sortObjectsForReverseRelationOrder
 */
public function sortObjectsForRelationOrder()
{
    return uksort( $this->DataArray, array($this, "compareTwoTablesRelation") );
}

/**
 * сортирует порядок объектов в массиве $this->DataArray,
 * таким образом, что бы ссылающиеся объекты располагались раньше, чем те, на которые они ссылаются,
 * обратная сортировка функции sortObjectsForRelationOrder
 */
public function sortObjectsForReverseRelationOrder()
{
    return uksort( $this->DataArray, array($this, "compareTwoTablesReverseRelation") );
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
 * +1 $Table1Name больше $Table2Name, и $Table2Name должна идти раньше (сортировка по возрастанию),
 * -1 $Table2Name больше $Table1Name, и $Table1Name должна идти раньше
 */
private function compareTwoTablesRelation( $Table1Name, $Table2Name )
{
    // количество ссылок в 1-м объекте
    $Relation1 = 0;
    // количество ссылок во 2-м объекте
    $Relation2 = 0;
    // проверяем ссылается ли таблица 1 на таблицу 2
    foreach( $this->DataArray[$Table1Name] as $FieldName => $Field )
    {
        if( !empty($Field->Relation) )
        {
            $Relation1++;
            if( $Field->Relation[TRMDataMapper::OBJECT_NAME_INDEX] == $Table2Name )
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
    }
    // если ссылок из Т1 на Т2 не найдено, проверяем наоборот, ссылки из Т2 на Т1
    // проверяем ссылается ли таблица 1 на таблицу 2
    foreach( $this->DataArray[$Table2Name] as $FieldName => $Field )
    {
        if( !empty($Field->Relation) )
        {
            $Relation2++;
            if( $Field->Relation[TRMDataMapper::OBJECT_NAME_INDEX] == $Table1Name )
            {
                // число <0, 2-я таблица сссылается на 1-ю, $Table1Name < $Table2Name, 
                // таблица 1 должна обновляться раньше, что бы обновились поля для связи
                return -1; 
            }
        }
    }
    
    // если объекты не ссылаются друг на друга, 
    // то сравнивается кол-во ссылок в одном и другом объекте
    if( $Relation1 > $Relation2 )
    { 
        return +1; 
    }
    if( $Relation1 < $Relation2 )
    { 
        return -1; 
    }

    // если ничего не найдено, значит таблицы идентичны
    // с точки зрения порядка обновления
    return 0;
}
/**
 * функция для сортировка ключей массива $this->DataArray,
 * т.е. для сортировка по именам таблиц, основываясь на наличии Relation и ссылок одной таблицы на другу,
 * если одна таблица ссылается на другую, значит она больше другой, 
 * и другая должна идти в порядке обработки первее...
 * в данном случае:
 * если из $Table1Name есть ссылка на $Table2Name, то вернется -1, т.е. $Table1Name < $Table2Name
 * если на $Table1Name есть ссылка из $Table2Name, то вернется +1, т.е. $Table1Name > $Table2Name
 * еслии таблицы не связаны друг с другом, то вернется 0,  т.е. $Table1Name == $Table2Name
 * 
 * @param string $Table1Name - первый сравниваемый ключ - имя таблицы 1
 * @param string $Table2Name - второй сравниваемый ключ - имя таблицы 1
 * 
 * @return int - 0 - порядок одинаковый, 
 * -1 $Table1Name меньше $Table2Name, и $Table2Name должна идти после (сортировка по возрастанию),
 * +1 $Table2Name меньше $Table1Name, и $Table1Name должна идти после
 */
private function compareTwoTablesReverseRelation( $Table1Name, $Table2Name )
{
    return (-1 * $this->compareTwoTablesRelation($Table1Name, $Table2Name) );
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
    $ObjectsNamesArray = array_flip( $this->getArrayKeys() );
    
    foreach( $this->DataArray as $Object )
    {
        foreach( $Object as $Field )
        {
            // если у очередного поля есть секция Relation (ссылка на другое поле другого объекта)
            // то удаляем элемента массива $ObjectsNamesArray с именем объекта, 
            // на который идет ссылка
            if( !empty($Field->Relation) )
            {
                unset($ObjectsNamesArray[ $Field->Relation[TRMDataMapper::OBJECT_NAME_INDEX] ]);
                if(empty($ObjectsNamesArray))
                {
                    throw new TRMDataMapperRelationException( __METHOD__ );
                }
            }
        }
    }

    // возвращаем массив из оставшихся ключей. т.е. из оставшихся имен объектов!!!
    return array_keys($ObjectsNamesArray);
}


} // TRMDataMapper
