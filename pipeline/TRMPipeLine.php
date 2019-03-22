<?php

namespace TRMEngine\PipeLine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TRMEngine\PipeLine\Interfaces\MiddlewareInterface;
use TRMEngine\PipeLine\Interfaces\RequestHandlerInterface;

/**
 * TRMPipeLine - собирает обработчики в массив и вызывает последовательно
 *
 * @author TRM
 * @version 2018-12-22
 */
class TRMPipeLine implements RequestHandlerInterface, MiddlewareInterface
{
/**
 * очередь объектов для типа MiddlewareInterface, каждый из которых реализует механизм посредника
 * @var \SplQueue
 */
protected $PipeLine;
/**
 * послдений Action-Middleware,
 * который будет вызван, если очередь уже пуста, можно передавать заглушку, с обработчиком страницы 404
 * @var RequestHandlerInterface
 */
private $LastAction;

/**
 * 
 * @param \TRMEngine\TRMPipeLine\RequestHandlerInterface $LastAction
 */
public function __construct( RequestHandlerInterface $LastAction )
{
    $this->PipeLine = new \SplQueue();
    $this->LastAction = $LastAction;
}

/**
 * при клонировании объекта, клонируется так же очередь,
 * поэтому в каждом клонированном TRMPipeLine будут свои очереди
 */
public function __clone()
{
    $this->PipeLine = clone $this->PipeLine;
}

/**
 * добавляет объект $MiddleWare в очередь
 * 
 * @param MiddlewareInterface $MiddleWare
 */
public function pipe( MiddlewareInterface $MiddleWare )
{
    $this->PipeLine->enqueue( $MiddleWare );
}

/**
 * метод обработки запроса $Request,
 * так как объект TRMPipeLine отвечает при обработке запроса за вызов всех MiddleWare объекто из очереди,
 * то для того , чтобы избежать опустощения очереди при каждом вызове этой функуии объект сначала клонируется,
 * тем самым клонируется и очередь, и уже внутри опустошается...
 * 
 * @param Request $Request
 * @return Response
 * @throws \Exception - если попытаться вызвать этот обработчик для пустой очереди, то выбрасывается исключение,
 * так как задача TRMPipeLine обеспечить вызов обработчиков по цепочки из заранее сформированной очереди,
 * никаких других действий для обработк $Request не производится!
 */
public function handle( Request $Request )
{
    $Next = new TRMNext( clone $this->PipeLine, $this->LastAction );
    return $Next->handle( $Request );


////////////// Zend version //////////////////////    
//    if( $this->PipeLine->isEmpty() )
//    {
//        throw new \Exception( __METHOD__ . " Вызван обработчик запроса с пустой очередью PipeLine" );
//    }
//
//    // клонируем объект, что бы сохранить первоначальную очередь обработчиков
//    $NextHandler = clone $this;
//    // из очереди извлекается первый обработчик 
//    $MiddleWare = $NextHandler->pipeline->dequeue();
//    // вызывает обработку (метод process) для извлеченного $MiddleWare
//    return $MiddleWare->process($Request, $NextHandler);
}

/**
 * Вызывает цепочку обработчиков, находящихся в очереди PipeLine,
 * при этом очередь не опустощается, для передачи во внутренний объект TRMNext она клонируется!!!
 * 
 * @param Request $Request
 * @param \TRMEngine\TRMPipeLine\RequestHandlerInterface $LastAction
 * @return Response
 */
public function process( Request $Request, RequestHandlerInterface $LastAction )
{
    // при создании объекта, передается клон очереди, который будет опустощаться внутри объекта TRMNext
    $Next = new TRMNext( clone $this->PipeLine, $LastAction );
    return $Next->handle( $Request );
}


} // TRMPipeLine
