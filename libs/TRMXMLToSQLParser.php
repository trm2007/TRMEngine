<?php

namespace TRMEngine\XMLParser;

use TRMEngine\File\TRMFile;
use TRMEngine\Helpers\TRMLib;

/**
 * ������� ����� ��� ������� XML � ������������ SQL-�������� ��� ������ ������ � ��
 */
class TRMXMLToSQLParser
{
const TRM_XML_OUTPUT_FILE_CHARSET = "UTF-8";
/**
 * ������ ������������� ����� ��� ���������� � ������ � ���� (� ������)
 */
const TRM_XML_MAX_BLOCK_SIZE = 65535; // 32767;

/**
 * ������������ ������ ��������� �����, 
 */
const TRM_MAX_OUTPUT_FILE_SIZE = 25165824; // 41943040;
/**
 * ����������, ��� �������� ������� ���� �������������� � �������� ������� � ��
 */
const TRM_XML_STATUS_TABLE = 1;
/**
 * ����������, ��� �������� ������� ���� �������������� � ������, ������ ������ ����� ���� ����� ��������� ������ �� ���������� ������...
 */
const TRM_XML_STATUS_ENTITY = 2;
/**
 * ����������, ��� ������ ��� �������� ��������� �������-���� � ��, � ���������� ������ ���� - ������ ��� ������ � �������...
 */
const TRM_XML_STATUS_FIELD = 4;
/**
 * ����������, ��� ������ ��� �������� ��������� ���������� ���� � ��, � ���������� ������ ����, ��� �������, �������� �����-�� ��������������...
 */
const TRM_XML_STATUS_INDEX = 8;
/**
 * ����������, ��� ������ ������ ��� ����� ���� �������� �� �����, �� ��������� ���� � �������� ����� ���� ������� ������!
 */
const TRM_XML_STATUS_NOTSAVE = 128;
/**
 * ����� ���������� ������ � �������� - ������� �������, ������� ������ ��� ���������
 */
const TRM_SQL_MODE_INSERT = 1;
/**
 * ����� ���������� ������ � �������� - ������� � �������������� ��� ���������
 */
const TRM_SQL_MODE_INSERT_IGNORE = 2;
/**
 * ����� ���������� ������ � �������� - ������� � ���������� ������ ��� ���������, 
 */
const TRM_SQL_MODE_INSERT_ODKU = 4;
/**
 * ����� ���������� ������ � �������� - ������ ������ �� ����� ��� ���������, 
 * �������� ��������� AUTO_INCREMET �������, ���� ������������ �� �� ����, � �� ������� ��������� ����!!!
 */
const TRM_SQL_MODE_REPLACE = 8;

/**
 * @var string - ��� �������, ������� ���������� ����� ���������� �������������� ������ ���������� ������� = TRM_MAX_OUTPUT_FILE_SIZE
 * ����� ��������� ����� ���� ������� �����
 */
protected $CallBack = null;
/**
 * @var string - ���� � XML-�����
 */
protected $XMLFile;
/**
 * @var TRMFile - ������ ��� ������ � �����, � ������� ����� ������������ SQL-�������
 */
protected $SQLFile;
/**
 * @var string - ��������� ��� ����� ��� ���������� ��������� - SQL-�������� 
 */
protected $SQLFileName = "";
/**
 * @var string - ��������� ��� ����� ��� ��������� XML-������
 */
protected $XMLFileName = "";
/**
 * @var array - ��������� ������ � XML � c������������� ��
 */
protected $config = array();
/**
 * @var array - ������ � ������� ����� XML, 
 * ��� �� �� �������� � ������ ����������� 
 * array( array( "tagname", "tagstatus", "tablename", "fieldname" ... ), ... )
 */
protected $Tags = array();
/**
 * @var string - ������ ��� ���� , ��� �������� ����� �������� ������, ��� ��� Entity
 */
private $Transaction = "";
/**
 * @var string - ��� �������� ��������������� ����, ������������ � ������� ��������� ������ data()
 */
private $CurrentTagName = "";
/**
 * @var string - ��� ���������� ������������� ����
 */
private $LastTagName = "";
/**
 * @var integer - ���������� ������ ����������� ������ ��� �������� �����
 */
private $DataCount = 0;
/**
 * @var array - ������ ��� ������� ����������, 
 * � ������ ���������� ��� ���� � �� �������� ��� ������� Entity
 */
private $TransactionData = array();
/**
 * @var array - ������ ��� �������� ����� ������������ �� ��������
 */
private $IndexData = array();
/**
 * @var mixed - ������� �������� ��������������� ���������� ���� �� XML
 */
private $CurrentIndexValue = null;
/**
 * @var integer - �������� ������� � ������� Tags ��� �������� XML-����, 
 * ���� ��� ���� �� ����������� ���������, ���������� � ������� Tags, $CurrentTagIndex ��������� �������� -1
 */
private $CurrentTagIndex = -1;
/**
 * @var string - ���������, � ������� �������� XML-����
 */
private $XMLCharset = "WINDOWS-1251";
/**
 * @var array - ������ � ��������� � ����������� �����-�����
 */
private $template = array();
/**
 * @var integer - ������� ������ ������������ ����� XML-�����
 */
public $CurrentSize;
/**
 * @var integer - ���� ������ ��������� ����� ��������� TRM_MAX_OUTPUT_FILE_SIZE (40 ��), 
 * �� �� ����������� �� ��������� ������,
 * � ������ ��������� ������������ � ����������� ������ ������������ �� �������
 */
public $CurrentOutputFileNumber = 0;
/**
 * @var integer - ������ ������ XML-�����
 */
public $XMLSize;
/**
 * @var string - ��� ������� ��� ������, ����� ���� 1 - �������, ��� 0 - ������
 */
protected $InsertMode = 1;


public function __construct()
{
    $this->config = array(
        "RecreateTables" => false, // true - ������� � ������� ������� ������, false - ���������, ���� ��� ����
        "RecreateFields" => false, // true - ������� � ������� ���� ������, false - ���������, ���� ��� ����
        "RewriteIndexData" => true, // true - �������������� ������ � ���� ������, ��������� � ��������, �� ������, false - ��������� ��� ����
    );
}

/**
 * @param type $funcname - ��� �������, ������� ���������� ����� ���������� �������������� ������ ���������� ������� = TRM_MAX_OUTPUT_FILE_SIZE
 * ����� ��������� ����� ���� ������� �����, ��� ������ � ������� �� ������� ���������� ���� �������� - ��� ������������� �����
 */
public function setCallBack( $funcname )
{
    if(is_callable($funcname) )
    {
        $this->CallBack = $funcname;
    }
}

/**
 * ������������� ����� ������� ������� � �� ��� ������ - REPLACE INTO ...
 */
public function setReplaceMode()
{
    $this->InsertMode = self::TRM_SQL_MODE_REPLACE;
}

/**
 * ������������� ����� ������� ������� � �� 
 * ���� $mode �� �����, ��� �������� ������� �������,
 * �� ��������������� ����� ��� ������� � ������� ��� ��������� - INSERT INTO ... ON DUPLICATE KEY UPDATE
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
 * ��������� ������������ ������� XML-����
 * 
 * @param string $pattern - ������ - ���������� ���������
 * @param string $tagname - ��������������� ��� ����
 * @param string $entityname - �������� ������ ��� ����� ����
 */
public function addTemplate($pattern, $tagname, $entityname)
{
    $this->template[] = array( "pattern" => $pattern, 
                                "tagname" => strtoupper($tagname), 
                                "entityname" => strtoupper($entityname) );
}

/**
 * ������� ������� template ����������� �������� XML-�����
 */
public function clearTemplate()
{
    unset($this->template);
    $this->template = array();
}

/**
 * @param string $charset - ��������� XML-�����
 */
public function setXMLCharset($charset)
{
    $this->XMLCharset = strtoupper($charset);
}

/**
 * ������ � ����������� �������� ���� - $tagname �������� ������� �� �� - $tablename
 * 
 * @param string $tagname - ��� ���� �� XML-���������
 * @param string $tablename - ��� �������
 */
public function addTableName($tagname, $tablename)
{
    $this->Tags[] = array( "tagstatus" => static::TRM_XML_STATUS_TABLE,
                                   "tagname" => strtoupper($tagname),
                                   "entityname" => "",
                                   "tablename" => $tablename );
}

/**
 * ������ � ����������� �������� ���� - $tagname �������� ���� $fieldname �� ������� �� - $tablename
 * 
 * @param string $tagname - ��� ���� �� XML-���������
 * @param string $entityname - ��� XML-������-����, � �������� ��������� ������ ����
 * @param string $datafieldname - ��� ���� � ������� ��
 * @param string $tablename - ��� ������� ��
 * @param string $fieldtype - ��� ������ � ��
 * @param string $callback - ��� �������, ������� ���������� � ����������� ($name, $data) � ���������� ������������ ������ ��� ���������� � ��
 * @param boolean $saveflag - ���� true - ������ ����������� ��� ���� � SQL-������ ��� ���������� ������, ���� false ���� �� ��������� � ��
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
 * ������ � ����������� �������� ���� - $tagname �������� ���������� ���� $fieldname �� ������� �� - $tablename
 * � ��������� � ����� ���� $datafieldname �� ������� �� ����� ���������� ������ ������ ����� ����
 * 
 * @param string $tagname - ��� ���� �� XML-���������
 * @param string $entityname - ��� XML-������-����, � �������� ��������� ������ ����
 * @param string $indexfieldname - ��� ���� � �������� �� ������� ��
 * @param string $datafieldname - �������� ���� � ������� �� � �������, ������� ����������� ������� $tagname �� ���� $indexfieldname
 * @param string $tablename - ��� �������
 * @param string $parenttagname - ��� ���� �� ���� �� ������-entity, ������� ����� ���� ������ ��� ��� ������������� ��������, 
 *   ��� �������� � �������� ������
 * @param string $callback - ��� �������, ������� ���������� � ����������� ($name, $data) � ���������� ������������ ������ ��� ���������� � ��
 * @param boolean $saveflag - ���� true - ������ ����������� ��� ���� � SQL-������ ��� ���������� ������, ���� false ���� �� ��������� � ��
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
 * ������ � ����������� �������� XML-���� ($tagname) �������� �� ��, ���������� ������ �����, ��������, �� ���������� ������
 * 
 * @param string $tagname - ��� ���� �� XML-���������, ������� ���������� ������ ����� ������
 * @param string $datafieldname - ��� ���� � ������� ��, ���� $tagname ��������� ������ ���� ��������
 * @param string $tablename - ��� ������� ��
 * @param string $fieldtype - ��� ������ � ��
 * @param boolean $saveflag - ���� true - ������ ����������� ��� ���� � SQL-������ ��� ���������� ������, ���� false ���� �� ��������� � ��
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
 * ���������� �������� ������� �� ������� Tags ��� XML-���� $tagname
 * 
 * @param string $tagname - ��� XML-����
 * @param string $entityname - ��� XML-������, � ������� ���� ��� ����� ���������, ��������, ��� ����� - ��� ������
 * 
 * @return integer - ���� � ������� ���� ������ ��� $tagname �� ��������� $entityname, ������������ ������ �� �������, ����� -1
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
 * ���������� �������� �� ������� Tags ��� XML-���� $tagname
 * 
 * @param string $tagname - ��� XML-����
 * @param string $entityname - ��� XML-������, � ������� $tagname ����� ���������,
 *   ��������, ���� $tagname - ��� ��� ����, ����� $entityname - ��� �������� ������ (����������)
 * @param string $dataindex - ������, ������� ����� �������� ��� ����
 * 
 * @return boolean|mixed - ���� � ������� ���� ������ ��� $tagname, ������������ ��� ������, 
 * ���� �� ������� ����� ������ ������ ����� $dataindex, �� ������������ ������ �� ������� Tags,
 * ���� ������ $dataindex, �� ������ ������� ��� � XML, �� �������� false
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
 * ��������� �� ������������ ������ �������� �������,
 * � XML ��� ���� ������ ���� ������ ����������, �������, ���� ��� ���������� �����-�� ������, 
 * � ���� ����������� ������� �����,
 * �� ��� ���� ��������� � �������� ����� �����
 * 
 * @param string $val - ��������� �������� �������
 * @return integer
 */
public function getIndexValue($val)
{
    return preg_replace("#[a-zA-Z]([0-9]+)#", "$1", $val);
}

/**
 * ������� ������ � �������
 * 
 * @param resource $parser - ������ �������, ���������, ��������, xml_parser_create
 * @param mixed $data - ������, ������� ��������� ������ ��������������� � ������ ������ ����
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
 * ���������� ����� ������ ��������� ����������� ������ ���, ��������, <offer>
 * 
 * @param resource $parser - �������� ������� �� XML ���������� ���������� ����������
 * @param string $name -  ��� ��������, ��� �������� ���� ���������� ����������
 * @param array $attrs - ������������� ������ � ���������� �������� (���� ����). 
 * ��������� ����� ������� ����� ����� ���������, � �������� ������� ����� ��������������� ��������� ���������
 */
function startElement($parser, $name, $attrs)
{
    // ���� ������ ��� �������� �������� � ����������� ������ �������,
    // �� checkTemplate ������ ��� ������������ XML-���� ��� ����� �������,
    // ���� ������ ������
    $this->CurrentTagName = $this->checkTemplate($name);

    // �������� ������ ��� �������� ���� $name ��� ������� ������-entity �� ������� Tags
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
            throw new \Exception( __METHOD__ . " ������� ���������� ���� �� ���� {$name} �������� �� �������!" );
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
            throw new \Exception( __METHOD__ . " �������� ��������� XML-���������, ��� ������� ������ ��������, ���� {$name} ��� ������!" );
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
            throw new \Exception( __METHOD__ . " �������� ��������� XML-���������, � ������ {$name} �� ��������� ����������� ���!" );
        }
        // �������� ���������� ��� ������
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
 * ���������� ����� ������ ��������� ����������� ������ ���, ��������, </offer>
 * 
 * @param resource $parser - �������� ������� �� XML ���������� ���������� ����������
 * @param string $name -  ��� ��������, ��� �������� ���� ���������� ����������
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

