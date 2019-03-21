<?php

namespace TRMEngine\Cookies;

use TRMEngine\Cookies\Exceptions\TRMAuthCookieException;


class TRMAuthCookie extends TRMCookie
{ 
/**
 * @var time время создания
 */
private $created;
/**
 * @var string имя пользователя
 */
private $username;
/**
 * @var int версия созданного cookie-фала авторизации
 */
private $version;
/**
 * @var string имя cookie 
 */
protected $cookiename = "";
/**
 * @var int версия cookie
 */
protected static $myversion = "3";
/**
 * @var time  срок действия cookie (0 - на 1 год )
 */
protected static $expiration = 0;
/**
 * @var int период повторного выпуска cookie в сек.
 */
protected static $warning   = 30;
/**
 * @var char разделитель значений в текущем cookie
 */
protected static $glue = '|';

/**
 * @param string $cookiename - имя cookie для авторизации, желательно создавать один для всего проекта
 * @param string $username - имя пользователя
 */
public function __construct( $cookiename, $username = null ) 
{
    $this->cookiename = $cookiename;
    if( $username )
    {
        $this->username = $username;
    }
    else
    {
        $tmpcookie = parent::get($this->cookiename);
        if( $tmpcookie )
        {
            $this->_unpackage($tmpcookie);
        }
        else
        {
            $this->version = self::$myversion;
            $this->created = time();
            $this->username = "";
        }
    }
}

/**
 * устанавливаем cookie для аторизации
 * 
 * @throws TRMAuthCookieException
 */
public function setauth()
{
    $this->_reissue();
    $cookie = $this->_package();

    if( !parent::set($this->cookiename, $cookie, (self::$expiration>0) ? ($this->created+self::$expiration) : (365*24*60*60) ) )
    {
        throw new TRMAuthCookieException( "Не могу создать COOKIE " . $this->cookiename );
    }
}

/**
 * получаем имя текущего пользователя 
 * 
 * @return string
 */
public function getUser()
{
    return $this->username;
}

/**
 * проверяем правильность cookie для авторизации
 * 
 * @throws TRMAuthCookieException
 */
public function validate()
{
    if( !is_string( $this->username ) )
    {
        throw new TRMAuthCookieException("Cookie содержит недопустимое имя пользователя");
    }

    if ($this->version != self::$myversion)
    {
        throw new TRMAuthCookieException("Несоответствие версии");
    }

    if ( self::$expiration>0 && (time() - $this->created) > self::$expiration)
    {
        throw new TRMAuthCookieException("Истек срок действия cookie");
    }
    else if ( (time() - $this->created) > self::$warning)
    {
        $this->setauth();
    }
}

/**
 * выход - просто удаляем cookie
 */
public function logout()
{
    $this->delete( $this->cookiename );
    $this->created = 0;
}

/**
 * упаковываем cookie
 * 
 * @return string сериализованную строку с cookie
 */
private function _package()
{
    $parts = array(self::$myversion, $this->created, $this->username);
    $cookie = base64_encode( implode(self::$glue, $parts) );

    return $cookie;
}

/**
 * распаковываем cookie
 * 
 * @param string $cookie
 * @throws TRMAuthCookieException
 */
private function _unpackage($cookie)
{
    $buffer = base64_decode($cookie);

    list($this->version, $this->created, $this->username) = explode(self::$glue, $buffer);

    if($this->version != self::$myversion)
    {
        throw new TRMAuthCookieException("Не совпадает версия cookie");
    }
    if(!$this->created)
    {
        throw new TRMAuthCookieException("Неверное время создания Cookie");
    }
    if(!$this->username)
    {
        throw new TRMAuthCookieException("Не удалось распознать пользователя");
    }
}

/**
 * обновляет время сессии
 */
private function _reissue()
{
    $this->created = time();
}


} // TRMAuthCookie
