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
 * @param string $username - имя пользователя, если не задано, 
 * то пытается получить уже записанный Cookie или оставляет пустым
 */
public function __construct( $cookiename, $username = "" ) 
{
    $this->cookiename = $cookiename;
    $this->version = self::$myversion;
    $this->created = time();
    $this->username = $username;

    if( empty($this->username) )
    {
        $tmpcookie = parent::get($this->cookiename);
        if( $tmpcookie )
        {
            $this->_unpackage($tmpcookie);
        }
    }
    else // если передано имя пользователя, проверяем на валидность
    {
        $this->validate();
    }
}

/**
 * устанавливаем cookie для аторизации,
 * можно передать новое имя пользователя для записив Cookie
 * 
 * @param string $username - можно установить нового пользователя
 * @throws TRMAuthCookieException
 */
public function setauth( $username = "" )
{
    if( !empty($username) )
    {
        $this->username = $username;
    }
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
 * устанавливает новое имя текущего пользователя,
 * 
 * @param string $username - можно установить нового пользователя
 */
public function setUser($username)
{
    $this->username = $username;
}

/**
 * проверяет правильность cookie для авторизации,
 * 
 * @throws TRMAuthCookieException
 */
public function validate()
{
    if( !is_string( $this->username ) )
    {
        throw new TRMAuthCookieException("Cookie авторизации содержит недопустимое имя пользователя!");
    }

    if( $this->version != self::$myversion )
    {
        throw new TRMAuthCookieException("Несоответствие версии Сookie авторизации!");
    }

    if( self::$expiration>0 && (time() - $this->created) > self::$expiration )
    {
        throw new TRMAuthCookieException("Истек срок действия Сookie авторизации!");
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
 * упаковывает cookie в строку для передачи клиенту,
 * перед упаковкой проверяет
 * 
 * @return string сериализованную строку с cookie
 */
private function _package()
{
    $this->validate();
    $parts = array(self::$myversion, $this->created, $this->username);
    $cookie = base64_encode( implode(self::$glue, $parts) );

    return $cookie;
}

/**
 * распаковывает строку с cookie полученную от клиента,
 * проверяет на валидность 
 * 
 * @param string $cookie
 */
private function _unpackage($cookie)
{
    $buffer = base64_decode($cookie);

    list($this->version, $this->created, $this->username) = explode(self::$glue, $buffer);
    $this->validate();
}

/**
 * обновляет время Cookie
 */
private function _reissue()
{
    $this->created = time();
}


} // TRMAuthCookie
