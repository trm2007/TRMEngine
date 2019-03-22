<?php
namespace TRMEngine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TRMEngine\PipeLine\Interfaces\MiddlewareInterface;
use TRMEngine\PipeLine\Interfaces\RequestHandlerInterface;
use TRMEngine\PipeLine\TRMPathMiddlewareDecorator;
use TRMEngine\PipeLine\TRMPipeLine;

/**
 * основной класс для приложения, позволяет задавать цепочки для последовательного выполнения MiddlewareInterface->process()
 *
 * @author TRM
 */
class TRMApplication implements RequestHandlerInterface
{
/**
 *
 * @var TRMPipeLine - Цепочка посредников (Middleware)
 */
private $PipeLine;


public function __construct( RequestHandlerInterface $LastAction )
{
    $this->PipeLine = new TRMPipeLine( $LastAction );
}

/**
 * 
 * @param MiddlewareInterface $Middleware
 * @param type $Path
 */
public function pipe( MiddlewareInterface $Middleware, $Path = null )
{
    if( $Path === null )
    {
        $this->PipeLine->pipe( $Middleware );
    }
    else
    {
        $this->PipeLine->pipe(new TRMPathMiddlewareDecorator( $Path, $Middleware ) );
    }
}

/**
 * {@inheritDoc}
 * @param Request $Request
 * @return Response
 */
public function handle( Request $Request )
{
    return $this->PipeLine->handle($Request );
}
    
    
} // TRMApplication