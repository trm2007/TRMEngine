<?php

namespace TRMEngine\TRMPipeLine;

use Symfony\Component\HttpFoundation\Request;

/**
 * Handles a server request and produces a response.
 *
 * An HTTP request handler process an HTTP request in order to produce an
 * HTTP response.
 */
interface RequestHandlerInterface
{
    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     * 
     * @param Request $Request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle( Request $Request ); //: ResponseInterface;
}

/**
 * Participant in processing a server request and response.
 *
 * An HTTP middleware component participates in processing an HTTP message:
 * by acting on the request, generating the response, or forwarding the
 * request to a subsequent middleware and possibly acting on its response.
 */
interface MiddlewareInterface
{
    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     * 
     * @param Request $Request
     * @param \TRMEngine\TRMPipeLine\RequestHandlerInterface $Handler
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function process( Request $Request, RequestHandlerInterface $Handler ); //: ResponseInterface;
}


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
 * @return \Symfony\Component\HttpFoundation\Response
 * @throws Exception - ���� ���������� ������� ���� ���������� ��� ������ �������, �� ������������� ����������,
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
//        throw new Exception( __METHOD__ . " ������ ���������� ������� � ������ �������� PipeLine" );
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
 * @return \Symfony\Component\HttpFoundation\Response
 */
public function process( Request $Request, RequestHandlerInterface $LastAction )
{
    // ��� �������� �������, ���������� ���� �������, ������� ����� ������������ ������ ������� TRMNext
    $Next = new TRMNext( clone $this->PipeLine, $LastAction );
    return $Next->handle( $Request );
}


} // TRMPipeLine
