<?php

namespace TRMEngine\File;

use TRMEngine\Helpers\TRMState;

/**
 * ����� ��� ������ � ������ - ������ ������ � ���� �� ������, ������ ������ � ����� �� �����
 * 
 * @author TRM
 */
class TRMFile extends TRMState
{
/**
 * ���������� ������� ���������� � ������� �����, 
 * ���� � ������� ���� �� ���������� �������� ������ ���������
 */
const FwriteTryCount = 10;
/**
 * @var string - ������ ��� �����, ��� �������� ����
 */
protected $FileName = "";
/**
 * @var string - ���� � ����� (����������-�����), ��� ����� ������ �����
 */
protected $FileDir = "";
/**
 * @var string - ������ ��� �����, ������� ����
 */
protected $FullPath = "";
/**
 * @var resource  - ���������� ��������� �����, ������������ fopen
 */
protected $Handle = null;
/**
 * @var string - ����� ��������� ����� 'r', 'w', 'a', 'w+' � �.�....
 */
protected $Mod = "r";
/**
 * @var string - ��������� ����� ������ ��� ������ � ����, ��� ��� ���������� ������ �� �����
 */
protected $Buffer = "";

/**
 * @var string - �������� ����� ��������� ������
 */
protected $ErrorString="";


public function __construct($fullpath = null)
{
    if( isset($fullpath) ) { $this->setFullPath($fullpath); }
    $this->Handle = null;
}

public function __destruct()
{
    $this->closeFile();
}

/**
 * @param string $name - ������ ��� ����. ��� �������� ����������
 * @return boolean
 */
public function setFileName($name)
{
    if( is_string($name) )
    {
        $this->FileName = basename($name);
    }
    else { $this->StateString = "�������� �������� ��� ����� name"; return false; }

    $this->generateFullPath();

    $this->StateString = "";
    return true;
}

/**
 * @param string $name - ����������-�����, � ������� ��������� ����
 * @return boolean
 */
public function setFileDir($name)
{
    if( is_string($name) )
    {
        $this->FileDir = $name;
    }
    else { $this->StateString = "�������� �������� ���������� name"; return false; }

    $this->generateFullPath();

    $this->StateString = "";
    return true;
}

/**
 * ��������� ������ ��� �����
 * ��������� �� ����� � ��������� � ��������� ���������� ��� � ���� 
 * 
 * @param string $fullpath
 * @return boolean
 */
public function setFullPath($fullpath)
{
    if( empty($fullpath) ) { $this->StateString = "������� ������ fullpath"; return false; }
    
    $fileinfo = pathinfo( $fullpath ); //, PATHINFO_DIRNAME | PATHINFO_BASENAME );
    $this->FileName = $fileinfo["basename"];
    $this->FileDir = $fileinfo["dirname"];

    $this->generateFullPath();

    $this->StateString = "";
    return true;
}

/**
 * ��������������� �������, ���������� ������ ���� � ����� �� ������ FileDir � FileName
 */
private function generateFullPath()
{
    $this->FullPath = rtrim($this->FileDir,"/") . "/" . trim($this->FileName,"/");
}

/**
 * ���������� ������ ���� � ���� FullPath
 * 
 * @return string
 */
public function getFullPath()
{
    return $this->FullPath;
}

/**
 * �������� ������ � �����, ���������� � ������
 * 
 * @param strin $buffer - ���������� � ����� ������
 */
public function setBuffer($buffer)
{
    $this->Buffer = (string)$buffer;
}
/**
 * @return string - �����, ���������� ������ �����������, ��� �������������� ��� ������ � ����
 */
public function getBuffer()
{
    return $this->Buffer;
}

/**
 * @return integer - ������ ������ � ��������� ������, 
 * ��� ��������� ������� ������� ����������� getArraySize
 */
public function getBufferSize()
{
    return strlen($this->Buffer);
}

/**
 * ��������� � ����� ������, ���� ��� ����� ������, �� ��������� ��� =,
 * ���� ��� ���-�� ����, �� ��������� +=
 * 
 * @param string $buffer - ����������� ������
 */
public function addToBuffer($buffer)
{
    if( empty($this->Buffer) ){ $this->Buffer = (string)$buffer; }
    else{ $this->Buffer .= (string)$buffer; }
}

/**
 * ������� ��������� �����
 */
public function clearBuffer()
{
    $this->Buffer = "";
}

/**
 * ��������� ����, �������������� �������� ���, ���� ��������, � ��� �� ������� ������������ �����.
 * �� ��������� ��������� ��� ������ - 'r'
 * 
 * @param string $filename - ��� �����
 * @param string $mod - 'r' ��������� ���� ������ ��� ������; �������� ��������� � ������ �����. 
 * 'r+' ��������� ���� ��� ������ � ������; �������� ��������� � ������ �����. 
 * 'w' ��������� ���� ������ ��� ������; �������� ��������� � ������ ����� � �������� ���� �� ������� �����. ���� ���� �� ���������� - ������� ��� �������. 
 * 'w+' ��������� ���� ��� ������ � ������; �������� ��������� � ������ ����� � �������� ���� �� ������� �����. ���� ���� �� ���������� - �������� ��� �������. 
 * 'a' ��������� ���� ������ ��� ������; �������� ��������� � ����� �����. ���� ���� �� ���������� - �������� ��� �������. � ������ ������ ������� fseek() �� ���������, ������ ������ �����������. 
 * 'a+' ��������� ���� ��� ������ � ������; �������� ��������� � ����� �����. ���� ���� �� ���������� - �������� ��� �������. � ������ ������ ������� fseek() ������ ������ �� ����� ������, ������ ������ �����������. 
 * 
 * @return boolean
 */
public function openFile($filename = null, $mod = 'r')
{
    // setFullPath ������ �������� $this->StateString
    if( isset($filename) ) { if( !$this->setFullPath($filename) ) { return false; } }

    if( !$this->FullPath ) { $this->StateString = "FullPath �� �����"; return false; }

    // �� ������ ������ �������� ������� ����
    $this->closeFile();

    $this->Handle = fopen($this->FullPath, $mod);
    if(!$this->Handle){ $this->StateString = "���� ������� �� �������"; return false; }

    $this->Mod = $mod;
    
    $this->StateString = "";
    return true;
}

/**
 * ��������� �������� ����, ������������� ���������� ����� � null
 */
public function closeFile()
{
    if($this->Handle) { fclose($this->Handle); }

    $this->Handle = null;
}

/**
 * ��������� ����� ���������� �� ����� $filename,
 * �������� ����������, ���� ���� ���� �� ��� ������ �����,
 * 
 * @param string $filename - ��� �����, ������� ����� ���������
 * @return boolean
 */
public function getAllFileToBuffer($filename)
{
    if( !is_file($filename) ) { $this->StateString = "��� ����� ������� ����� {$filename}"; return false; }
    $this->clearBuffer();
    $this->Buffer = file_get_contents ($filename);
    if( !$this->Buffer ){ return false; }
    
    $this->StateString = "";
    return true;
}

/**
 * �������� ��������� ������ �� ��������� ����� � �������� �� � ��������� �����
 * ����� ������� ���������� ����������� ����, �� ��������� ������ ������, ���� 4096 ����� ���� ��� �������, ���� �� ����� �����
 * 
 * @param int $length - ���������� ����, ������� ����� ������� �� �����
 * @return boolean - ���� ���� �������� ������, �� �������� true,
 * ����� false, ����� ���� ���� �� ��� ������ �������� false
 */
public function getStringToBufferFrom($length = 4096)
{
    if( !$this->Handle ){ $this->StateString = "���� �� ������"; return false; }

    $str = fgets($this->Handle, $length);
    if(!$str) { $this->StateString = "�� ������� ��������� ��������� ������"; return false; }

    $this->addToBuffer($str);

    $this->StateString = "";
    return true;
}

/**
 * ��������� ��������� ���������� ���� �� ��������� ����� � �����,
 * ���� ���� �� ��������� ����� �����,
 * 
 * @param int $length - ���������� ����, ������� ����� ������� �� �����
 * @return boolean - ���� ���� �������� ������, �� �������� true,
 * ����� false, ����� ���� ���� �� ��� ������ �������� false
 */
public function getToBuffer($length = 8192)
{
    if( !$this->Handle ){ $this->StateString = "���� �� ������"; return false; }

    $str = fread($this->Handle, $length);
    if(!$str) { $this->StateString = "�� ������� ��������� {$length} ���� �� �����"; return false; }

    $this->addToBuffer($str);

    $this->StateString = "";
    return true;
}

/**
 * ���������� ���������� ������ � ����.
 * ���� �� �� ������, �� �������� ������� ��� ��� ����������, ��� ���� ��� ������ ������ � ���� ����� �������!
 * ������������ ���������� ���������� ����, 0 - ���� ����� ���� � false � ������ ������
 * 
 * @return boolean|int
 */
public function putBufferTo()
{
    return $this->writeStringTo($this->Buffer, "w");
}

/**
 * ���������� ���������� ������ � ����.
 * ���� �� �� ������, �� �������� ������� ��� ��� ���������� ������ � ����� �����!
 * ������������ ���������� ���������� ����, 0 - ���� ����� ���� � false � ������ ������
 * 
 * @return boolean|int
 */
public function addBufferTo()
{
    return $this->writeStringTo($this->Buffer, "a");
}

/**
 * ���������� ���������� ������ � �������� ����
 * 
 * @param &string $string - ����� �� ����������� ������, �������� ��������� �� �������
 * @param string $mod - �����: "a" ���������� � ����, ��� "w" ���������� ����� �����������
 * 
 * @return int|false - ���������� ���������� ����, ��� false � ������ ������, ��� �������� ���������� ����� ������������ "==="
 */
/**
 * 
 * @param type $string
 * @param type $mod
 * @return boolean|int
 */
private function writeStringTo(&$string, $mod)
{
    if( empty($string) ) { $this->StateString = "������ ������"; return 0; }

    $closeflag = false;
    if( !$this->Handle )
    {
        // $this->FullPath - ����������� � ������� $this->openFile
        //if( !$this->FullPath ) { $this->StateString="FullPath �� �����"; return false; }
        if( !$this->openFile( $this->FullPath, $mod ) ) { return false; }
        $closeflag = true;
    }
    // ������ � ������� ����� ����� ������������ �� ����, ��� ����� �������� ��� ������,
    // fwrite_stream ��������� ������� ��������� �������
    $fwritesize = self::fwrite_stream($this->Handle, $string);

    if($closeflag) { $this->closeFile(); }

    return $fwritesize;    
}

/**
 * ������ � ������� ����� ����� ������������ �� ����, ��� ����� �������� ��� ������.
 * ��� ����� �������������� � ������� �������� ������������� �������� ������� fwrite().
 * ������ ������� �������� �������� ������ ����� � �����, ���� ��� �� �������� � ������� ����,
 * ���-�� ������� ���������� $count
 * 
 * @param resource $fp - ��������� (resource) �� ����, ������ ����������� � ������� ������� fopen().
 * @param string $string - ������������ ������.
 * @param int $count - ���-�� ������� ���������� � �����
 * 
 * @return int - ���������� ���������� ���������� ����, ���� �� ���� �������� �� ������ ����� �������� ����
 */
public static function fwrite_stream( $fp, &$string, $count = self::FwriteTryCount )
{
    $fwritesize = 0;
    // ���� ������� fwrite_stream �� ������� ���������� ���� ������ ����� ������������ ������ �������� �� ����� � �����,
    // ���� ���� ���������� ������� �� ���������� �� ��������� $count,
    // �.�. ���� ��������� �� ����� $count-���
    for( $written = 0; $written < strlen($string) && $count > 0; $written += $fwritesize, --$count )
    {
        // ���������� 
        // � ����, �������� ����� $fp ,
        // ��������� �� $string 
        // ������� � $written-��� �����
        $fwritesize = fwrite($fp, substr($string, $written));
        if( $fwritesize === false ) { break; }
    }
    return $written;
}

/**
 * @return int - ������ �����, ���� ���� �� ������, �� �������� ����
 */
public function getFileSize()
{
    $this->StateString = "";
    if($this->FullPath) { return filesize($this->FullPath); }
    $this->StateString = "��� ����� �� ������";
    return 0;
}

/**
 * ��������� ��������� �� ����� �����, ���� ���������� Handle �� ����� � ����� (���� ���������� �� ������), �� ��� �� �������� TRUE
 * 
 * @return boolean - TRUE - ���� ��������� ����� ��������� ������, ���� ���� �� ������, FALSE ���� ��������� ����� ��� �� ������ �����
 */
public function isEOF()
{
    if($this->Handle) { return feof($this->Handle); }
    return true;
}

/**
 * ��������� ���������� �� ���������� URL � ��������� ������
 *
 * @param string $url - URL ������ ����� �������� ������
 * @param int $connect_timeout - �����, ���������� �� ������� ���������� � URL (� ��������)
 * @param int $timeout - �����, ���������� �� �������� ����� ��������
 *
 * @return int|boolean - ���������� ������ ����������� ������ � ������, ���� false � ������ ���������� �������� ������.
 * ����� �������� false �� ���� (0) ��������� ������� ������� ���������� ���������� "==="
 */
public function getBufferFromURL($url, $connect_timeout=10, $timeout=120)
{
    $this->StateString = "";

    $ch = curl_init();
    if( !$ch || curl_errno($ch) ) { return false; }

    curl_setopt($ch, CURLOPT_URL, $url);
    // CURLOPT_HEADER - ���� true, �� ��������� ����� �������� � ���������� ������, false - �� ��������� �� ����� ������� � ����������
    curl_setopt($ch, CURLOPT_HEADER, false);
    // CURLOPT_RETURNTRANSFER - ���� ���������� � true, �� ��� ������ curl_exec ������ ����������� � ������, ���� � false, �� ���������� ������������ �������
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // CURLOPT_TIMEOUT - �����, ������� ��������� ��� �������� ������ � ���������� url
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    // CURLOPT_CONNECTTIMEOUT - ����� �� ���������� � ��������
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connect_timeout);

    $this->clearBuffer();
    $this->Buffer = curl_exec($ch);

    // ���������, ��� ������ ������� ��� ���������� (�� ��������� ���������� 404-� �������� ��� ������ 5�� �������, 
    // � ��� � ������ ���� ������, � �� false
    if( curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200 || $this->Buffer === false )
    {
        $this->StateString = "��� ������� curl_getinfo �������� �������� ���������";
        return false;
    }

    curl_close($ch);
    return $this->getBufferSize();
}

/**
 * ���������� ������ � ��������� ��������� ��������� ������
 * 
 * @return string
 */
public function getErrorString()
{
    return $this->StateString;
}

} // TRMFile