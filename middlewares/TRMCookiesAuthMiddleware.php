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
 * @var string - имя аргумента в GET-зпросе с адресом-URI,
 * куда будет направлен пользователь после авторизации,
 * устанавливается в конструкторе наследуемого
 */
protected $OriginatingUriArgumentName;
/**
 * @var string - имя Cookie в котором будет хранится информации об авторизации пользователя,
 * если не задан явно, будет сформирован автоматически из www_имясайта_ru_auth
 */
protected $AuthCookieName;
/**
 * @var string - путь, по которому будет перенаправлен не авторизованный пользователь,
 * как правило это страница для ввода логина и пароля - /login
 */
protected $UnAuthURL;

/**
 * @param string $AuthCookieName - имя Cookie-фйла для авторизации
 * @param string $UnAuthURL - путь, по которому перенаправляется не авторизованный пользоватьель
 * @param string $OriginatingUriArgumentName - имя аргумента GET-запроса, в котором сохраняется исходный URI
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
 * проверяет корректный cookie,
 * если отсутсвует,то перенаправляет на $UnAuthURL
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
 * проверяет наличие Cookies с данными об авторизации на машине клиента
 */
protected function checkCookies()
{
    try
    {
        $cookie = new TRMAuthCookie($this->AuthCookieName);
        
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
 * @return boolean - если пользователь найден возвращает true
 */
abstract protected function checkUser($username);


} // TRMCookiesAuthMiddleware
