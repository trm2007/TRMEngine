<?php

namespace TRMEngine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TRMEngine\Exceptions\TRMActionNotFoundedException;
use TRMEngine\Exceptions\TRMControllerNotFoundedException;
use TRMEngine\Exceptions\TRMMustStartOtherActionException;
use TRMEngine\TRMPipeLine\RequestHandlerInterface;

/**
 * ��������� ���������� ���������� � TRMPathFinder ����������� � ������ �������!
 */
class TRMPathDispatcher implements RequestHandlerInterface
{

/**
 * {@inheritDoc}
 * ��� ���������� �������� ������� ��������� ����������� � �������� ��� �������-action
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
        // ���� ��� ���������� Action ��������� ��������� ������� �������� �� ����� actionAction,
        // �� ������������� ����������, ��� ���������� �������� ����� ������� ������ ���� �������...
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
