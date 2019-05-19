<?php

namespace TRMEngine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TRMEngine\PipeLine\Interfaces\MiddlewareInterface;
use TRMEngine\PipeLine\Interfaces\RequestHandlerInterface;
use TRMEngine\PipeLine\TRMNoPathMiddlewareDecorator;
use TRMEngine\PipeLine\TRMPathMiddlewareDecorator;
use TRMEngine\PipeLine\TRMPipeLine;

/**
 * основной класс для приложения, 
 * позволяет задавать цепочки для последовательного выполнения MiddlewareInterface->process()
 */
class TRMApplication implements RequestHandlerInterface
{
/**
 * @var TRMPipeLine - Цепочка посредников (Middleware)
 */
private $PipeLine;

/**
 * @param RequestHandlerInterface $LastAction - послдений Action-Middleware,
 * который будет вызван, если очередь уже пуста, 
 */
public function __construct( RequestHandlerInterface $LastAction )
{
    $this->PipeLine = new TRMPipeLine( $LastAction );
}

/**
 * добавляет посредника в цепочку выполнения перед стартом основного $LastAction
 * 
 * @param MiddlewareInterface $Middleware - добавляемый посредник
 * @param string $Path - если указан, то будет выполняться, 
 * только если начало URI содержит данную часть ( шаблон пути )
 */
public function pipe( MiddlewareInterface $Middleware, $Path = null )
{
    if( $Path === null )
    {
        $this->PipeLine->pipe( $Middleware );
    }
    else
    {
        $this->PipeLine->pipe( new TRMPathMiddlewareDecorator( $Path, $Middleware ) );
    }
}

/**
 * добавляет посредника в цепочку выполнения перед стартом основного $LastAction
 * 
 * @param MiddlewareInterface $Middleware - добавляемый посредник
 * @param array $Path - массив строк и методов сравнения, 
 * данный посредник будет выполняться, 
 * только если начало URI НЕ содержит ни одну из перечисленных частей ( шаблонов пути )
 */
public function pipeNoPath( MiddlewareInterface $Middleware, array $Path )
{
    $this->PipeLine->pipe( new TRMNoPathMiddlewareDecorator( $Path, $Middleware ) );
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