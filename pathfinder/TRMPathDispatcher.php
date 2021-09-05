<?php

namespace TRMEngine\PathFinder;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TRMEngine\Controller\Exceptions\TRMMustStartOtherActionException;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\PathFinder\Exceptions\TRMActionNotFoundedException;
use TRMEngine\PathFinder\Exceptions\TRMControllerNotFoundedException;
use TRMEngine\PipeLine\Interfaces\RequestHandlerInterface;

/**
 * запускает выплонение найденного в TRMPathFinder контроллера с нужным методом!
 */
class TRMPathDispatcher implements RequestHandlerInterface
{
  /**
   * @var TRMDIContainer 
   */
  protected $DIC;

  public function __construct(TRMDIContainer $DIC)
  {
    $this->DIC = $DIC;
  }

  /**
   * {@inheritDoc}
   * для найденного маршрута создает экземпляр контроллера и вызывает его функцию-action
   * 
   * @throws TRMControllerNotFoundedException
   * @throws TRMActionNotFoundedException
   */
  public function handle(Request $Request)
  {
    $Controller = $Request->attributes->get('controller'); // . "Controller";

    if (!class_exists($Controller)) {
      throw new TRMControllerNotFoundedException($Controller);
    }

    try {
      // если для выбранного Action требуется выполнить функцию отличную от имени actionAction,
      // то выбрасывается исключение, где контроллер сообщает какая функция должна быть вызвана...
      $TRMObject = $this->DIC->get($Controller); // new $Controller( $Request );
    } catch (TRMMustStartOtherActionException $e) {
      $Request->attributes->set('relevant-action', $Request->attributes->get('action'));
      $Request->attributes->set('action', $e->getActionName());
      $TRMObject = $this->DIC->get($Controller); // new $Controller( $Request );
    }

    $Action = "action" . $Request->attributes->get('action');

    if (!method_exists($TRMObject, $Action)) {
      throw new TRMActionNotFoundedException($Controller, $Action, $Request->attributes->get('param'));
    }

    ob_start();
    //if( $Request->attributes->get('param') )
    $Res = $TRMObject->$Action($Request->attributes->get('param'));
    // методы могут возвращать готовый объект Response
    if (is_a($Res, Response::class)) {
      ob_get_clean();
      return $Res;
    }
    // либо выводить html-код напрямую 
    return new Response(ob_get_clean());
  }
} // TRMPathDispatcher
