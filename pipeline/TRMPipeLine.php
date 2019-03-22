<?php

namespace TRMEngine\PipeLine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TRMEngine\PipeLine\Interfaces\MiddlewareInterface;
use TRMEngine\PipeLine\Interfaces\RequestHandlerInterface;

/**
 * TRMPipeLine - �������� ����������� � ������ � �������� ���������������
 *
 * @author TRM
 * @version 2018-12-22
 */
class TRMPipeLine implements RequestHandlerInterface, MiddlewareInterface
{
/**
 * ������� �������� ��� ���� MiddlewareInterface, ������ �� ������� ��������� �������� ����������
 * @var \SplQueue
 */
protected $PipeLine;
/**
 * ��������� Action-Middleware,
 * ������� ����� ������, ���� ������� ��� �����, ����� ���������� ��������, � ������������ �������� 404
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
 * ��� ������������ �������, ����������� ��� �� �������,
 * ������� � ������ ������������� TRMPipeLine ����� ���� �������
 */
public function __clone()
{
    $this->PipeLine = clone $this->PipeLine;
}

/**
 * ��������� ������ $MiddleWare � �������
 * 
 * @param MiddlewareInterface $MiddleWare
 */
public function pipe( MiddlewareInterface $MiddleWare )
{
    $this->PipeLine->enqueue( $MiddleWare );
}

/**
 * ����� ��������� ������� $Request,
 * ��� ��� ������ TRMPipeLine �������� ��� ��������� ������� �� ����� ���� MiddleWare ������� �� �������,
 * �� ��� ���� , ����� �������� ����������� ������� ��� ������ ������ ���� ������� ������ ������� �����������,
 * ��� ����� ����������� � �������, � ��� ������ ������������...
 * 
 * @param Request $Request
 * @return Response
 * @throws \Exception - ���� ���������� ������� ���� ���������� ��� ������ �������, �� ������������� ����������,
 * ��� ��� ������ TRMPipeLine ���������� ����� ������������ �� ������� �� ������� �������������� �������,
 * ������� ������ �������� ��� �������� $Request �� ������������!
 */
public function handle( Request $Request )
{
    $Next = new TRMNext( clone $this->PipeLine, $this->LastAction );
    return $Next->handle( $Request );


////////////// Zend version //////////////////////    
//    if( $this->PipeLine->isEmpty() )
//    {
//        throw new \Exception( __METHOD__ . " ������ ���������� ������� � ������ �������� PipeLine" );
//    }
//
//    // ��������� ������, ��� �� ��������� �������������� ������� ������������
//    $NextHandler = clone $this;
//    // �� ������� ����������� ������ ���������� 
//    $MiddleWare = $NextHandler->pipeline->dequeue();
//    // �������� ��������� (����� process) ��� ������������ $MiddleWare
//    return $MiddleWare->process($Request, $NextHandler);
}

/**
 * �������� ������� ������������, ����������� � ������� PipeLine,
 * ��� ���� ������� �� ������������, ��� �������� �� ���������� ������ TRMNext ��� �����������!!!
 * 
 * @param Request $Request
 * @param \TRMEngine\TRMPipeLine\RequestHandlerInterface $LastAction
 * @return Response
 */
public function process( Request $Request, RequestHandlerInterface $LastAction )
{
    // ��� �������� �������, ���������� ���� �������, ������� ����� ������������ ������ ������� TRMNext
    $Next = new TRMNext( clone $this->PipeLine, $LastAction );
    return $Next->handle( $Request );
}


} // TRMPipeLine
