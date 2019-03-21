<?php

namespace TRMEngine\Cookies;

use TRMEngine\Cookies\Exceptions\TRMAuthCookieException;


class TRMAuthCookie extends TRMCookie
{ 
/**
 * @var time ����� ��������
 */
private $created;
/**
 * @var string ��� ������������
 */
private $username;
/**
 * @var int ������ ���������� cookie-���� �����������
 */
private $version;
/**
 * @var string ��� cookie 
 */
protected $cookiename = "";
/**
 * @var int ������ cookie
 */
protected static $myversion = "3";
/**
 * @var time  ���� �������� cookie (0 - �� 1 ��� )
 */
protected static $expiration = 0;
/**
 * @var int ������ ���������� ������� cookie � ���.
 */
protected static $warning   = 30;
/**
 * @var char ����������� �������� � ������� cookie
 */
protected static $glue = '|';

/**
 * @param string $cookiename - ��� cookie ��� �����������, ���������� ��������� ���� ��� ����� �������
 * @param string $username - ��� ������������
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
 * ������������� cookie ��� ����������
 * 
 * @throws TRMAuthCookieException
 */
public function setauth()
{
    $this->_reissue();
    $cookie = $this->_package();

    if( !parent::set($this->cookiename, $cookie, (self::$expiration>0) ? ($this->created+self::$expiration) : (365*24*60*60) ) )
    {
        throw new TRMAuthCookieException( "�� ���� ������� COOKIE " . $this->cookiename );
    }
}

/**
 * �������� ��� �������� ������������ 
 * 
 * @return string
 */
public function getUser()
{
    return $this->username;
}

/**
 * ��������� ������������ cookie ��� �����������
 * 
 * @throws TRMAuthCookieException
 */
public function validate()
{
    if( !is_string( $this->username ) )
    {
        throw new TRMAuthCookieException("Cookie �������� ������������ ��� ������������");
    }

    if ($this->version != self::$myversion)
    {
        throw new TRMAuthCookieException("�������������� ������");
    }

    if ( self::$expiration>0 && (time() - $this->created) > self::$expiration)
    {
        throw new TRMAuthCookieException("����� ���� �������� cookie");
    }
    else if ( (time() - $this->created) > self::$warning)
    {
        $this->setauth();
    }
}

/**
 * ����� - ������ ������� cookie
 */
public function logout()
{
    $this->delete( $this->cookiename );
    $this->created = 0;
}

/**
 * ����������� cookie
 * 
 * @return string ��������������� ������ � cookie
 */
private function _package()
{
    $parts = array(self::$myversion, $this->created, $this->username);
    $cookie = base64_encode( implode(self::$glue, $parts) );

    return $cookie;
}

/**
 * ������������� cookie
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
        throw new TRMAuthCookieException("�� ��������� ������ cookie");
    }
    if(!$this->created)
    {
        throw new TRMAuthCookieException("�������� ����� �������� Cookie");
    }
    if(!$this->username)
    {
        throw new TRMAuthCookieException("�� ������� ���������� ������������");
    }
}

/**
 * ��������� ����� ������
 */
private function _reissue()
{
    $this->created = time();
}


} // TRMAuthCookie
