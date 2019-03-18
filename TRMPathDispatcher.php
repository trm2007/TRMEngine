<?php

namespace TRMEngine;

use TRMEngine\TRMPipeLine\RequestHandlerInterface;

use TRMEngine\Exceptions\TRMExceptionControllerNotFound;
use TRMEngine\Exceptions\TRMExceptionActionNotFound;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * запускает выплонение найденного в TRMPathFinder контроллера с нужным методом!
 */
class TRMPathDispatcher implements RequestHandlerInterface
{

/**
 * {@inheritDoc}
 * дл€ найденного маршрута создает экземпл€р контроллера и вызывает его функцию-action
 * 
 * @throws TRMExceptionControllerNotFound
 * @throws TRMExceptionActionNotFound
 */
public function handle( Request $Request )
{
    $Controller = $Request->attributes->get('controller') . "Controller";
    
    if( !class_exists($Controller) )
    {
        throw new TRMExceptionControllerNotFound( $Controller );
    }

    try
    {
        // если дл€ выбранного Action требуетс€ выполнить функцию отличную от имени actionAction,
        // то выбрасываетс€ исключение, где контроллер сообщает кака€ функци€ должна быть вызвана...
        $TRMObject = new $Controller( $Request );
    }
    catch (\TRMEngine\Exceptions\TRMMustStartOtherAction $e)
    {
        $Request->attributes->set( 'relevant-action', $Request->attributes->get( 'action' ) );
        $Request->attributes->set( 'action', $e->getActionName() );
        $TRMObject = new $Controller( $Request );
    }

    $Action = "action" . $Request->attributes->get('action');

    if( !method_exists($TRMObject, $Action) )
    {
        throw new TRMExceptionActionNotFound( $Controller, $Action, $Request->attributes->get('param') );
    }

    ob_start();
    $TRMObject->$Action();
    return new Response( ob_get_clean() );
}


} // TRMPathDispatcher
