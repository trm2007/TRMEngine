<?php

namespace TRMEngine\PipeLine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TRMEngine\PipeLine\Exceptions\TRMMiddlewareBadResponseException;
use TRMEngine\PipeLine\Interfaces\MiddlewareInterface;
use TRMEngine\PipeLine\Interfaces\RequestHandlerInterface;

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
 * @param RequestHandlerInterface $Handler
 * @return Response
 * @throws TRMMiddlewareBadResponseException
 */
public function process( Request $Request, RequestHandlerInterface $Handler )
{
    $MiddleWare = $this->MiddleWare;
    $Response = $MiddleWare($Request, $Handler);
    if( !($Response instanceof Response) )
    {
        throw new TRMMiddlewareBadResponseException( __METHOD__  ); 
    }
    return $Response;
}


} // TRMCallableMiddleware