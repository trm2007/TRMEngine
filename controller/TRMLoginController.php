<?php

namespace TRMEngine\Controller;

use Symfony\Component\HttpFoundation\Request;
use TRMEngine\Cookies\TRMAuthCookie;

/**
 * класс контроллера для входа и авторизации с использованием Cookie,
 * на стороне пользователя должны быть реализованы методы проверки пары - пользователь<=>пароль,
 * и метод-действие на случай, если нужно отбражать форму входа (не авторизован)
 * 
 * @version 2019-03-21
 */
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
 *
 * @var string - адрес, на который будет перенаправлен пользователь, 
 * при авторизации, если не задан OriginatingUri
 */
protected $DefaultUri;


/**
 * @param Request $Request - объект запроса
 * @param string $AuthCookieName - имя Cookie-фйла для авторизации
 * @param string $UnAuthURL - путь, по которому перенаправляется не авторизованный пользоватьель
 * @param string $OriginatingUriArgumentName - имя аргумента GET-запроса, в котором сохраняется исходный URI
 * @param string $DefaultUri - адрес, на который будет перенаправлен пользователь, если на задан OriginatingUri
 */
function __construct(
        Request $Request,
        $AuthCookieName = "", 
        $UnAuthURL = "/login", 
        $OriginatingUriArgumentName = "originating_uri",
        $DefaultUri = "/" )
{
    parent::__construct($Request);
    $this->AuthCookieName = $AuthCookieName;
    $this->UnAuthURL = $UnAuthURL;
    $this->OriginatingUriArgumentName = $OriginatingUriArgumentName;
    $this->DefaultUri = $DefaultUri;
}

/**
 * вход - проверка пользователя и пароля и создание Cookie с авторизацией
 */
public function actionLogin()
{
    $this->setHeaders();

    $cookie = new TRMAuthCookie( $this->AuthCookieName );
    
    $name = $cookie->getUser();
    
    if( empty($name) )
    {
        $name = $this->Request->request->get("name");

        $password = $this->Request->request->get("password");
        // проверка пользователя и пароля
        // как правило checkPassword пробует в базе найти пользователя с таким паролем
        if( !$this->checkPassword($name, $password) )
        {
            $this->renderLoginView();
            return true;
        }
        // если этот код выполняется, значит авторизовались

        // сохраняем cookie с текущим пользователем
        $cookie->setauth($name);
    }

    // при входе originating_uri передается через GET-запрос,
    // если такого аргумента нет, то происходит переадресация на DefaultUri
    $uri = $this->Request->request->get( $this->OriginatingUriArgumentName, $this->DefaultUri );
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

/**
 * формирует HTTP-заголовки для очистки кэша
 */
protected function setHeaders()
{
    header("Cache-Control: no-cache, no-store");
    header("Pragma: no-cache");
    header("Expires: Tue, 22 Jan 2000 21:45:35 GMT");
}

/**
 * пользовательская реализация,
 * в ней должны предприниматься действия, когда пользователь не авторизован и,
 * например, должен отображаться вид с формой входа
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
abstract protected function checkPassword($name, $password);


} // TRMLoginController