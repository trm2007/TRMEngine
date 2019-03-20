<?php

namespace TRMEngine\PipeLine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TRMEngine\PipeLine\Interfaces\RequestHandlerInterface;

/**
 * ����� ��� ��������� ����� ������� ����������� �� �������
 * 
 * @author TRM
 * @version 2018-12-22
 */
final class TRMNext implements RequestHandlerInterface
{
/**
 * ��������� Action-Middleware,
 * ������� ����� ������, ���� ������� ��� �����, ����� ���������� ��������, � ������������ �������� 404
 * @var RequestHandlerInterface
 */
private $LastAction;
/**
 * ������� �����������
 * @var SplQueue
 */
private $Queue;

/**
 * @param SplQueue $Queue - ������� � ��������� Middleware, ������ ������������, ������� ���� ����� ���������� ���� 
 * @param \TRMEngine\TRMPipeLine\RequestHandlerInterface $LastAction - ��������� Middleware, ������� ����� ������ ���� ������� �����
 */
public function __construct( SplQueue $Queue, RequestHandlerInterface $LastAction )
{
    $this->Queue = $Queue;
    $this->LastAction = $LastAction;
}

/**
 * ��������� ��������, ������� ��� �������� Middleware �� ������� $this->Queue,
 * � �������� LastAction ������� $MiddleWare ������������� ���������� ���������� ��������� ������� $this,
 * ������ ���� ������ ��������� ���������� �����
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