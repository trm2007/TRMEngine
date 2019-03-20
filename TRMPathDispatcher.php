<?php

namespace TRMEngine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TRMEngine\Exceptions\TRMActionNotFoundedException;
use TRMEngine\Exceptions\TRMControllerNotFoundedException;
use TRMEngine\Exceptions\TRMMustStartOtherActionException;
use TRMEngine\TRMPipeLine\RequestHandlerInterface;

/**
 * запускает выплонение найденного в TRMPathFinder контроллера с нужным методом!
 */
class TRMPathDispatcher implements RequestHandlerInterface
{

/**
 * {@inheritDoc}
 * дл€ найденного маршрута создает экземпл€р контроллера и вызывает его функцию-action
 * 
 * @throws TRMControllerNotFoundedException
 * @throws TRMActionNotFoundedException
 */
public function handle( Request $Request )
{
    $Controller = $Request->attributes->get('controller') . "Controller";
    
    if( !class_exists($Controller) )
    {
        throw new TRMControllerNotFoundedException( $Controller );
    }

    try
    {
        // если дл€ выбранного Action требуетс€ выполнить функцию отличную от имени actionAction,
        // то выбрасываетс€ исключение, где контроллер сообщает кака€ функци€ должна быть вызвана...
        $TRMObject = new $Controller( $Request );
    }
    catch (TRMMustStartOtherActionException $e)
    {
        $Request->attributes->set( 'relevant-action', $Request->attributes->get( 'action' ) );
        $Request->attributes->set( 'action', $e->getActionName() );
        $TRMObject = new $Controller( $Request );
    }

    $Action = "action" . $Request->attributes->get('action');

    if( !method_exists($TRMObject, $Action) )
    {
        throw new TRMActionNotFoundedException( $Controller, $Action, $Request->attributes->get('param') );
    }

    ob_start();
    $TRMObject->$Action();
    return new Response( ob_get_clean() );
}


} // TRMPathDispatcher
