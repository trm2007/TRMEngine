<?php

namespace TRMEngine\Helpers;

/**
 * �����, � ������� ������� ��������� �������� ������� � ���� ����������� ������� - ����������
 */
class TRMLib
{
/**
 * DefaultDebugStringColor - ���� ������ ���������� ���������� �� ���������, ������������ � ������� debugPrint � dp
 */
const DefaultDebugTextColor = "red";
/**
 * DefaultInfoTextColor - ���� ������ ��������������� ��������� �� ���������, ������������ � ������� ip
 */
const DefaultInfoTextColor = "blue";
/**
 * DefaultStringTextColor - ���� ������ ������� ����� �� ���������, ������������ � ������� sp
 */
const DefaultStringTextColor = "green";
/**
 * DefaultArrayTextColor - ���� ������ �������� �� ���������, ������������ � ������� ap
 */
const DefaultArrayTextColor = "gray";

/**
 * ������� ������������� ������ ���������� ����������
 */
static public $DebugBlocksCounter = 0;

/**
 * ������� ���������� ���������� � ���� ������� ����� �������
 * 
 * @param array $arr - ������ ������������ ������ ������������ debug_backtrace()
 */
protected static function printDebugTrace( array $arr )
{
    echo "<table border='1' style='border: 1px solid black; border-collapse: collapse;'>";
    echo "<tr><th>�</th><th>���� ������</th><th>���������</th></tr>";
    foreach ($arr as $num => $item)
    {
        echo "<tr>";
        echo "<td>$num</td>";
        echo "<td style='vertical-align: top;'>";
        if(array_key_exists("file", $item) ) { self::sp("��� �����: " . $item["file"]); }
        if(array_key_exists("line", $item) ) { self::sp("����� ������: " . $item["line"]); }
        if(array_key_exists("class", $item) ) { self::sp("�����: " . $item["class"]); }
        if(array_key_exists("object", $item) ) { self::sp("������:"); self::ip( $item["object"]); }
        if(array_key_exists("type", $item) ) { self::sp("��� ������ �������: " . $item["type"]); }
//            ���� ��� ����� ������ �������, ����� �������� [->].
//            ���� ��� ����� ������������ ������ ������, �� [::].
//            ���� ��� ������� ����� �������, �� ��������� ������.
        if(array_key_exists("function", $item) ) { self::sp("�������: " . $item["function"]); }
        echo "</td>";

        echo "<td style='vertical-align: top;'>";
        // array 	��� ���������� ������ �������, ����� ������� ������ ���������� ���� �������. ���� ������ ����������� �����, ����� ������� ������ ���������� ������. 
        if(array_key_exists("args", $item) ) { self::ap($item["args"]); }
        echo "</td>";

        echo "</tr>";
    }
    echo "</table>";
}

/**
 * ������� ��� ������ ���������� ����������
 * 
 * @param string $str - ������ ��� ������
 * @param string $color - ���� ��� ������, red, yellow, blue... ��� #00ff00...
 * @param boolean $traceflag - ����, ������� ��������� �������� ��� ��� ���������� �� ����� �������
 */
public static function dp($str, $color = self::DefaultDebugTextColor, $traceflag = false )
{
    if( defined("DEBUG") )
    {
        echo "<div class='trm_debug_info_div' style='width: 100%;'><strong>[Debug block(" . self::$DebugBlocksCounter++ . ")]</strong>";
        self::debugPrint($str, $color);
        if($traceflag) { self::printDebugTrace( debug_backtrace(), $color); }
        echo "</div>";
    }
}

/**
 * �������� ���������� ������ $str � ����� $color � ����. ����� <pre>...</pre> 
 * ������ ���� ���������� ��������� DEBUG
 * 
 * @param string $str - ������ ��� ������
 * @param string $color - ���� ������
 */
public static function debugPrint($str, $color = self::DefaultDebugTextColor )
{
    if( defined("DEBUG") )
    {
        echo "<pre style='color: {$color};'>";
        var_dump( $str );
        echo "</pre>";
    }
}

/**
 * ������� ��� ������ ���������� � ����� <pre>
 * 
 * @param mixed $str - ������ ��� ������
 * @param string $color - ���� ��� ������, red, yellow, blue... ��� #00ff00...
 */
public static function ip( $str, $color = self::DefaultInfoTextColor )
{
    echo "<pre style='color: {$color};'>";
    var_dump( $str );
    echo "</pre>";
}

/**
 * ������� ��� ������ �������
 * 
 * @param array $arr - ������ ��� ������
 * @param string $color - ���� ��� ������, red, yellow, blue... ��� #00ff00...
 */
public static function ap( array $arr, $color = self::DefaultArrayTextColor )
{
    echo "<pre style='color: {$color};'>";
    print_r( $arr );
    echo "</pre>";
}

/**
 * ������� ��� ������ ������
 * 
 * @param string $str - ������ ��� ������
 * @param string $color - ���� ��� ������, red, yellow, blue... ��� #00ff00...
 */
public static function sp( $str, $color = self::DefaultStringTextColor )
{
    echo "<pre style='color: {$color};'>{$str}</pre>";
}

/**
 * ������� �������� � ���������� �������� � ������� �������� �� ������ - 
 * 
 * @param string $s - ������ � �������� �������, ������� ����� �������� �� ���������� ������
 * @param boolean $specialforurl - ���� ���� ���� ���������� � true, ����� ���������� 100�100 � ������� ��, ���������� �� 100x100 � ���������� ���!
 * @param string $charset - ����� ��������. ����������� ��������������� � "windows-1251"
 * @return string - ������ � ������������� ��� ������������ ��������
 */
public static function translit($s, $specialforurl=false, $charset = "windows-1251")
{
    $s = (string) $s; // ����������� � ��������� ��������
    $s = strip_tags($s); // ������� HTML-����
    $s = str_replace(array("\n", "\r"), "-", $s); // ������� ������� �������

    // ������ ������� � �� ���������� X ���� ������� ���� specialforurl ��� ���������� � �������, �������� ��� �������� 100�100
    if($specialforurl) 
    {
      $s = preg_replace("/([0-9]+)�([0-9]+)/U", '$1x$2', $s);
    }

    // $s = function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s); // ��������� ������ � ������ ������� (������ ���� ������ ������)
    $s = mb_strtolower($s, $charset);
    $s = strtr($s, array('�'=>'a','�'=>'b','�'=>'v','�'=>'g','�'=>'d','�'=>'e','�'=>'e','�'=>'zh','�'=>'z','�'=>'i','�'=>'j','�'=>'k','�'=>'l','�'=>'m','�'=>'n','�'=>'o','�'=>'p','�'=>'r','�'=>'s','�'=>'t','�'=>'u','�'=>'f','�'=>'h','�'=>'c','�'=>'ch','�'=>'sh','�'=>'shch','�'=>'y','�'=>'e','�'=>'yu','�'=>'ya','�'=>'','�'=>''));
    $s = preg_replace("/[^0-9a-z-_]/i", "-", $s); // ������� ������ �� ������������ ��������
    $s = preg_replace("/\-\-+/", '-', $s); // ������� ������������� ����� -
    $s = trim($s, "-");
    return $s; // ���������� ���������
}

/**
 * ������������ ������ (��� ���� ������),
 * � ������� �������������� ������ ��������� ��������,
 * ���������� ���������� ��� ���� �������� ���������
 * 
 * @param array|string &$array - ������ �� ������ ��� ������, � ���������� ���������� ������� �������� ��� ����������
 * @param type $from - ��������� �� ������� ��������������
 * @param type $to - ��������� � ������� ��������������
 * @return array|string - ���������������� ������ ��� ������
 */
public static function conv(&$array, $from = "utf-8", $to = "windows-1251")
{
//    if( empty($array) ) { return $array; }

    if( !empty($array) && $from !== $to )
    {
        if( is_array($array) )
        {
            foreach($array as $key => $param )
            {
                $array[$key] = TRMLib::conv ($array[$key], $from, $to);
            }
        }
        elseif(is_string($array) )
        {
            $array = iconv($from, $to, $array);
        }
    }

    return $array;
}

/**
 * ������� ����������� ������� � XML ������.
 * �� ���� �������� ������-��������� ������,
 * �� ������ ���������� ������ � �������� xml � ������� ��������
 *
 * @param array $data
 * @param string $rootNodeName - �������� �������� (��� ���������� � ��������) xml-����
 * @param SimpleXMLElement $xml - ������������ ����������
 * 
 * @return string XML - ���������� XML � ���� ������
 */
public static function convertArrayToXml($data, $rootNodeName = 'data', $xml=null)
{
    if ($xml == null)
    {
        $xml = simplexml_load_string("<?xml version=\"1.0\" encoding=\"utf-8\"?><$rootNodeName />");
    }

    //���� �������� ������� 
    foreach($data as $key => $value)
    {
        // ������ ��������� �������� �������� ����� � XML
        if (is_numeric($key))
        {
            // ������� ������ �� ���������� �� ������� unknownNode_123...
            $key = "unknownNode_{$key}";
        }

        // ������� �� ��������� ������� � �� �����
        $key = preg_replace('/[^a-z0-9]/i', '', $key);

        // ���� �������� ������� ����� �������� �������� �� �������� ���� ����������
        if (is_array($value))
        {
            // ��������� ������ ���� XML � ���������� ������ SimpleXMLElement (������ ������ �� ����)
            $node = $xml->addChild($key);
            // ����������� �����
            self::convertArraytoXml($value, $rootNodeName, $node);
        }
        else
        {
            // ��������� ���� ����, 
            // ���������� ��������� ������� � �������������� HTML-���� �������� htmlentities (�������� �������, ��������� � �.�.)
            $value = htmlentities($value);
            // ��������� ���� XML � �������
            $xml->addChild($key,$value);
        }

    }

    return $xml->asXML();
}

/**
 * �������!!!
 * Returns the values from a single column of the input array, identified by
 * the $columnKey.
 *
 * Optionally, you may provide an $indexKey to index the values in the returned
 * array by the values from the $indexKey column in the input array.
 *
 * @param array $input A multi-dimensional array (record set) from which to pull
 *                     a column of values.
 * @param mixed $columnKey The column of values to return. This value may be the
 *                         integer key of the column you wish to retrieve, or it
 *                         may be the string key name for an associative array.
 * @param mixed $indexKey (Optional.) The column to use as the index/keys for
 *                        the returned array. This value may be the integer key
 *                        of the column, or it may be the string key name.
 * @return array
 */
public static function array_column($input = null, $columnKey = null, $indexKey = null)
{
    // Using func_get_args() in order to check for proper number of
    // parameters and trigger errors exactly as the built-in array_column()
    // does in PHP 5.5.
    $argc = func_num_args();
    $params = func_get_args();

    if ($argc < 2)
    {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
    }

    if (!is_array($params[0]))
    {
            trigger_error( 'array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given', E_USER_WARNING );
            return null;
    }

    if (!is_int($params[1])
            && !is_float($params[1])
            && !is_string($params[1])
            && $params[1] !== null
            && !(is_object($params[1]) && method_exists($params[1], '__toString')) )
    {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
    }

    if (isset($params[2])
            && !is_int($params[2])
            && !is_float($params[2])
            && !is_string($params[2])
            && !(is_object($params[2]) && method_exists($params[2], '__toString')) )
    {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
    }

    $paramsInput = $params[0];
    $paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;
    $paramsIndexKey = null;

    if (isset($params[2]))
    {
            if (is_float($params[2]) || is_int($params[2])) { $paramsIndexKey = (int) $params[2]; }
            else { $paramsIndexKey = (string) $params[2]; }
    }

    $resultArray = array();

    foreach ($paramsInput as $row)
    {
            $key = $value = null;
            $keySet = $valueSet = false;

            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row))
            {
                    $keySet = true;
                    $key = (string) $row[$paramsIndexKey];
            }

            if ($paramsColumnKey === null)
            {
                    $valueSet = true;
                    $value = $row;
            }
            elseif (is_array($row) && array_key_exists($paramsColumnKey, $row))
            {
                    $valueSet = true;
                    $value = $row[$paramsColumnKey];
            }

            if ($valueSet)
            {
                    if ($keySet)
                    {
                            $resultArray[$key] = $value;
                    }
                    else
                    {
                            $resultArray[] = $value;

                    }
            }
    }

    return $resultArray;
}


} // TRMLib