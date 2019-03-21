<?php

namespace TRMEngine\Middlewares;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use TRMEngine\Cookies\Exceptions\TRMAuthCookieException;
use TRMEngine\Cookies\TRMAuthCookie;
use TRMEngine\PipeLine\Interfaces\MiddlewareInterface;
use TRMEngine\PipeLine\Interfaces\RequestHandlerInterface;

/**
 *
 * @author TRM
 * @version 2018-12-23
 */
abstract class TRMCookiesAuthMiddleware implements MiddlewareInterface
{
/**
 * @var string - ��� ��������� � GET-������ � �������-URI,
 * ���� ����� ��������� ������������ ����� �����������,
 * ��������������� � ������������ ������������
 */
protected $OriginatingUriArgumentName;
/**
 * @var string - ��� Cookie � ������� ����� �������� ���������� �� ����������� ������������,
 * ���� �� ����� ����, ����� ����������� ������������� �� www_��������_ru_auth
 */
protected $AuthCookieName;
/**
 * @var string - ����, �� �������� ����� ������������� �� �������������� ������������,
 * ��� ������� ��� �������� ��� ����� ������ � ������ - /login
 */
protected $UnAuthURL;

/**
 * @param string $AuthCookieName - ��� Cookie-���� ��� �����������
 * @param string $UnAuthURL - ����, �� �������� ���������������� �� �������������� �������������
 * @param string $OriginatingUriArgumentName - ��� ��������� GET-�������, � ������� ����������� �������� URI
 */
public function __construct( 
        $AuthCookieName = "", 
        $UnAuthURL = "/login", 
        $OriginatingUriArgumentName = "originating_uri" )
{
    $this->AuthCookieName = $AuthCookieName;
    $this->UnAuthURL = $UnAuthURL;
    $this->OriginatingUriArgumentName = $OriginatingUriArgumentName;
}

/**
 * @return string
 */
public function getUnAuthURL() {
    return $this->UnAuthURL;
}
/**
 * @param string $UnAuthURL
 */
public function setUnAuthURL($UnAuthURL) {
    $this->UnAuthURL = $UnAuthURL;
}
/**
 * @return string
 */
public function getAuthCookieName() {
    return $this->AuthCookieName;
}
/**
 * @param string $AuthCookieName
 */
public function setAuthCookieName($AuthCookieName) {
    $this->AuthCookieName = $AuthCookieName;
}

/**
 * ��������� ���������� cookie,
 * ���� ����������,�� �������������� �� $UnAuthURL
 * 
 * @param Request $Request
 * @param RequestHandlerInterface $Handler
 */
public function process(Request $Request, RequestHandlerInterface $Handler)
{
    if( empty($this->AuthCookieName) )
    {
        $this->AuthCookieName = str_replace( ".", "_", $Request->server->get("REQUEST_URI") ) . "_auth";
    }
    if( !$this->checkCookies() )
    {
        return new RedirectResponse( 
                $this->UnAuthURL 
                . "?{$this->OriginatingUriArgumentName}=" 
                . $Request->server->get("REQUEST_URI") );
    }
    return $Handler->handle( $Request );
}

/**
 * ��������� ������� Cookies � ������� �� ����������� �� ������ �������
 */
protected function checkCookies()
{
    try
    {
        $cookie = new TRMAuthCookie($this->AuthCookieName);
        $cookie->validate();
        
        $username = $cookie->getUser();
        
        if( empty($username) ) { return false; }
        
        return $this->checkUser($username);
    }
    catch( TRMAuthCookieException $e )
    {
        return false;
    }
    return true;
}

/**
 * ��������� ������� ������������ � ��
 * 
 * @param string $username - ��� ������������ ������������
 * @return boolean - ���� ������������ ������ ���������� true
 */
abstract protected function checkUser($username);


} // TRMCookiesAuthMiddleware
