<?php
namespace TRMEngine;

use Symfony\Component\HttpFoundation\Request;

use TRMEngine\Exceptions\NoControllerException;
use TRMEngine\Exceptions\NoActionException;
/**
 * базовый класс дл€ всех контроллеров
 */
abstract class TRMController
{
const DefaultPageName = "page";
/**
 * @var array сюда сохран€етс€ весь массив данных (controller - actinon - param - ...) из PathFinder-a
 */
protected $path;
/**
 * @var Request - объект запроса от клиента
 */
protected $Request;
/**
 * @var TRMView - объект вида дл€ текущего отображени€ данных, 
 * по концепции контроллер разбирает запрос из адреса URL - это одна страница,
 * и вид должен быть один, может быть собран из разных данных
 */
protected $view;
/**
 * @var integer - номер страницы, если есть пагинаци€
 */
protected $page = 1;
/**
 * @var string - им€ текущего Action
 */
protected $CurrentAction = '';
/**
 * @var string - им€ текущего Controller
 */
protected $CurrentControllerName = '';

protected $ConfigArray; //ссылка на массив с конфигурационными данными

public function __construct(Request $Request)
{
    $this->Request = $Request;
    // дл€ удобства выдел€ем им€ Controller в отдельную переменную
    $this->CurrentControllerName = $this->Request->attributes->get("controller");
    if( empty($this->CurrentControllerName) )
    {
        throw new NoControllerException( __METHOD__ . " Ќе правильно проинициализирован Controller", 404);
    }
    // дл€ удобства выдел€ем им€ Action в отдельную переменную
    $this->CurrentActionName = $this->Request->attributes->get("action");
    if( !isset($this->CurrentActionName) )
    {
        throw new NoActionException( __METHOD__ . " Ќе указан Action", 404);
    }

    // а так же дл€ удобства выдел€ем номер страницы (дл€ пагинации) в отдельную переменную
    $this->page = $this->Request->query->getInt(defined ("PAGE_NUMERIC_NAME") ? PAGE_NUMERIC_NAME : self::DefaultPageName, 1);
}


} // TRMController