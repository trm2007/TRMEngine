<?php

namespace TRMEngine;

use TRMEngine\Helpers\TRMLib;

/**
 * ����� ��������� ������ � ������ ���-�����
 * 
 * @author TRM 2018
 */
class TRMErrorHandler
{
const DefaultErrorFileName = "error_log.txt";
/**
 * @var array - ������ � ������ ������
 */
protected $ErrorCodeArray = array(
    404 => "Not Found",
    500 => "Internal Server Error",
    503 => "Service Unavailable",
);
/**
 * @var array - ������ � ������ � ������: � ������, ����� ������������, ������ ������ �� �� ���� - 404, 503 � �.�.
 */
protected $Config = array();


/**
 * @param string $filename - ���� � ����� �������� (������ � ������ � ������: � ������, ����� ������������, ������ ������ �� �� ���� - 404, 503 � �.�.)
 */
public function __construct( $filename = null )
{
    if( !empty($filename) && is_file($filename) )
    {
        $this->Config = require($filename);
    }

    if(defined("DEBUG") )
    {
        error_reporting(E_ALL);
        ini_set('error_reporting',E_ALL);
        ini_set('display_errors','1');
        ini_set('display_startup_errors','1');
    }
    else
    {
        error_reporting(0);
        ini_set('error_reporting','0');
        ini_set('display_errors','0');
        ini_set('display_startup_errors','0');
    }
    // ������������� ���������� �� ��������� ������
    set_error_handler(array($this, "ErrorHandler"));
    // �������� ����������� ������, ��� �� �� ������������ ����������� ��������� PHP
    ob_start();
    // ���������� �������, ������� ���������� ��� ���������� ������ ������� (� ������ ������ ����� �������������� ��������� ������)
    register_shutdown_function(array($this, "FatalErrorHandler"));
    // ���������� ���������� ����������, ������� �� ���� ����������� � � �������� � ������� catch(...)
    set_exception_handler(array($this, "ExceptionHandler"));
}

/*
 * ������� ��������� � ��������� �������������� ����������
 * 
 * @param \Exception $e
 */
public function ExceptionHandler($e) //\Exception $e)
{
    $this->displayError("Exception",$e->getMessage(),$e->getFile(),$e->getLine(),$e->getCode());
}

/**
 * ������� ��������� � ��������� ������ ������ E_NOTICE � E_WARNING...
 * 
 * @param int $errno - ����� ������ PHP
 * @param string $errstr - ��������� �� ������ PHP
 * @param string $errfile - ��� �����, � ������� ��������� ������
 * @param int $errline - ������ � �����, �� ������� ��������� ������
 * 
 * @return boolean - ���� ������ ���������� ���������� ����� ������ ��������� � ������� � @, 
 * �� ������������ false , � ������ �������������� ������������ ���������� PHP, ��� ���������� ���������� ������, 
 * ����� ������������ true, �� ����� ���� ���������� displayError, � �� � ������ ������ ��������� ������ ������� ����� die
 */
public function ErrorHandler($errno,$errstr,$errfile,$errline)
{
    if(error_reporting() === 0)
    {
        error_log("[".date("Y-m-d H:i:s")."] ������ - (".$errno.") : ".$errstr."   � �����: ".$errfile."  � ������: ".$errline."\n******************************************\n",
                3,
                isset( $this->Config["errorreporfilename"] ) ? $this->Config["errorreporfilename"] : str_replace( "//", "/", __DIR__ . "/" . self::DefaultErrorFileName ) );
        return false;
    }
    
    $this->displayError($errno,$errstr,$errfile,$errline);
    return true;
}

/**
 * ������� ��������� � ��������� ��������� ������ ������ E_ERROR � ��...
 */
public function FatalErrorHandler()
{
    $error = error_get_last();
    //���� ������ ����, �� ���������� ����� ������, ��� �� �� ���������� ����������� ���������, � ������� ���� �������� ������
    if(!empty($error) && $error["type"] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR) )
    {
        if(ob_get_length()>0) { ob_end_clean(); }
        $this->displayError($error["type"],$error["message"],$error["file"],$error["line"]);
    }
    else // ���� ������ ���, �� ������� ���������� ������
    {
        if(ob_get_length()>0) { ob_end_flush(); }
    }
}

