<?php

namespace TRMEngine\TRMPipeLine;

use Symfony\Component\HttpFoundation\Request;

/**
 * реализация посредника для выполнения обработки запроса с помощью обычной функции,
 * 
 */
final class TRMCallableMiddleware implements MiddlewareInterface
{
/**
 * @var callable
 */
private $MiddleWare;


public function __construct( callable $MiddleWare )
{
    $this->MiddleWare = $MiddleWare;
}
/**
 * {@inheritDoc}
 * 
 * @param Request $Request
 * @param \TRMEngine\TRMPipeLine\RequestHandlerInterface $Handler
 * @return Response
 * @throws Exception
 */
public function process( Request $Request, RequestHandlerInterface $Handler )
{
    $MiddleWare = $this->MiddleWare;
    $Response = $MiddleWare($Request, $Handler);
    if( !($Response instanceof Response) )
    {
        throw new Exception( __METHOD__ . " Обработчик возвращает объект отличный от Response" ); //$this->MiddleWare);
    }
    return $Response;
}


} // TRMCallableMiddleware