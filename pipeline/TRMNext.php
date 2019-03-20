<?php

namespace TRMEngine\PipeLine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TRMEngine\PipeLine\Interfaces\RequestHandlerInterface;

/**
 * класс для обработки стека вызовов посредников из очереди
 * 
 * @author TRM
 * @version 2018-12-22
 */
final class TRMNext implements RequestHandlerInterface
{
/**
 * послдений Action-Middleware,
 * который будет вызван, если очередь уже пуста, можно передавать заглушку, с обработчиком страницы 404
 * @var RequestHandlerInterface
 */
private $LastAction;
/**
 * очередь посредников
 * @var SplQueue
 */
private $Queue;

/**
 * @param SplQueue $Queue - очередь с объектами Middleware, внутри опустошается, поэтому сюда нужно передавать клон 
 * @param \TRMEngine\TRMPipeLine\RequestHandlerInterface $LastAction - последний Middleware, который будет вызван если очередь пуста
 */
public function __construct( SplQueue $Queue, RequestHandlerInterface $LastAction )
{
    $this->Queue = $Queue;
    $this->LastAction = $LastAction;
}

/**
 * Реализует итерацию, вызывая все элементы Middleware из очереди $this->Queue,
 * в качестве LastAction каждому $MiddleWare предлсагается передавать дальнейшую обработку объекту $this,
 * именно этот подход реализует рекрсивный вызов
 * 
 * @param Request $Request
 * 
 * @return Response
 */
public function handle( Request $Request )
{
    if ($this->Queue->isEmpty())
    {
        return $this->LastAction->handle($Request);
    }

    $MiddleWare = $this->Queue->dequeue();
    return $MiddleWare->process($Request, $this);
}


} // TRMNext