/**
 * ���������� ������ �� ������ � �������������� �� �������� � �������
 * 
 * @param integer $errno - ����� ������
 * @param string $errstr - �������� ������
 * @param string $errfile - ����, � ������� ��������� ������
 * @param string $errline - ������, �� ������� ��������� ������
 * @param integer $rescode - ��� ������ �������
 */
protected function displayError($errno,$errstr,$errfile,$errline,$rescode=503)
{
    if( isset($this->Config["errorreporfilename"]) )
    {
        error_log("[".date("Y-m-d H:i:s")."] ������ - (".$errno.") : ".$errstr."   � �����: ".$errfile."  � ������: ".$errline."\n******************************************\n",3, $this->Config["errorreporfilename"]);
    }

    $this->makeHeader( intval($rescode) );
    if( isset($this->Config[$rescode]) && is_file($this->Config[$rescode]) )
    {
        require( $this->Config[$rescode] );
    }
    else if( isset($this->Config["commonerror"]) && is_file($this->Config["commonerror"]) )
    {
        require( $this->Config["commonerror"] );
    }
    if(defined("DEBUG") )
    {
        static::printErrorDebug($errno, $errstr, $errfile, $errline, $rescode);
    }
    exit();
}

static public function printErrorDebug($errno,$errstr,$errfile,$errline,$rescode)
{
    echo "<div class='trm_debug_info'>";
    echo "<h1>����� ������� �������</h1>" . PHP_EOL;

    echo "error_reporting: ".ini_get("error_reporting")."<br>" . PHP_EOL;
    echo "display_errors: ".ini_get('display_errors')."<br>" . PHP_EOL;

    echo "<pre>" . PHP_EOL;
    echo "����� (���) ������: <b>{$errno}</b><br>" . PHP_EOL;
    echo "���� � ������� ��������� ������: <b>{$errfile}</b><br>" . PHP_EOL;
    echo "������ �� ������� ��������� ������: <b>{$errline}</b><br>" . PHP_EOL;
    echo "���: <b>{$rescode}</b><br>" . PHP_EOL;
    echo "</pre>" . PHP_EOL;
    echo "</div>";

    TRMLib::dp($errstr, TRMLib::DefaultDebugTextColor, true);
}

/**
 * ��������� ��������� ������ � �������� ����� $rescode
 * 
 * @param integer $rescode - ��� ������ �������
 */
protected function makeHeader($rescode)
{
    if( !empty($argc) || 
        ( "cli" == php_sapi_name() ) ||
        ( !isset($_SERVER['DOCUMENT_ROOT']) && !isset($_SERVER['REQUEST_URI']) ) )
    { echo "������ ��� ������� �� ��������� ������<br>\n"; return; }

    if( !headers_sent($filename, $linenum) )
    {
        header('Cache-Control: no-cache', false, $rescode);
        date_default_timezone_set('UTC');
        header(sprintf('Date: %s GMT', date('D, d M Y H:i:s')), false, $rescode);

        $resstr = sprintf('%s %s', $_SERVER['SERVER_PROTOCOL'], $rescode );
        if( isset($this->ErrorCodeArray[$rescode]) ) { $resstr .= " " . $this->ErrorCodeArray[$rescode]; }
        header($resstr, true, $rescode);
    }
    else
    {
        echo "<pre>";
        echo "��� ��� ����� � ��������� ���������� �� �������!<br>";
        echo $filename;
        echo "<br>";
        echo $linenum;
        echo "</pre>";
    }

    if( function_exists('fastcgi_finish_request') )
    {
        fastcgi_finish_request();
    }
    else // if( 'cli' !== PHP_SAPI )
    {
        while(ob_get_level() > 0) { echo ob_get_clean(); /* ob_end_clean(); */ }
    }
}


} // TRMErrorHandler
