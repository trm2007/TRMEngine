<?php

namespace TRMEngine\File;

/**
 * ��������� ������ � ������ TRMFile, 
 * �������� ����������� � ���������� �������� ��� � ���������� �������,
 * � ���������, � ���������� �����������, 
 * ����� ����� �������� foreach( TRMStringsFile->getArrayBuffer() ) ��� �������� ���� ���������� �����
 */
class TRMStringsFile extends TRMFile
{
/**
 * @var array - ���� ������ ����� 
 */
protected $ArrayBuffer = "";


/**
 * @return array - ���������� ������, ���������� �� ����� � ���� �������,
 * ������ ����� �� ������, ������ ����� ������ getEveryStringToArrayFrom(...),
 * ���� ���������� �������
 */
public function getArrayBuffer()
{
    return $this->ArrayBuffer;
}
/**
 * @param array $ArrayBuffer - ������������� ������ �� ��������, ��������� ����� �� �����������!!!
 */
public function setArrayBuffer($ArrayBuffer)
{
    $this->ArrayBuffer = $ArrayBuffer;
}

/**
 * @param string $str - ������, ������� ����� ��������� � ������ ArrayBuffer, �� �� � ��������� �����!!!
 */
public function addStringToArray($str)
{
    $this->ArrayBuffer[] = (string)$str;
}

/**
 * @return int - ���������� ���������� �����, ���������� � �������,
 * ��� ��������� ������� ������ ����������� - getBufferSize
 */
public function getArraySize()
{
    return count($this->ArrayBuffer);
}

/**
 * ������� ������ �� ��������
 */
public function clearArray()
{
    $this->ArrayBuffer = array();
}

/**
 * ��������� ������ ArrayBuffer ���������� �����, 
 * ������ ������ ������������ � ����� ������� �������
 * 
 * @param string $filename - ��� �����, ������� ����� ���������
 * @param int $option - ����� ������ ����� ��� ������� file(..., $option), 
 * ����������� �������� ����� ���������� ������ ������ FILE_SKIP_EMPTY_LINES
 * 
 * @return boolean
 */
public function getEveryStringToArrayFrom($filename, $option = FILE_SKIP_EMPTY_LINES)
{
    if( !is_file($filename) ) { echo "[��� ����� �� ����� {$filename}]"; return false; }
    $this->ArrayBuffer = array();
    $this->ArrayBuffer = file($filename, $option);
    if( !$this->ArrayBuffer ){ $this->ArrayBuffer = array(); return false; }
    
    return true;
}

/**
 * �������� ��������� ������ �� ��������� ����� � �������� �� � ������
 * ����� ������� ���������� ����������� ����, �� ��������� ������ ��� ������������, 
 * ���� 4096 ����� ���� ��� �������, ���� �� ����� �����
 * 
 * @param int $length
 * @return boolean
 */
public function getStringToArrayFrom($length = 4096)
{
    if( !$this->Handle ){ return false; }

    $str = fgets($this->Handle, $length);
    if(!$str)
    {
        return false;
    }
    $this->addToBuffer($str);
    return true;
}

/**
 * ���������� ������ ������ �� ������� � ����, ������ ������ ����� �������������� EOL .
 * ���� ���� �� ������, �� �������� ������� ��� � ������ $mod - ����������� ���������� ����� �����������...
 * ������������ ���������� ���������� ����, 0 - ���� ����� ���� � false � ������ ������
 * 
 * @param string $mod - �����: "a" ���������� � ����, ��� "w" ���������� ����� �����������
 * @return boolean|int
 */
public function putStringsArrayTo($mod = "w", $AddEolflag = true)
{
    if( empty($this->ArrayBuffer) ) { return 0; }
    if( !$this->Handle )
    {
        if( !$this->FullPath ) { return false; }
        if( !$this->openFile( $this->FullPath, $mod ) ) { return false; }
    }
    $count = 0;
    foreach( $this->ArrayBuffer as $str )
    {
        $str .= PHP_EOL;
        $count += strlen($str);
        if($AddEolflag) { self::fwrite_stream($this->Handle, $str ); }
        else { self::fwrite_stream($this->Handle, $str); }// fwrite( $this->Handle, $str ); }
    }
    return $count;
}


} // TRMStringsFile