<?php

namespace TRMEngine\Controller;

use Symfony\Component\HttpFoundation\Request;
use TRMEngine\Cookies\TRMAuthCookie;
use TRMEngine\TRMController;

abstract class TRMLoginController extends TRMController
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
 * @param Request $Request - объект запроса
 * @param string $AuthCookieName - имя Cookie-фйла для авторизации
 * @param string $UnAuthURL - путь, по которому перенаправляется не авторизованный пользоватьель
 * @param string $OriginatingUriArgumentName - имя аргумента GET-запроса, в котором сохраняется исходный URI
 */
function __construct(
        Request $Request,
        $AuthCookieName = "", 
        $UnAuthURL = "/login", 
        $OriginatingUriArgumentName = "originating_uri" )
{
    parent::__construct($Request);
    $this->AuthCookieName = $AuthCookieName;
    $this->UnAuthURL = $UnAuthURL;
    $this->OriginatingUriArgumentName = $OriginatingUriArgumentName;
}

/**
 * вход - проверка пользователя и пароля и создание Cookie с авторизацией
 */
public function actionLogin()
{
    $this->setHeaders();

    $name = $this->Request->request->get("name");

    $password = $this->Request->request->get("password");
    // проверка пользователя и пароля
    // как правило checkPassword пробует в базе найти пользователя с таким паролем
    if( $this->checkPassword($name, $password) )
    {
        $this->renderLoginView();
        return true;
    }
    // при входе originating_uri передается из формы
    $uri = $this->Request->request->get( $this->OriginatingUriArgumentName, $this->DefaultUri );

    // если этот код выполняется, значит авторизовались, сохраняем cookie с текущим пользователем
    $cookie = new AuthCookie($name);

    $cookie->setauth();

    // теперь переадресуем на запрашиваемую страницу
    header("Location: {$uri}");
    exit;
}

/**
 * выход - удаление Cookie с авторизацией
 */
public function actionLogout()
{
    if( empty($this->AuthCookieName) )
    {
        $this->AuthCookieName = str_replace( ".", "_", $this->Request->server->get("REQUEST_URI") ) . "_auth";
    }
    
    $cookie = new TRMAuthCookie($this->AuthCookieName);
    $cookie->logout();
    //отключаем кэширование!
    header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
    $this->setHeaders();

    header("Location: {$this->UnAuthURL}");
    exit;
}

protected function setHeaders()
{
    // убираем кэширование
    header("Cache-Control: no-cache, no-store");
    header("Pragma: no-cache");
    header("Expires: Tue, 22 Jan 2000 21:45:35 GMT");
}

/**
 * пользовательская реализация,
 * должна отрисовывать форму входа
 */
abstract public function renderLoginView();

/**
 * пользовательская реализация функции,
 * должна проверять соответствие пароля и пользователя
 * 
 * @param string $name
 * @param string $password
 * @return boolean
 */
abstract public function checkPassword($name, $password);


} // TRMLoginController