    // ����� � TransactionData ���������� ������ � ��������� = ��������� ����� � ��,
    // ����� ��������� ������ DataObject , ��������� ���� ������ - addRow($TransactionData),
    // ����� � ������� TRMARCommon ������������ ��� ������� ������ update...
    if( $this->Transaction == $name )
    {
        $fieldstr = array();
        $datastr = array();
        $odkustr = array();
        
        foreach( $this->TransactionData as $tagname => $data )
        {
             // ���� ������ ������, ��������� � ���������...
            //if( empty($data) ) { continue; }

            $currentindex = $this->getCurrentTagIndex($tagname, $this->Transaction == $tagname ? "" : $this->Transaction );
/*
TRMLib::ip($tagname);
TRMLib::ip($data);
TRMLib::ip($this->TransactionData);
*/
            // ���� ��� ���� ����� ���� �� ����������, ��������� � ���������...
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
            
            // ���� ������� �����������, ������ � ������ ���������� ��� ���� ��!!!
            // �� �������� ����� ������� ������, � ������ Duplicate Key
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
 * �������� ������ XML � ��������� �� ��� ������ SQL ��� �������� ��� ���������� ��
 * 
 * @param string $XMLFileName - ��� ����� � XML-������� ��� ������
 * @param string $SQLFileName - ��� �����, � ������� ����� �������� SQL-�������
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
    // ������� ����������� php-������ XML
    $xml_parser = xml_parser_create( static::TRM_XML_OUTPUT_FILE_CHARSET );
    // ��� ����� ����� ��������� � ������� �������, ����� ��� ���� ����� ���������� �������
    xml_parser_set_option( $xml_parser, XML_OPTION_CASE_FOLDING, true);
    // ��������� ����� ������� ����� �������� ��� �������� � �������� �����
    xml_set_element_handler( $xml_parser, array($this, "startElement"), array($this, "endElement") );
    // ��������� ������� ��� ������ � �������
    xml_set_character_data_handler( $xml_parser, array($this, "data") );

    while( $this->XMLFile->getStringToBufferFrom() )
    {
        if( $this->XMLCharset != static::TRM_XML_OUTPUT_FILE_CHARSET )
        {
            $data = iconv($this->XMLCharset, static::TRM_XML_OUTPUT_FILE_CHARSET, $this->XMLFile->getBuffer() );
        }
        // ���������� ������ � ������ xml
        if ( !xml_parse( $xml_parser, $data ) )
        {
            TRMLib::ip($this->XMLFile->getBuffer() );
            // ���� ���������� ������, ������������� ����������
            throw new \Exception( "<br>XML Error: ".xml_error_string(xml_get_error_code($xml_parser)) . " at line ".xml_get_current_line_number($xml_parser) );
        }
        $this->XMLFile->clearBuffer();
    }
    // ��������� ������
    xml_parse($xml_parser, '', true);
    // ��������� ������ � ����������� ������
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
 * ��������� �������, �������� ������ � �����,
 * ���� ������ ������ ��������� TRM_XML_MAX_BLOCK_SIZE ����, �� ������������ � ����
 * 
 * @param string $str - ������������ ������
 */
private function putToFile($str, $parser)
{
    if( empty($str) ) { return false; }

    $CurrentSize = strlen($str."\n");
    if( $CurrentSize > static::TRM_MAX_OUTPUT_FILE_SIZE )
    {
        TRMLib::dp( __METHOD__ . " �������� ��� ������ � ���� ��������� ���������� ������!" );
        return false;
    }

    $this->CurrentSize += $CurrentSize;
    // ��� ��������� ���������� ������ ���� �������� ����������, 
    // ������� ���� �� ���������� ����, � ��������� ���� � ������� � ����� ������
    if( $this->CurrentSize > static::TRM_MAX_OUTPUT_FILE_SIZE )
    {
        if( $this->SQLFile->getBufferSize() && !$this->SQLFile->putBufferTo() )
        {
            throw new \Exception("�� ������� �������� ���������� ������ � ���� " 
                    .$this->SQLFile->getFullPath()
                    ." - "
                    .$this->SQLFile->getStateString() );
        }
        $this->SQLFile->clearBuffer();
        $this->SQLFile->closeFile();
        // ����� ��������� ������ ����� � ����� ������ ��������� ������ �� callback-�������,
        // ���� ������, �� �������� ��, ������� ���������� ��� �������� ����� � ����������� SQL-�������
        if(is_callable($this->CallBack) )
        {
            call_user_func( $this->CallBack, $this->SQLFile->getFullPath() );
            //$this->CallBack( $this->SQLFile->getFullPath() );
        }
        $this->CurrentOutputFileNumber += 1;
        $this->SQLFile->openFile( $this->makeCurrentOutputFileName(), "w" );

        // ����� ������ ������ ����� ����� �� ����������� ���� �����
        $this->CurrentSize = $CurrentSize;
    }
    // ������ ���������� ��������� ����
    $this->SQLFile->addToBuffer($str . "\n");
    if( $this->SQLFile->getBufferSize() > static::TRM_XML_MAX_BLOCK_SIZE )
    {
        if( !$this->SQLFile->putBufferTo() )
        {
            throw new \Exception("�� ������� �������� ���������� ������ � ���� " . $this->SQLFile->getFullPath() );
        }
        $this->SQLFile->clearBuffer();
    }
    
    return true;
}

/**
 * ��������� �������� ���� (��� ��������) �� ������������ ������� �� �������
 * � ���������� ��������� ��� ���� �������������� �������
 * 
 * @param string $name - ���������� �������� (��� ��� �������), ���������� �������� �������
 * @return string - ��� ���� �������������� �������, ���� ������ ������
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
 * �������� ����� "." �� ������������������ "_000." � ����� ����, ��� �� ���� ������ �������� ��� �������������� ������, 
 * ���� ��� ���������� �������� � ����������� �� ���������, ��� 000 - ������� ����� �����, ������������� �� +1 � putToFile
 * 
 * @return string - ����� ��� �����
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