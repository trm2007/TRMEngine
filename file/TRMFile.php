<?php

namespace TRMEngine\File;

use TRMEngine\Helpers\TRMState;

/**
 * класс для работы с файлом - запись данных в файл из буфера, чтение данных в буфер из файла
 * 
 * @author TRM
 */
class TRMFile extends TRMState
{
/**
 * количество попыток дозаписать в сетевой поток, 
 * если с первого раза не получилось записать строку полностью
 */
const FwriteTryCount = 10;
/**
 * @var string - только имя файла, без указания пути
 */
protected $FileName = "";
/**
 * @var string - путь к файлу (директория-папка), без имени самого файла
 */
protected $FileDir = "";
/**
 * @var string - полное имя файла, включая путь
 */
protected $FullPath = "";
/**
 * @var resource  - дескриптор отурытого файла, возвращаемый fopen
 */
protected $Handle = null;
/**
 * @var string - режим открытого файла 'r', 'w', 'a', 'w+' и т.д....
 */
protected $Mod = "r";
/**
 * @var string - строковый буфер данных для записи в файл, или для сохранения данных из файла
 */
protected $Buffer = "";

/**
 * @var string - содержит текст последней ошибки
 */
protected $ErrorString="";


public function __construct($fullpath = null)
{
    if( isset($fullpath) ) { $this->setFullPath($fullpath); }
}

public function __destruct()
{
    $this->closeFile();
}

/**
 * @param string $name - только имя файл. без указания директроии
 * @return boolean
 */
public function setFileName($name)
{
    if( is_string($name) )
    {
        $this->FileName = basename($name);
    }
    else { $this->StateString = "передано неверное имя файла name"; return false; }

    $this->generateFullPath();

    $this->StateString = "";
    return true;
}

/**
 * @param string $name - директория-папка, в которой находится файл
 * @return boolean
 */
public function setFileDir($name)
{
    if( is_string($name) )
    {
        $this->FileDir = $name;
    }
    else { $this->StateString = "передано неверное директории name"; return false; }

    $this->generateFullPath();

    $this->StateString = "";
    return true;
}

/**
 * указываем полное имя файла
 * разбираем на части и сохраняем в отдельных переменных имя и путь 
 * 
 * @param string $fullpath
 * @return boolean
 */
public function setFullPath($fullpath)
{
    if( empty($fullpath) ) { $this->StateString = "передан пустой fullpath"; return false; }
    
    $fileinfo = pathinfo( $fullpath ); //, PATHINFO_DIRNAME | PATHINFO_BASENAME );
    $this->FileName = $fileinfo["basename"];
    $this->FileDir = $fileinfo["dirname"];

    $this->generateFullPath();

    $this->StateString = "";
    return true;
}

/**
 * вспомогательная функция, генерирует полный путь к файлу на основе FileDir и FileName
 */
private function generateFullPath()
{
    $this->FullPath = rtrim($this->FileDir,"/") . "/" . trim($this->FileName,"/");
}

/**
 * возвращает полный путь к фалу FullPath
 * 
 * @return string
 */
public function getFullPath()
{
    return $this->FullPath;
}

/**
 * помещаем данные в буфер, приводятся к строке
 * 
 * @param strin $buffer - помещаемые в буфер данные
 */
public function setBuffer($buffer)
{
    $this->Buffer = (string)$buffer;
}
/**
 * @return string - буфер, содержащий данные прочитанные, или подготовленные для записи в файл
 */
public function getBuffer()
{
    return $this->Buffer;
}

/**
 * @return integer - размер данных в строковом буфере, 
 * для получения размера массива используйте getArraySize
 */
public function getBufferSize()
{
    return strlen($this->Buffer);
}

/**
 * дубавляем в буфер данные, если наш буфер пустой, то заполняем его =,
 * если уже что-то есть, то дополняем +=
 * 
 * @param string $buffer - добавляемые данные
 */
public function addToBuffer($buffer)
{
    if( empty($this->Buffer) ){ $this->Buffer = (string)$buffer; }
    else{ $this->Buffer .= (string)$buffer; }
}

/**
 * очищает строковый буфер
 */
public function clearBuffer()
{
    $this->Buffer = "";
}

/**
 * открывает файл, предварительно проверяя имя, если передано, а так же наличие открываемого файла.
 * по умолчанию открывает для чтения - 'r'
 * 
 * @param string $filename - имя файла
 * @param string $mod - 'r' Открывает файл только для чтения; помещает указатель в начало файла. 
 * 'r+' Открывает файл для чтения и записи; помещает указатель в начало файла. 
 * 'w' Открывает файл только для записи; помещает указатель в начало файла и обрезает файл до нулевой длины. Если файл не существует - пробует его создать. 
 * 'w+' Открывает файл для чтения и записи; помещает указатель в начало файла и обрезает файл до нулевой длины. Если файл не существует - пытается его создать. 
 * 'a' Открывает файл только для записи; помещает указатель в конец файла. Если файл не существует - пытается его создать. В данном режиме функция fseek() не применима, записи всегда добавляются. 
 * 'a+' Открывает файл для чтения и записи; помещает указатель в конец файла. Если файл не существует - пытается его создать. В данном режиме функция fseek() влияет только на место чтения, записи всегда добавляются. 
 * 
 * @return boolean
 */
public function openFile($filename = "", $mod = 'r')
{
    // setFullPath меняет значение $this->StateString
    if( !empty($filename) ) { if( !$this->setFullPath($filename) ) { return false; } }

    if( !$this->FullPath ) { $this->StateString = "FullPath не задан"; return false; }

    // на всякий случай пытаемся закрыть файл
    $this->closeFile();

    $this->Handle = fopen($this->FullPath, $mod);
    if(!$this->Handle){ $this->StateString = "Файл открыть не удалось"; return false; }

    $this->Mod = $mod;
    
    $this->StateString = "";
    return true;
}

/**
 * закрывает открытый файл, устанавливает дескриптор файла в null
 */
public function closeFile()
{
    if($this->Handle) { fclose($this->Handle); }

    $this->Handle = null;
}

/**
 * заполняем буфер содержимым из файла $filename,
 * получает содержимое, даже если файл не был открыт ранее,
 * 
 * @param string $filename - имя файла, которое нужно прочитать, если не указано,
 * будет произведена попытка получит содержимое файла заданного в FullPath данного объекта TRMFile
 * 
 * @return boolean
 */
public function getAllFileToBuffer($filename = "")
{
    if( empty($filename) ) { $filename = $this->getFullPath(); }
    if( !is_file($filename) ) { $this->StateString = "Имя файла указано не верно {$filename}"; return false; }
    $this->clearBuffer();
    $this->Buffer = file_get_contents($filename);
    if( $this->Buffer === false ){ $this->StateString = "Не удалось прочитать содержимое файла {$filename}"; return false; }
    
    $this->StateString = "";
    return true;
}

/**
 * получает очередную строку из открытого файла и помещает ее в строковый буфер
 * можно указать количество считываемых байт, по умолчанию читает строку, либо 4096 байта если она длиннее, либо до конца файла
 * 
 * @param int $length - количество байт, которое будет считано из файла
 * @return boolean - если файл прочитан удачно, то вернется true,
 * иначе false, также если файл не был открыт вернется false
 */
public function getStringToBufferFrom($length = 4096)
{
    if( !$this->Handle ){ $this->StateString = "файл не открыт"; return false; }

    $str = fgets($this->Handle, $length);
    if(!$str) { $this->StateString = "не удалось прочитать очередную строку"; return false; }

    $this->addToBuffer($str);

    $this->StateString = "";
    return true;
}

/**
 * считывает указанное количество байт из открытого файла в буфер,
 * либо пока не достигнет конца файла,
 * 
 * @param int $length - количество байт, которое будет считано из файла
 * @return boolean - если файл прояитан удачно, то вернется true,
 * иначе false, также если файл не был открыт вернется false
 */
public function getToBuffer($length = 8192)
{
    if( !$this->Handle ){ $this->StateString = "файл не открыт"; return false; }

    $str = fread($this->Handle, $length);
    if(!$str) { $this->StateString = "не удалось прочитать {$length} байт из файла"; return false; }

    $this->addToBuffer($str);

    $this->StateString = "";
    return true;
}

/**
 * записывает содержимое буфера в файл.
 * если он не открыт, то пытаестя открыть его для перезаписи, при этом все старые данные в фале будут утеряны!
 * возвращается количество записанных байт, 0 - если буфер пуст и false в случае ошибки
 * 
 * @return boolean|int
 */
public function putBufferTo()
{
    return $this->writeStringTo($this->Buffer, "w");
}

/**
 * записывает содержимое буфера в файл.
 * если он не открыт, то пытаестя открыть его для добавления данных в конец файла!
 * возвращается количество записанных байт, 0 - если буфер пуст и false в случае ошибки
 * 
 * @return boolean|int
 */
public function addBufferTo()
{
    return $this->writeStringTo($this->Buffer, "a");
}

/**
 * записывает содержимое буфера в открытый файл
 * 
 * @param &string $string - чтобы не дублировать данные, передаем указатель на строку
 * @param string $mod - режим: "a" добавления в файл, или "w" перезаписи всего содержимого
 * 
 * @return int|false - количество записанных байт, или false в случае ошибки, 
 * для проверки результата нужно использовать "==="
 */
private function writeStringTo(&$string, $mod)
{
    if( empty($string) ) { $this->StateString = "пустая строка"; return 0; }

    $closeflag = false;
    if( !$this->Handle )
    {
        // $this->FullPath - проверяется в функции $this->openFile
        //if( !$this->FullPath ) { $this->StateString="FullPath не задан"; return false; }
        if( !$this->openFile( $this->FullPath, $mod ) ) { return false; }
        $closeflag = true;
    }
    // Запись в сетевой поток может прекратиться до того, как будут записаны все данные,
    // fwrite_stream позволяет сделать несколько попыток
    $fwritesize = self::fwrite_stream($this->Handle, $string);

    if($closeflag) { $this->closeFile(); }

    return $fwritesize;    
}

/**
 * Запись в сетевой поток может прекратиться до того, как будут записаны все данные.
 * Это можно контролировать с помощью проверки возвращаемого значения функции fwrite().
 * Данная функция - fwrite_stream 
 * пытается записать данные снова и снова, если они не записаны с превого раза,
 * кол-во попыток ограничено $count
 * 
 * @param resource $fp - Указатель (resource) на файл, обычно создаваемый с помощью функции fopen().
 * @param string $string - Записываемая строка.
 * @param int $count - кол-во попыток дозаписать в поток
 * 
 * @return int - возвращает количество записанных байт, если не было записано ни одного байте вернется ноль
 */
public static function fwrite_stream( $fp, &$string, $count = self::FwriteTryCount )
{
    $fwritesize = 0;
    // пока функция fwrite_stream не запишет количество байт равное длине передаваемой строки вызываем ее снова и снова,
    // либо пока количество попыток не сравняется со значением $count,
    // т.е. цикл сработает не более $count-раз
    for( $written = 0; $written < strlen($string) && $count > 0; $written += $fwritesize, --$count )
    {
        // записываем 
        // в файл, открытый через $fp ,
        // подстроку из $string 
        // начиная с $written-ого байта
        $fwritesize = fwrite($fp, substr($string, $written));
        if( $fwritesize === false ) { break; }
    }
    return $written;
}

/**
 * @return int - размер файла, если файл не открыт, то вернется ноль
 */
public function getFileSize()
{
    $this->StateString = "";
    if($this->FullPath) { return filesize($this->FullPath); }
    $this->StateString = "имя файла не установлено";
    return 0;
}

/**
 * проверяет наличие файла,
 * 
 * @param string $filename - имя файла, если $filename не установлено, 
 * то проверяется наличие файла с установленным ранее FullPath для этого объекта 
 * 
 * @return boolean
 */
public function existFile($filename = "")
{
    if( empty($filename) ) { $filename = $this->getFullPath(); }
    if( is_file($filename) === false ) { $this->StateString = "файл не найден {$filename}"; return false; }
    
    $this->StateString = "";
    return true;
}

/**
 * проверяет достигнут ли конец файла, 
 * если дескриптор Handle не свзан с фалом (файл фактически не открыт), 
 * то так же вернется TRUE
 * 
 * @return boolean - TRUE - если указатель файла находится вконце, либо файл не открыт, FALSE если указатель файла еще не достиг конца
 */
public function isEOF()
{
    if($this->Handle) { return feof($this->Handle); }
    return true;
}

/**
 * сохраняет содержимое по указанному URL в строковый массив
 *
 * @param string $url - URL откуда будут получены данные
 * @param int $connect_timeout - время, отведенное на попытку соединения с URL (в секундах)
 * @param int $timeout - время, отведенное на загрузку всего контента
 *
 * @return int|boolean - возвращает размер загруженных данных в байтах, либо false в случае неудачного открытия потока.
 * Чтобы отличить false от нуля (0) результат функции следует сравнивать оператором "==="
 */
public function getBufferFromURL($url, $connect_timeout=10, $timeout=120)
{
    $this->StateString = "";

    $ch = curl_init();
    if( !$ch || curl_errno($ch) ) { return false; }

    curl_setopt($ch, CURLOPT_URL, $url);
    // CURLOPT_HEADER - если true, то заголовки будут включены в содержимое ответа, false - то заголовок не будет включен в содержимое
    curl_setopt($ch, CURLOPT_HEADER, false);
    // CURLOPT_RETURNTRANSFER - если установлен в true, то при вызове curl_exec данные сохраняются в памяти, если в false, то содержимое отправляется клиенту
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // CURLOPT_TIMEOUT - время, которое отводится для загрузки данных с указанного url
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    // CURLOPT_CONNECTTIMEOUT - время на соединение с сервером
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connect_timeout);

    $this->clearBuffer();
    $this->Buffer = curl_exec($ch);

    // проверяем, что отклик сервера был правильным (не вернулось содержимое 404-й страницы или ошибки 5хх сервера, 
    // и что в буфере есть данные, а не false
    if( curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200 || $this->Buffer === false )
    {
        $this->StateString = "при запросе curl_getinfo вернулся неверный результат";
        return false;
    }

    curl_close($ch);
    return $this->getBufferSize();
}

/**
 * возвращает строку с описанием последней возникшей ошибки
 * 
 * @return string
 */
public function getErrorString()
{
    return $this->StateString;
}

} // TRMFile