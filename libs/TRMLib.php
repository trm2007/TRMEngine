<?php

namespace TRMEngine\Helpers;

/**
 * класс, в котором собраны некоторые полезные функции в виде статическиъ методов - библиотека
 */
class TRMLib
{
/**
 * DefaultDebugStringColor - цвет текста отладочной информации по умолчанию, используется в функции debugPrint и dp
 */
const DefaultDebugTextColor = "red";
/**
 * DefaultInfoTextColor - цвет текста информационного сообщения по умолчанию, используется в функции ip
 */
const DefaultInfoTextColor = "blue";
/**
 * DefaultStringTextColor - цвет текста простых строк по умолчанию, используется в функции sp
 */
const DefaultStringTextColor = "green";
/**
 * DefaultArrayTextColor - цвет текста массивов по умолчанию, используется в функции ap
 */
const DefaultArrayTextColor = "gray";

/**
 * счетчик распечатанных блоков отладочной информации
 */
static public $DebugBlocksCounter = 0;

/**
 * выводит отладочную тнформацию в виде таблицы стека вызовов
 * 
 * @param array $arr - должен передаваться массив возвращаемый debug_backtrace()
 * @param boolean $CLIFlag - если установлен в TRUE, 
 * то будет производится вывод для Command Line интерфеса,
 * без HTML оформления
 */
protected static function printDebugTrace( array $arr, $CLIFlag = null )
{
    if( $CLIFlag === null ) { $CLIFlag = self::isCLI(); }

    if( !$CLIFlag )
    {
        echo "<style>";
        echo "
.debug_table{
    display: flex;
    clear: both;
}
.debug_table:nth-child(4n), .debug_table:nth-child(4n-1){
    background-color: #ddd;
}
.debug_table:nth-child(odd) div{
    overflow: unset !important;
}
.debug_table .td1{
    display: inline-block;
    float: left;
    border-top: 1px solid black;
    border-left: 1px solid black;
    width: 20px;
    box-sizing: border-box;
}
.debug_table .td2{
    display: inline-block;
    float: left;
    border-top: 1px solid black;
    border-left: 1px solid black;
    border-right: 1px solid black;
    width: calc(50% - 20px);
    box-sizing: border-box;
    vertical-align: top;
    max-height: 300px;
    overflow: scroll;
    white-space: break-spaces;
    word-wrap: anywhere;
    word-break: break-all;
}
.debug_table .td3{
    display: inline-block;
    float: left;
    border-top: 1px solid black;
    border-right: 1px solid black;
    width: 50%;
    box-sizing: border-box;
    vertical-align: top;
    max-height: 300px;
    overflow: scroll;
    white-space: break-spaces;
    word-wrap: anywhere;
    word-break: break-all;
}
        ";
        echo "</style>";
    }
    
//    echo "<div class='debug_table'>";
    foreach ($arr as $num => $item)
    {
        if( !$CLIFlag )
        {
            echo "<div class='debug_table'>"
                . "<div class='td1'>№</div>"
                . "<div class='td2'>Стек вызова</div>"
                . "<div class='td3'>Аргументы</div>"
                . "</div>";

            echo "<div class='debug_table'>"
                . "<div class='td1'>$num</div>"
                . "<div class='td2'>";
        }
        if(array_key_exists("file", $item) ) 
        { self::sp("Имя файла: " . $item["file"], self::DefaultStringTextColor, $CLIFlag); }
        if(array_key_exists("line", $item) ) 
        { self::sp("Номер строки: " . $item["line"], self::DefaultStringTextColor, $CLIFlag); }
        if(array_key_exists("class", $item) ) 
        { self::sp("Класс: " . $item["class"], self::DefaultStringTextColor, $CLIFlag); }
        if(array_key_exists("type", $item) ) 
        { self::sp("Тип вызова функции: " . $item["type"], self::DefaultStringTextColor, $CLIFlag); }
//            Если это вызов метода объекта, будет выведено [->].
//            Если это вызов статического метода класса, то [::].
//            Если это простой вызов функции, не выводится ничего.
        if(array_key_exists("function", $item) ) 
        { self::sp("Функция: " . $item["function"], self::DefaultStringTextColor, $CLIFlag); }
        if(array_key_exists("object", $item) ) 
        {
            self::sp("Объект:", self::DefaultStringTextColor, $CLIFlag);
            self::ip( $item["object"], self::DefaultInfoTextColor, $CLIFlag);
        }
        if( !$CLIFlag )
        {
            echo "</div>";
            echo "<div class='td3'>";
        }
        // array
        // При нахождении внутри функции, будет выведен список аргументов этой функции. 
        // Если внутри включаемого файла, будет выведен список включаемых файлов. 
        if(array_key_exists("args", $item) ) 
        { self::ap($item["args"], self::DefaultArrayTextColor, $CLIFlag); }
        if( !$CLIFlag )
        {
            echo "</div>";
            echo "</div>";
        }
    }
//    echo "</div>";
}

/**
 * функция для вывода отладочной информации
 * 
 * @param string $str - строка для вывода
 * @param string $color - цвет для вывода, red, yellow, blue... или #00ff00...
 * @param boolean $traceflag - флаг, который указывает выводить или нет информацию из стека вызовов
 * @param boolean $CLIFlag - если установлен в TRUE, 
 * то будет производится вывод для Command Line интерфеса,
 * без HTML оформления
 */
public static function dp($str, $color = self::DefaultDebugTextColor, $traceflag = false, $CLIFlag = null )
{
    if( defined("DEBUG") )
    {
        if( $CLIFlag === null ) { $CLIFlag = self::isCLI(); }

        if(!$CLIFlag) 
        {
            echo "<div class='trm_debug_info_div' style='max-width: 100%;'"
            . "<strong>[Debug block(" . self::$DebugBlocksCounter++ . ")]</strong>";
        }
        self::debugPrint($str, $color, $CLIFlag);
        if($traceflag) { self::printDebugTrace( debug_backtrace(), $CLIFlag); }
        if(!$CLIFlag) 
        {
            echo "</div>";
        }
    }
}

/**
 * Печатает содержимое строки $str в цвете $color в спец. тэгах <pre>...</pre> 
 * только если определена константа DEBUG
 * 
 * @param string $str - строка для вывода
 * @param string $color - цвет текста
 * @param boolean $CLIFlag - если установлен в TRUE, 
 * то будет производится вывод для Command Line интерфеса,
 * без HTML оформления
 */
public static function debugPrint($str, $color = self::DefaultDebugTextColor, $CLIFlag = null )
{
    if( $CLIFlag === null ) { $CLIFlag = self::isCLI(); }
    if( defined("DEBUG") )
    {
        if(!$CLIFlag) { echo "<pre style='color: {$color};'>"; }
        var_dump( $str );
        if(!$CLIFlag) { echo "</pre>"; }
        else { echo PHP_EOL; }
    }
}

/**
 * функция для вывода информации var_dump($str) в тегах <pre>,
 * если только скрипт не выполняется в окружении CommandLine (CLI)
 * 
 * @param mixed $str - строка для вывода
 * @param string $color - цвет для вывода, red, yellow, blue... или #00ff00...
 * @param boolean $CLIFlag - если установлен в TRUE, 
 * то будет производится вывод для Command Line интерфеса,
 * без HTML оформления
 */
public static function ip( $str, $color = self::DefaultInfoTextColor, $CLIFlag = null )
{
    if( $CLIFlag === null ) { $CLIFlag = self::isCLI(); }

    if( !$CLIFlag ){ echo "<pre style='color: {$color};'>"; }
    var_dump( $str );
    if( !$CLIFlag ){ echo "</pre>"; }
    else { echo PHP_EOL; }
}

/**
 * функция для вывода массива
 * 
 * @param array $arr - строка для вывода
 * @param string $color - цвет для вывода, red, yellow, blue... или #00ff00...
 * @param boolean $CLIFlag - если установлен в TRUE, 
 * то будет производится вывод для Command Line интерфеса,
 * без HTML оформления
 */
public static function ap( array $arr, $color = self::DefaultArrayTextColor, $CLIFlag = null )
{
    if( $CLIFlag === null ) { $CLIFlag = self::isCLI(); }

    if( !$CLIFlag ){ echo "<pre style='color: {$color};'>"; }
    print_r( $arr );
    if( !$CLIFlag ){ echo "</pre>"; }
    else { echo PHP_EOL; }
}

/**
 * функция для вывода строки
 * 
 * @param string $str - строка для вывода
 * @param string $color - цвет для вывода, red, yellow, blue... или #00ff00...
 * @param boolean $CLIFlag - если установлен в TRUE, 
 * то будет производится вывод для Command Line интерфеса,
 * без HTML оформления
 */
public static function sp( $str, $color = self::DefaultStringTextColor, $CLIFlag = null )
{
    if( $CLIFlag === null ) { $CLIFlag = self::isCLI(); }

    if( !$CLIFlag ){ echo "<pre style='color: {$color};'>"; }
    echo $str;
    if( !$CLIFlag ){ echo "</pre>"; }
    else { echo PHP_EOL; }
}

/**
 * русские названия в английский транслит с заменой пробелов на минусы - 
 * 
 * @param string $s - строка с русскими буквами, которые будут заменены на английский аналог
 * @param boolean $specialforurl - если этот флаг установлен в true, тогда комбинация 100х100 с русским хэ, поменяется на 100x100 с английским икс!
 * @param string $charset - набор символов. поумолчанию устанавливается в "utf-8"
 * @return string - строка в траслитерации без кирилических символов
 */
public static function translit($s, $specialforurl=false, $charset = "utf-8")
{
    $s = (string) $s; // преобразуем в строковое значение
    $s = strip_tags($s); // убираем HTML-теги
    $s = str_replace(array("\n", "\r"), "-", $s); // убираем перевод каретки

    // меняет русский х на английский X если включен флаг specialforurl для комбинация с цифрами, 
    // например для размеров 100х100
    if($specialforurl) 
    {
      $s = preg_replace("/([0-9]+)х([0-9]+)/U", '$1x$2', $s);
    }

    // $s = function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
    $s = mb_strtolower($s, $charset);
    $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z','и'=>'i','й'=>'j','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
    $s = preg_replace("/[^0-9a-z-_]/i", "-", $s); // очищаем строку от недопустимых символов
    $s = preg_replace("/\-\-+/", '-', $s); // удаляем повторяющиеся знаки -
    $s = trim($s, "-");
    return $s; // возвращаем результат
}

/**
 * конвертирует массив (или одну строку),
 * в массиве конвертируются только строковые значения,
 * вызывается рекурсивно для всех дочерних элементов массива
 * 
 * @param array|string &$array - ссылка на массив или строку, в результате выполнения функции меняется его содержимое
 * @param string $from - кодировка из которой конвертируется, например, windows-1251
 * @param string $to - кодировка в которую конвертируется, например, UTF-8
 * 
 * @return array|string - конвертированный массив или строка
 */
public static function conv(&$array, $from = "utf-8", $to = "windows-1251")
{
//    if( empty($array) ) { return $array; }
    $from = strtolower($from);
    $to = strtolower($to);

    if( empty($array) || $from === $to )
    {
        return $array;
    }

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

    return $array;
}

/**
 * Функция конвертации массива в XML объект.
 * На вход подается мульти-вложенный массив,
 * на выходе получается строка с валидным xml с помощью рекурсии
 *
 * @param array $data
 * @param string $rootNodeName - назвение коневого (или очередного в рекурсии) xml-узла
 * @param SimpleXMLElement $xml - используется рекурсивно
 * 
 * @return string XML - возвращает XML в виде строки
 */
public static function convertArrayToXml($data, $rootNodeName = 'data', $xml=null)
{
    if ($xml == null)
    {
        $xml = simplexml_load_string("<?xml version=\"1.0\" encoding=\"utf-8\"?><$rootNodeName />");
    }

    //цикл перебора массива 
    foreach($data as $key => $value)
    {
        // нельзя применять числовое название полей в XML
        if (is_numeric($key))
        {
            // поэтому делаем их строковыми по образцу unknownNode_123...
            $key = "unknownNode_{$key}";
        }

        // удаляем не латинские символы и не цифры
        $key = preg_replace('/[^a-z0-9]/i', '', $key);

        // если значение массива также является массивом то вызываем себя рекурсивно
        if (is_array($value))
        {
            // добавляет пустой узел XML и возвращает объект SimpleXMLElement (точнее ссылку на него)
            $node = $xml->addChild($key);
            // рекурсивный вызов
            self::convertArraytoXml($value, $rootNodeName, $node);
        }
        else
        {
            // добавляем один узел, 
            // преобразуя возможные символы в соответсвующие HTML-коды функцией htmlentities (например кавычки, апострофы и т.д.)
            $value = htmlentities($value);
            // добавляет узел XML с данными
            $xml->addChild($key,$value);
        }

    }

    return $xml->asXML();
}

/**
 * ПОЛИФИЛ!!!
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

/**
 * проверяет выполняется текущий скрип в Command Line
 * @return boolean
 */
public static function isCLI()
{
//echo "http_response_code() === false" . PHP_EOL;
    if( http_response_code() === false ) { return true; }
//echo "defined('STDIN')" . PHP_EOL;
    if( defined('STDIN') ) { return true; }
//echo "php_sapi_name() === 'cli'" . PHP_EOL;
    if( php_sapi_name() === 'cli' ) { return true; }
//echo "empty(_SERVER['REMOTE_ADDR']) &&" . PHP_EOL;
    if( empty($_SERVER['REMOTE_ADDR']) &&
        !isset($_SERVER['HTTP_USER_AGENT']) && 
        count($_SERVER['argv']) > 0) { return true; }
//echo "!array_key_exists('REQUEST_METHOD', _SERVER)" . PHP_EOL;
    if ( !array_key_exists('REQUEST_METHOD', $_SERVER) ) { return true; }

//echo "Nothing...<br>" . PHP_EOL;

    return false;
}

/**
 * @return string - протокол, например, HTTP или HTTPS
 */
public static function getServerProtcol()
{
    if( isset( $_SERVER["HTTP_X_FORWARDED_PROTO"] ) )
    {
        return filter_input(INPUT_SERVER, "HTTP_X_FORWARDED_PROTO");
    }
    else if( isset( $_SERVER["HTTP_X_REQUEST_SCHEME"] ) )
    {
        return filter_input(INPUT_SERVER, "HTTP_X_REQUEST_SCHEME");
    }
    else if( isset( $_SERVER["REQUEST_SCHEME"] ) )
    {
        return filter_input(INPUT_SERVER, "REQUEST_SCHEME");
    }

    return "http";
}


public static function isMobile()
{
    $TestStr = "/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/";
    
    $UserAgent = filter_input(INPUT_SERVER, "HTTP_USER_AGENT");
    preg_match($TestStr, $UserAgent, $Matches);

    if($Matches && count($Matches))
    {
        return $Matches[0];
    }
    return null;
}


} // TRMLib