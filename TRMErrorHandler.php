<?php

namespace TRMEngine;

use TRMEngine\Helpers\TRMLib;

/**
 * класс обработки ошибок и записи лог-файла
 * 
 * @author TRM 2018
 */
class TRMErrorHandler
{
const DefaultErrorFileName = "error_log.txt";
/**
 * @var array - массив с кодами ошибок
 */
protected $ErrorCodeArray = array(
    404 => "Not Found",
    500 => "Internal Server Error",
    503 => "Service Unavailable",
);
/**
 * @var array - массив с путями к файлам: с логами, общим обработчиком, каждой ошибки по ее коду - 404, 503 и т.д.
 */
protected $Config = array();


/**
 * @param string $filename - путь к файлу настроек (массив с путями к файлам: с логами, общим обработчиком, каждой ошибки по ее коду - 404, 503 и т.д.)
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
    // устанавливаем обработчик не фатальных ошибок
    set_error_handler(array($this, "ErrorHandler"));
    // включаем буферизацию вывода, что бы не отображались стандартные сообщения PHP
    ob_start();
    // подключаем функцию, которая вызывается при завершении работы скрипта (в данном случае после возниконовения фатальной ошибки)
    register_shutdown_function(array($this, "FatalErrorHandler"));
    // подключаем обработчик исключений, которые не были перехвачены в в скриптах с помощью catch(...)
    set_exception_handler(array($this, "ExceptionHandler"));
}

/*
 * функция перехвата и обработки необработанных исключений
 * 
 * @param \Exception $e
 */
public function ExceptionHandler($e) //\Exception $e)
{
    $this->displayError("Exception",$e->getMessage(),$e->getFile(),$e->getLine(),$e->getCode());
}

/**
 * функция перехвата и обработки ошибок уровня E_NOTICE и E_WARNING...
 * 
 * @param int $errno - номер ошибки PHP
 * @param string $errstr - сообщение об ошибке PHP
 * @param string $errfile - имя файла, в котором произошла ошибка
 * @param int $errline - строка в файле, на которой произошла ошибка
 * 
 * @return boolean - если данный обработчик вызывается после ошибки вызванной в функции с @, 
 * то возвращается false , и ошибка обарабтывается стандартными средствами PHP, код продложает выполнятся дальше, 
 * иначе возвращается true, но перед этим вызывается displayError, а он в данной версии завершает работу скрипта путем die
 */
public function ErrorHandler($errno,$errstr,$errfile,$errline)
{
    if(error_reporting() === 0)
    {
        error_log("[".date("Y-m-d H:i:s")."] Ошибка - (".$errno.") : ".$errstr."   в файле: ".$errfile."  в строке: ".$errline."\n******************************************\n",
                3,
                isset( $this->Config["errorreporfilename"] ) ? $this->Config["errorreporfilename"] : str_replace( "//", "/", __DIR__ . "/" . self::DefaultErrorFileName ) );
        return false;
    }
    
    $this->displayError($errno,$errstr,$errfile,$errline);
    return true;
}

/**
 * функция перехвата и обработки фатальных ошибок уровня E_ERROR и др...
 */
public function FatalErrorHandler()
{
    $error = error_get_last();
    //если ошибки есть, то сбрасываем буфер вывода, что бы не показывать стандартные сообщения, и выводим свое описание ошибки
    if(!empty($error) && $error["type"] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR) )
    {
        if(ob_get_length()>0) { ob_end_clean(); }
        $this->displayError($error["type"],$error["message"],$error["file"],$error["line"]);
    }
    else // если ошибок нет, то выводим содержимое буфера
    {
        if(ob_get_length()>0) { ob_end_flush(); }
    }
}

/**
 * отображает ошибки на экране и перенаправляет на страницу с ошибкой
 * 
 * @param integer $errno - номер ошибки
 * @param string $errstr - описание ошибки
 * @param string $errfile - файл, в котором произолша ошибка
 * @param string $errline - строка, на которой произошла ошибка
 * @param integer $rescode - код ответа сервера
 */
protected function displayError($errno,$errstr,$errfile,$errline,$rescode=503)
{
    if( isset($this->Config["errorreporfilename"]) )
    {
        error_log("[".date("Y-m-d H:i:s")."] Ошибка - (".$errno.") : ".$errstr."   в файле: ".$errfile."  в строке: ".$errline."\n******************************************\n",3, $this->Config["errorreporfilename"]);
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
    echo "<h1>Режим отладки включен</h1>" . PHP_EOL;

    echo "error_reporting: ".ini_get("error_reporting")."<br>" . PHP_EOL;
    echo "display_errors: ".ini_get('display_errors')."<br>" . PHP_EOL;

    echo "<pre>" . PHP_EOL;
    echo "Номер (тип) ошибки: <b>{$errno}</b><br>" . PHP_EOL;
    echo "Файл в котором произошла ошибка: <b>{$errfile}</b><br>" . PHP_EOL;
    echo "Строка на которой произошла ошибка: <b>{$errline}</b><br>" . PHP_EOL;
    echo "Код: <b>{$rescode}</b><br>" . PHP_EOL;
    echo "</pre>" . PHP_EOL;
    echo "</div>";

    TRMLib::dp($errstr, TRMLib::DefaultDebugTextColor, true);
}

/**
 * формирует заголовок ответа с заданным кодом $rescode
 * 
 * @param integer $rescode - код ответа сервера
 */
protected function makeHeader($rescode)
{
    if( !empty($argc) || 
        ( "cli" == php_sapi_name() ) ||
        ( !isset($_SERVER['DOCUMENT_ROOT']) && !isset($_SERVER['REQUEST_URI']) ) )
    { echo "Ошибка при запуске из командной строки<br>\n"; return; }

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
        echo "Уже был вывод и заголовки установить не удается!<br>";
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
