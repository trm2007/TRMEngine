<?php

namespace TRMEngine\Image\Exceptions;

use TRMEngine\Exceptions\TRMException;

/**
 * Выбрасываются при работе с изображениями TRMImage
 */
class TRMImageExceptions extends TRMException
{
    /**
     * @param string $message
     * @param int $code
     * @param \Throwable $previous
     */
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка при работе с изображениями! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
}


class TRMImageNoDestImageException extends TRMImageExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Не сформировано результирующее! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
    
}

class TRMImageWrongBMPException extends TRMImageExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка при работе с BMP-файлом! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
    
}

class TRMImageWrongPNGException extends TRMImageExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка при работе с PNG-файлом! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
    
}

class TRMImageWrongGIFException extends TRMImageExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка при работе с GIF-файлом! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
    
}

class TRMImageWrongWBMPException extends TRMImageExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка при работе с WBMP-файлом! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
    
}

class TRMImageWrongJPEGException extends TRMImageExceptions
{
    public function __construct( $message = "", $code = 0, \Throwable $previous = NULL)
    {
        $message .= PHP_EOL . " Ошибка при работе с JPEG-файлом! " . PHP_EOL;
        parent::__construct($message, $code, $previous);
    }
    
}
