<?php

namespace TRMEngine\Controller;

use Symfony\Component\HttpFoundation\Request;
use TRMEngine\Controller\Exceptions\TRMNoActionException;
use TRMEngine\Controller\Exceptions\TRMNoControllerException;
use TRMEngine\TRMView;


/**
 * базовый класс дл€ всех контроллеров
 */
abstract class TRMController
{
/**
 * @var string - указывает какой аргумент добавл€етс€ к запросу дл€ указани€ номера страницы при пагинации
 */
static $DefaultPageName = "page";

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


public function __construct(Request $Request)
{
    $this->Request = $Request;
    // дл€ удобства выдел€ем им€ Controller в отдельную переменную
    $this->CurrentControllerName = $this->Request->attributes->get("controller");
    if( empty($this->CurrentControllerName) )
    {
        throw new TRMNoControllerException( __METHOD__ . " Ќеправильно проинициализирован Controller", 404);
    }
    // дл€ удобства выдел€ем им€ Action в отдельную переменную
    $this->CurrentActionName = $this->Request->attributes->get("action");
    if( empty($this->CurrentActionName) )
    {
        throw new TRMNoActionException( __METHOD__ . " Ќе указан Action", 404);
    }

    // а так же дл€ удобства выдел€ем номер страницы (дл€ пагинации) в отдельную переменную
    $this->page = $this->Request->query->getInt( self::DefaultPageName, 1);
}


} // TRMController