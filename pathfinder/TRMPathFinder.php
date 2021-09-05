<?php

namespace TRMEngine\PathFinder;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use TRMEngine\PathFinder\Exceptions\TRMPathNotFoundedException;
use TRMEngine\PipeLine\Interfaces\MiddlewareInterface;
use TRMEngine\PipeLine\Interfaces\RequestHandlerInterface;


/**
 * класс для выбора контроллера и Action для данного пути
 * 
 * @author TRM 2018
 */
class TRMPathFinder implements MiddlewareInterface
{
  /**
   * @var string - имя контроллера по умлочанию
   */
  const DefaultControllerName = "Main";
  /**
   * @var string - имя action-функции по умолчанию
   */
  const DefaultActionName = "Index";
  /**
   * @var array - текущий путь разобранный на $CurrentPath["controller"] = string, $CurrentPath["action"] = string, 
   * возможно $CurrentPath["param"] и другие параметры из Route
   */
  public static $CurrentPath = array();
  /**
   * @var Request - массивы _GET, _POST, _SERVER и т.д.
   */
  public $Request;


  /**
   * 
   * @param RouteCollection $Routes
   */
  public function __construct(RouteCollection $Routes)
  {
    $this->Routes = $Routes;
  }

  /**
   * возвращает текущий (подобранный) маршрут для данного URL
   * 
   * @return array
   */
  static public function getCurrentPath()
  {
    return self::$CurrentPath;
  }

  /**
   * Преобразует часть URL в CamelCase, убирая дефисы
   * 
   * @param string $Name - имя для преобразования
   * @return string - преобразованная и очищенная от нежелательных символов строка
   */
  static protected function sanitizeName($Name)
  {
    return str_replace(" ", "", ucwords(str_replace("-", " ", filter_var($Name, FILTER_SANITIZE_URL))));
  }

  /**
   * поиск нужных контроллера и его метода (Action) для данного пути из URI,
   * заполняет атрибуты "controller", "action" и "param" в объекте Request
   * 
   * @param Request $Request
   * @param RequestHandlerInterface $Handler
   * @return ResponseInterface
   * @throws Exceptions
   */
  public function process(Request $Request, RequestHandlerInterface $Handler)
  {
    $Context = new RequestContext();

    $Context->fromRequest($Request);

    $Matcher = new UrlMatcher($this->Routes, $Context);

    try {
      $parameters = $Matcher->match($Context->getPathInfo());
    } catch (ResourceNotFoundException $e) {
      throw new TRMPathNotFoundedException($Request->getPathInfo());
    }

    $this->generateCurrentPathFromParameters($parameters);

    foreach (self::$CurrentPath as $key => $val) {
      $Request->attributes->set($key, $val);
    }

    return $Handler->handle($Request);
  }

  /**
   * все параметры из найденного Route сохранет в локальном self::$CurrentPath,
   * имя контроллера сохраняется под индексом controller, вместо _controller
   * 
   * @param array $parameters
   */
  protected function generateCurrentPathFromParameters(array $parameters)
  {
    self::$CurrentPath['controller'] = self::sanitizeName($parameters["_controller"]);
    self::$CurrentPath['action'] = self::DefaultActionName;
    self::$CurrentPath['param'] = "";

    foreach ($parameters as $key => $val) {
      if ($key === "_controller") {
        continue;
      } else if ($key === "action") {
        self::$CurrentPath['action'] = self::sanitizeName($parameters["action"]);
      } else if ($key === "param") {
        self::$CurrentPath['param'] = $parameters["param"]; // для совместимости со старой версией
      } else {
        self::$CurrentPath[$key] = $val;
      }
    }
  }
}  // TRMPathFinder
