<?php

namespace TRMEngine\XMLParser;

use TRMEngine\File\TRMFile;
use TRMEngine\Helpers\TRMLib;

/**
 * Простой класс для разбора XML и формирования SQL-запросов для записи данных в БД
 */
class TRMXMLToSQLParser
{
const TRM_XML_OUTPUT_FILE_CHARSET = "UTF-8";
/**
 * размер максимального блока для считывания и записи в файл (в байтах)
 */
const TRM_XML_MAX_BLOCK_SIZE = 65535; // 32767;

/**
 * максимальный размер выходного файла, 
 */
const TRM_MAX_OUTPUT_FILE_SIZE = 25165824; // 41943040;
/**
 * показывает, что название данного тега приравнивается к названию таблицы в БД
 */
const TRM_XML_STATUS_TABLE = 1;
/**
 * показывает, что название данного тега приравнивается к записе, данные внутри этого тега могут содержать данные из нескольких таблиц...
 */
const TRM_XML_STATUS_ENTITY = 2;
/**
 * показывает, что данный тег является названием столбца-поля в БД, и информация внутри него - данные для записи в таблицу...
 */
const TRM_XML_STATUS_FIELD = 4;
/**
 * показывает, что данный тег является значением индексеого поля в БД, а информация внутри него, как правило, название какой-то характеристики...
 */
const TRM_XML_STATUS_INDEX = 8;
/**
 * показывает, что данные именно для этого тега записаны не будут, но вложенные теги и атрибуты имеют свои статусы записи!
 */
const TRM_XML_STATUS_NOTSAVE = 128;
/**
 * режим обновления данных в таблицах - обычная вставка, вызовет ошибку при дубликате
 */
const TRM_SQL_MODE_INSERT = 1;
/**
 * режим обновления данных в таблицах - вставка с игнорированием при дубликате
 */
const TRM_SQL_MODE_INSERT_IGNORE = 2;
/**
 * режим обновления данных в таблицах - вставка с обнолвнием данных при дубликате, 
 */
const TRM_SQL_MODE_INSERT_ODKU = 4;
/**
 * режим обновления данных в таблицах - замена записи на номую при дубликате, 
 * возможно изменение AUTO_INCREMET индекса, если дублирование не по нему, а по другому ключевому полю!!!
 */
const TRM_SQL_MODE_REPLACE = 8;

/**
 * @var string - имя функции, которая вызывается после достижения результирующим файлом указанного размера = TRM_MAX_OUTPUT_FILE_SIZE
 * потом создается новый файл нулевой длины
 */
protected $CallBack = null;
/**
 * @var string - путь к XML-файлу
 */
protected $XMLFile;
/**
 * @var TRMFile - объект для работы с фалом, в который будут записываться SQL-запросы
 */
protected $SQLFile;
/**
 * @var string - начальное имя файла для сохранения результат - SQL-запросов 
 */
protected $SQLFileName = "";
/**
 * @var string - начальное имя файла для источника XML-данных
 */
protected $XMLFileName = "";
/**
 * @var array - настройки работы с XML и cоответсвующей БД
 */
protected $config = array();
/**
 * @var array - массив с именами тегов XML, 
 * так же их статусом и другой информацией 
 * array( array( "tagname", "tagstatus", "tablename", "fieldname" ... ), ... )
 */
protected $Tags = array();
/**
 * @var string - хранит имя поля , для которого нужно собирать данные, как для Entity
 */
private $Transaction = "";
/**
 * @var string - имя текущего обрабатываемого поля, используется в функции обработки данных data()
 */
private $CurrentTagName = "";
/**
 * @var string - имя последнего обработанного тега
 */
private $LastTagName = "";
/**
 * @var integer - количество вызово обработчика данных для текущего блока
 */
private $DataCount = 0;
/**
 * @var array - данные для текущей транзакции, 
 * в массив собираются все поля и их значения для текущей Entity
 */
private $TransactionData = array();
/**
 * @var array - данные для текущего сбора зависимостей по индексам
 */
private $IndexData = array();
/**
 * @var mixed - текущее значение обрабатываемого индексного тега из XML
 */
private $CurrentIndexValue = null;
/**
 * @var integer - значение индекса в массиве Tags для текущего XML-тега, 
 * если для тега не установлены настироки, отсутсвует в массиве Tags, $CurrentTagIndex принимает значение -1
 */
private $CurrentTagIndex = -1;
/**
 * @var string - кодировка, в которой сохранен XML-файл
 */
private $XMLCharset = "WINDOWS-1251";
/**
 * @var array - массив с шаблонами и соответсвие полям-тегам
 */
private $template = array();
/**
 * @var integer - текущий размер обработанной части XML-файла
 */
public $CurrentSize;
/**
 * @var integer - если размер выходного файла превышает TRM_MAX_OUTPUT_FILE_SIZE (40 МБ), 
 * то он разбивается на несколько частей,
 * и каждый следующий записывается с добавлением номера увеличинного на единицу
 */
public $CurrentOutputFileNumber = 0;
/**
 * @var integer - полный размер XML-файла
 */
public $XMLSize;
/**
 * @var string - тип вставки или замены, может быть 1 - вставка, или 0 - замена
 */
protected $InsertMode = 1;


public function __construct()
{
    $this->config = array(
        "RecreateTables" => false, // true - удаляем и создаем таблицу заново, false - оставляем, если уже есть
        "RecreateFields" => false, // true - удаляем и создаем поле заново, false - оставляем, если уже есть
        "RewriteIndexData" => true, // true - перезаписываем данные в поле записи, связанной с индексом, на новыео, false - оставляем как есть
    );
}

/**
 * @param type $funcname - имя функции, которая вызывается после достижения результирующим файлом указанного размера = TRM_MAX_OUTPUT_FILE_SIZE
 * потом создается новый файл нулевой длины, при вызове в функцию из скрипта передается один аргумент - имя обработанного файла
 */
public function setCallBack( $funcname )
{
    if(is_callable($funcname) )
    {
        $this->CallBack = $funcname;
    }
}

/**
 * устанавливает режим вставки записей в БД как замена - REPLACE INTO ...
 */
public function setReplaceMode()
{
    $this->InsertMode = self::TRM_SQL_MODE_REPLACE;
}

/**
 * устанавливает режим вставки записей в БД 
 * если $mode не задан, или значение указано неверно,
 * то устанавливается режим как вставка с заменой при дубликате - INSERT INTO ... ON DUPLICATE KEY UPDATE
 */
public function setInsertMode( $mode = 0 )
{
    switch ($mode)
    {
        case self::TRM_SQL_MODE_INSERT : 
        case self::TRM_SQL_MODE_INSERT_IGNORE : 
        case self::TRM_SQL_MODE_INSERT_ODKU : 
        case self::TRM_SQL_MODE_REPLACE : 
            $this->InsertMode = $mode;
            break;
        default : 
            $this->InsertMode = self::TRM_SQL_MODE_INSERT_ODKU;
            break;
    }
}

/**
 * добавляет соответствие шаблона XML-тегу
 * 
 * @param string $pattern - шаблон - регулярнок выражение
 * @param string $tagname - соответствующее имя тега
 * @param string $entityname - название записи для этого тега
 */
public function addTemplate($pattern, $tagname, $entityname)
{
    $this->template[] = array( "pattern" => $pattern, 
                                "tagname" => strtoupper($tagname), 
                                "entityname" => strtoupper($entityname) );
}

/**
 * очистка массива template соответсвий шаблонов XML-тегам
 */
public function clearTemplate()
{
    unset($this->template);
    $this->template = array();
}

/**
 * @param string $charset - кодировка XML-файла
 */
public function setXMLCharset($charset)
{
    $this->XMLCharset = strtoupper($charset);
}

/**
 * ставит в соответсвие названию тега - $tagname название таблицы из БД - $tablename
 * 
 * @param string $tagname - имя тега из XML-документа
 * @param string $tablename - имя таблицы
 */
public function addTableName($tagname, $tablename)
{
    $this->Tags[] = array( "tagstatus" => static::TRM_XML_STATUS_TABLE,
                                   "tagname" => strtoupper($tagname),
                                   "entityname" => "",
                                   "tablename" => $tablename );
}

/**
 * ставит в соответсвие названию тега - $tagname название поля $fieldname из таблицы БД - $tablename
 * 
 * @param string $tagname - имя тега из XML-документа
 * @param string $entityname - имя XML-секции-тега, к которому относится данное поле
 * @param string $datafieldname - имя поля в таблице БД
 * @param string $tablename - имя таблицы БД
 * @param string $fieldtype - тип данных в БД
 * @param string $callback - имя функции, которая вызывается с параметрами ($name, $data) и возвращает обработанные данные для сохранения в БД
 * @param boolean $saveflag - если true - должно добавляется это поле в SQL-запрос для обновления данных, если false поле не запишется в БД
 */
public function addFieldName($tagname, $entityname, $datafieldname, $tablename, $fieldtype = "VARCHAR(1024)", $callback = null, $saveflag = true )
{
    $this->Tags[] = array( "tagstatus" => static::TRM_XML_STATUS_FIELD | ( !$saveflag ? static::TRM_XML_STATUS_NOTSAVE : 0),
                                    "tagname" => strtoupper($tagname),
                                    "entityname" => strtoupper($entityname),
                                    "datafieldname" => $datafieldname,
                                    "tablename" => $tablename,
                                    "fieldtype" => $fieldtype,
                                    "callback" => $callback );
}

/**
 * ставит в соответсвие названию тега - $tagname значение индексного поля $fieldname из таблицы БД - $tablename
 * и указывает в какое поле $datafieldname из таблицы БД нужно записывать данные внутри этого тега
 * 
 * @param string $tagname - имя тега из XML-документа
 * @param string $entityname - имя XML-секции-тега, к которому относится данное поле
 * @param string $indexfieldname - имя поля с индексом из таблицы БД
 * @param string $datafieldname - название поля в таблице БД с данными, которые соответвуют индексу $tagname из поля $indexfieldname
 * @param string $tablename - имя таблицы
 * @param string $parenttagname - имя тега из этой же записи-entity, которое может быть задано как имя родительского элемента, 
 *   для которого и задается индекс
 * @param string $callback - имя функции, которая вызывается с параметрами ($name, $data) и возвращает обработанные данные для сохранения в БД
 * @param boolean $saveflag - если true - должно добавляется это поле в SQL-запрос для обновления данных, если false поле не запишется в БД
 */
public function addIndexName($tagname, $entityname, $indexfieldname, $datafieldname, $tablename, $parenttagname = null, $callback = null, $saveflag = true )
{
    $this->Tags[] = array( "tagstatus" => static::TRM_XML_STATUS_INDEX | ( !$saveflag ? static::TRM_XML_STATUS_NOTSAVE : 0),
                                    "tagname" => strtoupper($tagname),
                                    "entityname" => strtoupper($entityname),
                                    "datafieldname" => $datafieldname,
                                    "indexfieldname" => $indexfieldname,
                                    "tablename" => $tablename,
                                    "fieldtype" => "int(11)",
                                    "parenttagname" => strtoupper($parenttagname),
                                    "callback" => $callback );
}

/**
 * ставит в соответсвие названию XML-тега ($tagname) сущность из БД, содержащую данные полей, возможно, из нескольких таблиц
 * 
 * @param string $tagname - имя тега из XML-документа, который обозначает начало новой записи
 * @param string $datafieldname - имя поля в таблице БД, если $tagname описывает только одно значение
 * @param string $tablename - имя таблицы БД
 * @param string $fieldtype - тип данных в БД
 * @param boolean $saveflag - если true - должно добавляется это поле в SQL-запрос для обновления данных, если false поле не запишется в БД
 */
public function addEntityName($tagname, $datafieldname, $tablename, $fieldtype = "VARCHAR(1024)", $saveflag = true)
{
    $this->Tags[] = array( "tagstatus" => static::TRM_XML_STATUS_ENTITY | ( !$saveflag ? static::TRM_XML_STATUS_NOTSAVE : 0),
                                    "tagname" => strtoupper($tagname),
                                    "entityname" => "", // strtoupper($tagname),
                                    "datafieldname" => $datafieldname,
                                    "tablename" => $tablename,
                                    "fieldtype" => $fieldtype );
}

/**
 * возвращает значение индекса из массива Tags для XML-тега $tagname
 * 
 * @param string $tagname - имя XML-тега
 * @param string $entityname - имя XML-секции, к которой этот тег имеет отношение, например, для полей - это запись
 * 
 * @return integer - если в массиве есть данные для $tagname со значением $entityname, возвращается индекс из массива, иначе -1
 */
protected function getCurrentTagIndex($tagname, $entityname )
{
    foreach ($this->Tags as $index => $CurTag)
    {
        if( ($CurTag["tagname"] == $tagname) && ($CurTag["entityname"] == $entityname) )
        {
            return $index;
        }
    }
    return -1;
}

/**
 * возвращает значение из массива Tags для XML-тега $tagname
 * 
 * @param string $tagname - имя XML-тега
 * @param string $entityname - имя XML-секции, к которой $tagname имеет отношение,
 *   например, если $tagname - это имя поля, тогда $entityname - это название записи (виртуально)
 * @param string $dataindex - данные, которые нужно получить для тега
 * 
 * @return boolean|mixed - если в массиве есть данные для $tagname, возвращаются эти данные, 
 * если не указано какие именно данные нужны $dataindex, то возвращается индекс из массива Tags,
 * если указан $dataindex, но такого индекса нет в XML, то вернется false
 */
protected function getTags($tagname, $entityname, $dataindex = null )
{
//    if(!strlen($tagname) ) { return false; }

    if( ($index = $this->getCurrentTagIndex($tagname, $entityname) ) !== -1 )
    {
        if( $dataindex===null ) { return $index; }
        if( isset($this->Tags[$index][$dataindex]) ) { return $this->Tags[$index][$dataindex]; }
        //else { return false; }
    }

    return false;
}

/**
 * формирует из произвольных данных значение индекса,
 * в XML все теги должны быть только строковыми, поэтому, если тег обозначает какой-то индекс, 
 * к нему приписывают вначале букву,
 * от нее надо избавится и получить целое число
 * 
 * @param string $val - строковое значение индекса
 * @return integer
 */
public function getIndexValue($val)
{
    return preg_replace("#[a-zA-Z]([0-9]+)#", "$1", $val);
}

/**
 * функция работы с данными
 * 
 * @param resource $parser - объект парсера, созданный, например, xml_parser_create
 * @param mixed $data - данные, которые находятся внутри обрабатываемого в данный момент тега
 * @return boolean
 */
function data ($parser, $data)
{
    $current = $this->Transaction;
    if( $this->CurrentTagName == $this->Transaction )
    {
        $current = "";
    }

    if( strlen($this->CurrentIndexValue) )
    {
        if( $this->config["RewriteIndexData"] )
        {
            $parenttagname = $this->getTags($this->CurrentTagName, $current, "parenttagname");

            if( isset($this->IndexData[$this->CurrentIndexValue]) )
            {
                $this->IndexData[$this->CurrentIndexValue][$this->getTags($this->CurrentTagName, $current, "datafieldname")] .= $data;
            }
            else
            {
                $this->IndexData[$this->CurrentIndexValue] = array(
                    $this->getTags($this->CurrentTagName, $current, "indexfieldname") => $this->CurrentIndexValue,
                    $this->getTags($this->CurrentTagName, $current, "datafieldname") => $data,
                    $this->getTags($parenttagname, $current, "datafieldname") => $this->TransactionData[ $parenttagname ] 
                );
            }

        }
    }
    else
    {
        if( strlen($this->CurrentTagName) && !empty( $this->getTags($this->CurrentTagName, $current, "datafieldname") )  )
        {
            if( !isset($this->TransactionData[ $this->CurrentTagName ]) ) { $this->TransactionData[ $this->CurrentTagName ] = ""; }

            $this->TransactionData[ $this->CurrentTagName ] .= $data;
        }
    }

    $this->DataCount += 1;
    return true;
}

/**
 * вызывается когда парсер встречает открывающий секцию тег, например, <offer>
 * 
 * @param resource $parser - является ссылкой на XML анализатор вызывающий обработчик
 * @param string $name -  имя элемента, для которого этот обработчик вызывается
 * @param array $attrs - ассоциативный массив с атрибутами элемента (если есть). 
 * Индексами этого массива будут имена атрибутов, а значения массива будут соответствовать значениям атрибутов
 */
function startElement($parser, $name, $attrs)
{
    // если данный тег является индексом с добавленной буквой вначале,
    // то checkTemplate вернет имя виртуального XML-тега для этого индекса,
    // либо пустую строку
    $this->CurrentTagName = $this->checkTemplate($name);

    // получаем индекс для текущего тега $name для текущей записи-entity из массива Tags
    $this->CurrentTagIndex = $this->getCurrentTagIndex( $name, $this->Transaction );

    if( strlen($this->CurrentTagName) )
    {
        if( ($this->CurrentTagIndex !== -1) && ($f = $this->Tags[$this->CurrentTagIndex]["callback"]) )
        {
            $this->CurrentIndexValue = $f($this->CurrentTagName, $name);
        }
        else
        {
            $this->CurrentIndexValue = $this->getIndexValue($name);
        }
        if( empty($this->CurrentIndexValue) )
        {
            throw new \Exception( __METHOD__ . " Зачение индексного поля из тега {$name} получить не удалось!" );
        }
        return true;
    }

    $str = "";
    if( $this->CurrentTagIndex === -1 )
    {
        return false;
    }
    $currentstatus = $this->Tags[$this->CurrentTagIndex]["tagstatus"];

    if( $currentstatus & static::TRM_XML_STATUS_TABLE )
    {
        if($this->config["RecreateTables"] === true)
        {
            $str .= "DROP TABLE IF EXISTS `".$this->Tags[$this->CurrentTagIndex]["tablename"]."`;";
            $str .= "CREATE TABLE `".$this->Tags[$this->CurrentTagIndex]["tablename"]."`;";
        }
    }
    else if( $currentstatus & static::TRM_XML_STATUS_FIELD )
    {

        if( !strlen($this->Transaction) )
        {
            throw new \Exception( __METHOD__ . " Нарушена структура XML-документа, или неверно заданы настроки, поле {$name} вне записи!" );
        }

        if( isset($this->Tags[$this->CurrentTagIndex]["entityname"]) ) 
        {
            if($this->config["RecreateFields"] === true)
            {
                $str .= "ALTER TABLE `".$this->Tags[$this->CurrentTagIndex]["tablename"]."` DROP `".$this->Tags[$this->CurrentTagIndex]["datafieldname"]."`;";
                $str .= "ALTER TABLE `".$this->Tags[$this->CurrentTagIndex]["tablename"]."` ADD `".$this->Tags[$this->CurrentTagIndex]["datafieldname"]."` ".$this->Tags[$this->CurrentTagIndex]["fieldtype"].";";
            }

            $this->CurrentTagName = $name;
            $this->TransactionData[ $this->CurrentTagName ] = "";
        }
    }
    else if( $currentstatus & static::TRM_XML_STATUS_ENTITY )
    {
        if( $this->Transaction == $name )
        {
            throw new \Exception( __METHOD__ . " Нарушена структура XML-документа, у записи {$name} не обнаружен закрывающий тег!" );
        }
        // начинаем транзакцию для записи
        $this->Transaction = $name;
        $this->CurrentTagName = $name;
    }

    $this->putToFile($str, $parser);

    $CurrentTagName = $this->CurrentTagName;
    foreach( $attrs as $key => $val )
    {
        if( $key == "ENCODING" )
        {
            $this->setXMLCharset( strtoupper($val) );
            continue;
        }
        if( $this->getTags($key, $this->Transaction, "tagstatus") & static::TRM_XML_STATUS_FIELD )
        {
            $this->CurrentTagName = $key;
            $this->data($parser, $val);
        }
    }
    $this->CurrentTagName = $CurrentTagName;
    $this->DataCount = 0;
}

/**
 * вызывается когда парсер встречает закрывающий секцию тег, например, </offer>
 * 
 * @param resource $parser - является ссылкой на XML анализатор вызывающий обработчик
 * @param string $name -  имя элемента, для которого этот обработчик вызывается
 */
function endElement($parser, $name)
{
    $insertstr = "";

    if( strlen($this->CurrentIndexValue) )
    {
        if( $this->config["RewriteIndexData"] )
        {
            $tagname = $this->checkTemplate($name);
            $currenttablename = $this->getTags($tagname, $this->Transaction, "tablename");

            foreach( $this->IndexData as $dataarray )
            {
                $fieldstr = "";
                $datastr = "";
                $odkustr = "";
                foreach( $dataarray as $fieldname => $data )
                {
                    $fieldstr .= "`" . $fieldname . "`,";
                    $datastr  .= "'" . addcslashes($data, "'") . "',";
                    $odkustr  .= "`" . $fieldname . "` = '" . addcslashes($data, "'") . "',";
                }
                $fieldstr = rtrim($fieldstr, ",");
                $datastr = rtrim($datastr, ",");
                $odkustr = rtrim($odkustr, ",");
                if( $this->InsertMode == self::TRM_SQL_MODE_REPLACE ) { $insertstr .= "REPLACE INTO `{$currenttablename}`"; }
                else if( $this->InsertMode == self::TRM_SQL_MODE_INSERT_IGNORE ) { $insertstr .= "INSERT IGNORE INTO `{$currenttablename}`"; }
                else { $insertstr .= "INSERT INTO `{$currenttablename}`"; }
                    //if( $this->InsertMode == self::TRM_SQL_MODE_INSERT || $this->InsertMode == self::TRM_SQL_MODE_INSERT_ODKU )
                
                $insertstr .= " ({$fieldstr})";
                $insertstr .= " VALUES ({$datastr})";
                if( $this->InsertMode == self::TRM_SQL_MODE_INSERT_ODKU ) { $insertstr .= " ON DUPLICATE KEY UPDATE " . $odkustr; }
                $insertstr .= ";";
            }
        }
        $this->CurrentIndexValue = "";
        unset( $this->IndexData );
        $this->IndexData = array();
    }

    // можно в TransactionData записывать данные с индексами = названиям полей в БД,
    // затем создавать объект DataObject , добавлять туда строку - addRow($TransactionData),
    // затем с помощью TRMARCommon генерировать уже готовый запрос update...
    if( $this->Transaction == $name )
    {
        $fieldstr = array();
        $datastr = array();
        $odkustr = array();
        
        foreach( $this->TransactionData as $tagname => $data )
        {
             // если данные пустые, переходим к следующим...
            //if( empty($data) ) { continue; }

            $currentindex = $this->getCurrentTagIndex($tagname, $this->Transaction == $tagname ? "" : $this->Transaction );
/*
TRMLib::ip($tagname);
TRMLib::ip($data);
TRMLib::ip($this->TransactionData);
*/
            // если для поля стоит флаг не записывать, переходим к следующей...
            if( $this->Tags[$currentindex]["tagstatus"] & static::TRM_XML_STATUS_NOTSAVE ) { continue; }

            //$fn = $this->Tags[$currentindex]["callback"];
            if( isset($this->Tags[$currentindex]["callback"]) ) 
            {
                $data = $this->Tags[$currentindex]["callback"]($tagname, $data);
                $currentdatacharset = mb_detect_encoding($data);
                if($currentdatacharset != static::TRM_XML_OUTPUT_FILE_CHARSET ) // $this->XMLCharset)
                {
                    $data = iconv($currentdatacharset, static::TRM_XML_OUTPUT_FILE_CHARSET, $data);
                }
            }
            
            $currententity = $this->Transaction;
            if( $this->Transaction == $tagname )
            {
                $currententity = "";
            }
            $currenttablename = $this->Tags[$currentindex]["tablename"];
            $currentdatafieldname = $this->Tags[$currentindex]["datafieldname"];

            if( !isset($fieldstr[ $currenttablename ]) ) { $fieldstr[ $currenttablename ] = ""; }
            if( !isset($datastr[ $currenttablename ]) )  { $datastr[ $currenttablename ] = ""; }
            if( !isset($odkustr[ $currenttablename ]) )  { $odkustr[ $currenttablename ] = ""; }
            
            // если условие выполняется, значит в данных содержится имя поля БД!!!
            // из которого будут браться данные, в случае Duplicate Key
            if( preg_match("#^`[^`]+`$#", $data) )
            {
                $datastr[ $currenttablename ]  .= $data . ",";
                //$odkustr[ $currenttablename ]  .= "`" . $currentdatafieldname . "` = VALUES({$data}),";
                $odkustr[ $currenttablename ]  .= "`" . $currentdatafieldname . "` = VALUES(`" . $currentdatafieldname . "`),";
            }
            else
            {
                $datastr[ $currenttablename ]  .= "'" . addcslashes($data, "'") . "',";
                $odkustr[ $currenttablename ]  .= "`" . $currentdatafieldname . "` = '" . addcslashes($data, "'") . "',";
            }
            
            $fieldstr[ $currenttablename ] .= "`" . $currentdatafieldname . "`,";
        }

        foreach($fieldstr as $tablename => $str)
        {
            if( strlen($str) )
            {
                $fieldstr[$tablename] = rtrim($fieldstr[$tablename], ",");
                $datastr[$tablename] = rtrim($datastr[$tablename], ",");
                $odkustr[$tablename] = rtrim($odkustr[$tablename], ",");
                
                if( $this->InsertMode == self::TRM_SQL_MODE_REPLACE ) { $insertstr .= "REPLACE INTO `{$tablename}`"; }
                else if( $this->InsertMode == self::TRM_SQL_MODE_INSERT_IGNORE ) { $insertstr .= "INSERT IGNORE INTO `{$tablename}`"; }
                else { $insertstr .= "INSERT INTO `{$tablename}`"; }
                $insertstr .= " ({$fieldstr[$tablename]})";
                $insertstr .= " VALUES ({$datastr[$tablename]})";
                if( $this->InsertMode == self::TRM_SQL_MODE_INSERT_ODKU ) { $insertstr .= " ON DUPLICATE KEY UPDATE " . $odkustr[$tablename]; }
                $insertstr .= ";";
            }
        }
        unset( $this->TransactionData );
        $this->Transaction = "";
        $this->TransactionData = array();
    }

    $this->putToFile($insertstr, $parser);
    $this->CurrentTagName = "";
    $this->CurrentTagIndex = -1;
    $this->LastTagName = $name;
}

/**
 * начинает разбор XML и формирует на его основе SQL для создания или обновления БД
 * 
 * @param string $XMLFileName - имя файла с XML-данными для чтения
 * @param string $SQLFileName - имя файла, в который будут записаны SQL-запросы
 * @throws \Exception
 */
public function startParsing($XMLFileName, $SQLFileName, $StartString = 0)
{
    $this->CurrentOutputFileNumber = 0;
    $this->CurrentSize = 0;
    $this->XMLFileName = $XMLFileName;
    $this->SQLFileName = $SQLFileName;

    $this->SQLFile = new TRMFile();
    
    $this->SQLFile->openFile($this->makeCurrentOutputFileName(), "w");
    //$this->SQLFile->openFile( $this->SQLFileName . sprintf("_%03d", $this->CurrentOutputFileNumber), "w" );

    $this->XMLFile = new TRMFile();
    $this->XMLFile->openFile($this->XMLFileName, "r");
    $this->XMLSize = $this->XMLFile->getFileSize();

    for($i = 0; $i < $StartString; $i++ )
    {
        $this->XMLFile->getStringToBufferFrom();
    }
    $this->XMLFile->clearBuffer();
    // создаем стандартный php-парсер XML
    $xml_parser = xml_parser_create( static::TRM_XML_OUTPUT_FILE_CHARSET );
    // все имена тегов переводим в верхний регистр, чтобы все теги имели одинаковый регистр
    xml_parser_set_option( $xml_parser, XML_OPTION_CASE_FOLDING, true);
    // указываем какие функции будут работать при открытии и закрытии тегов
    xml_set_element_handler( $xml_parser, array($this, "startElement"), array($this, "endElement") );
    // указываем функцию для работы с данными
    xml_set_character_data_handler( $xml_parser, array($this, "data") );

    while( $this->XMLFile->getStringToBufferFrom() )
    {
        if( $this->XMLCharset != static::TRM_XML_OUTPUT_FILE_CHARSET )
        {
            $data = iconv($this->XMLCharset, static::TRM_XML_OUTPUT_FILE_CHARSET, $this->XMLFile->getBuffer() );
        }
        // отправляем данные в парсер xml
        if ( !xml_parse( $xml_parser, $data ) )
        {
            TRMLib::ip($this->XMLFile->getBuffer() );
            // если встретится ошибка, выбрасывается исключение
            throw new \Exception( "<br>XML Error: ".xml_error_string(xml_get_error_code($xml_parser)) . " at line ".xml_get_current_line_number($xml_parser) );
        }
        $this->XMLFile->clearBuffer();
    }
    // завершить разбор
    xml_parse($xml_parser, '', true);
    // закрываем парсер и освобождаем память
    xml_parser_free($xml_parser);

    $this->XMLFile->clearBuffer();
    $this->XMLFile->closeFile();

    $this->SQLFile->putBufferTo();
    $this->SQLFile->clearBuffer();
    $this->SQLFile->closeFile();
    if(is_callable($this->CallBack) )
    {
        call_user_func( $this->CallBack, $this->SQLFile->getFullPath() );
        //$this->CallBack( $this->SQLFile->getFullPath() );
    }
}

/**
 * служебная функция, помещает строку в буфер,
 * если размер буфера превышает TRM_XML_MAX_BLOCK_SIZE байт, то записывается в файл
 * 
 * @param string $str - записываемая строка
 */
private function putToFile($str, $parser)
{
    if( empty($str) ) { return false; }

    $CurrentSize = strlen($str."\n");
    if( $CurrentSize > static::TRM_MAX_OUTPUT_FILE_SIZE )
    {
        TRMLib::dp( __METHOD__ . " Фрагмент для записи в файл превышает допустимый размер!" );
        return false;
    }

    $this->CurrentSize += $CurrentSize;
    // при очередном добавлении размер фала превысит допустимый, 
    // поэтому пока не записываем блок, а закрываем фалй и создаем с новым именем
    if( $this->CurrentSize > static::TRM_MAX_OUTPUT_FILE_SIZE )
    {
        if( $this->SQLFile->getBufferSize() && !$this->SQLFile->putBufferTo() )
        {
            throw new \Exception("Не удалось записать содержимое буфера в файл " 
                    .$this->SQLFile->getFullPath()
                    ." - "
                    .$this->SQLFile->getStateString() );
        }
        $this->SQLFile->clearBuffer();
        $this->SQLFile->closeFile();
        // перед созданием нового файла с новым именем проверяем задана ли callback-функция,
        // если задана, то вызываем ее, передав параметром имя текущего файла с записанными SQL-данными
        if(is_callable($this->CallBack) )
        {
            call_user_func( $this->CallBack, $this->SQLFile->getFullPath() );
            //$this->CallBack( $this->SQLFile->getFullPath() );
        }
        $this->CurrentOutputFileNumber += 1;
        $this->SQLFile->openFile( $this->makeCurrentOutputFileName(), "w" );

        // общий размер данных будет равен не записанному пока блоку
        $this->CurrentSize = $CurrentSize;
    }
    // теперь записываем очередной блок
    $this->SQLFile->addToBuffer($str . "\n");
    if( $this->SQLFile->getBufferSize() > static::TRM_XML_MAX_BLOCK_SIZE )
    {
        if( !$this->SQLFile->putBufferTo() )
        {
            throw new \Exception("Не удалось записать содержимое буфера в файл " . $this->SQLFile->getFullPath() );
        }
        $this->SQLFile->clearBuffer();
    }
    
    return true;
}

/**
 * проверяет название тега (или атрибута) на соответствие шаблону из массива
 * и возвращает найденное имя тега соответсвующее шаблону
 * 
 * @param string $name - проверямый параметр (тег или атрибут), содержащий значение индекса
 * @return string - имя тега соответсвующее шаблону, либо пустую строку
 */
public function checkTemplate($name)
{
    foreach($this->template as $temp)
    {
        if( preg_match($temp["pattern"], $name) === 1 ) { return $temp["tagname"]; }
    }
    return "";
}

/**
 * заменяет точку "." на послдеовательность "_000." в имени фала, что бы были разные названия для результируюших файлов, 
 * если они получаются большими и разделяются на несколько, где 000 - текуший номер файла, увеличивается на +1 в putToFile
 * 
 * @return string - новое имя файла
 */
private function makeCurrentOutputFileName()
{
    $Pattern = "/(.*)(\.)([^.]*)$/m";
    $Replace = sprintf( "$1_%03u$2$3", $this->CurrentOutputFileNumber ); // "$1_000$2$3";

    return preg_replace( $Pattern, $Replace, $this->SQLFileName );

//    return str_replace(".", sprintf("_%03d.", $this->CurrentOutputFileNumber), $this->SQLFileName);
//    preg_replace("#.*(-{3}\d\.).*#", sprintf("-%03d.", $this->CurrentOutputFileNumber), $this->SQLFileName );
}

} // TRMXMLParser