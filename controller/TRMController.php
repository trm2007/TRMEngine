<?php

namespace TRMEngine\Controller;

use Symfony\Component\HttpFoundation\Request;
use TRMEngine\Controller\Exceptions\TRMNoActionException;
use TRMEngine\Controller\Exceptions\TRMNoControllerException;
use TRMEngine\View\TRMView;

/**
 * базовый класс для всех контроллеров
 */
abstract class TRMController
{
/**
 * @var string - указывает какой аргумент добавляется к запросу для указания номера страницы при пагинации
 */
static $DefaultPageName = "page";

/**
 * @var Request - объект запроса от клиента
 */
protected $Request;
/**
 * @var TRMView - объект вида для текущего отображения данных, 
 * по концепции контроллер разбирает запрос из адреса URL - это одна страница,
 * и вид должен быть один, может быть собран из разных данных
 */
protected $view;
/**
 * @var integer - номер страницы, если есть пагинация
 */
protected $page = 1;
/**
 * @var string - имя текущего Action
 */
protected $CurrentAction = '';
/**
 * @var string - имя текущего Controller
 */
protected $CurrentControllerName = '';


public function __construct(Request $Request)
{
    $this->Request = $Request;
    // для удобства выделяем имя Controller в отдельную переменную
    $this->CurrentControllerName = $this->Request->attributes->get("controller");
    if( empty($this->CurrentControllerName) )
    {
        throw new TRMNoControllerException( __METHOD__ . " Неправильно проинициализирован Controller", 404);
    }
    // для удобства выделяем имя Action в отдельную переменную
    $this->CurrentActionName = $this->Request->attributes->get("action");
    if( empty($this->CurrentActionName) )
    {
        throw new TRMNoActionException( __METHOD__ . " Не указан Action", 404);
    }

    // а так же для удобства выделяем номер страницы (для пагинации) в отдельную переменную
    $this->page = $this->Request->query->getInt( static::$DefaultPageName, 1);
}


} // TRMController