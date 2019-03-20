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
protected $AuthCookieName;
/**
 * @var string - Путь, по которому будет перенаправлен не авторизованный пользователь
 */
protected $UnAuthURL;

/**
 * @param string $UnAuthURL - Путь, по которому будет перенаправлен не авторизованный пользователь
 */
public function __construct( $AuthCookieName, $UnAuthURL )
{
    $this->AuthCookieName = $AuthCookieName;
    $this->UnAuthURL = $UnAuthURL;
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
 * проверяет корректный cookie,
 * если отсутсвует,то перенаправляет на $UnAuthURL
 * 
 * @param Request $Request
 * @param RequestHandlerInterface $Handler
 */
public function process(Request $Request, RequestHandlerInterface $Handler)
{
    if( !$this->checkCookies() )
    {
        return new RedirectResponse( 
                $this->UnAuthURL . "?originating_uri=" . $Request->server->get("REQUEST_URI") );
        // $Response->send();
    }
    return $Handler->handle( $Request );
}

/**
 * проверяет наличие Cookies с данными об авторизации на машине клиента
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
 * проверяет наличие пользователя в БД
 * 
 * @param string $username - имя проверяемого пользователя
 * @return boolean - если пользователь найден аозвращает true
 */
abstract protected function checkUser($username);


} // TRMCookiesAuthMiddleware
