<?php

namespace TRMEngine\PipeLine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TRMEngine\PipeLine\Interfaces\MiddlewareInterface;
use TRMEngine\PipeLine\Interfaces\RequestHandlerInterface;

/**
 * ������� ����������, ������� ����� ����������, 
 * ������ ���� � ������� Request ���� ���������� (��� ��������� ���������, � ����������� �� ������ ���������)
 * � �������� ���������  
 * 
 * @author TRM
 */
class TRMPathMiddlewareDecorator implements MiddlewareInterface
{
/**
 * ��������� ������ ���������� ���� � ��������
 * ��������, ���� URI = /ajax-list �� ��������� � ��������� /ajax
 */
const COMPARE_FULL = 0;
/**
 * ��������� ��������� ���������� ���� � ��������,
 * ��������, ���� URI = /ajax-list ��������� � ��������� /ajax
 */
const COMPARE_PARTICLE = 1;

/**
 * @var MiddlewareInterface
 */
private $Middleware;
/** 
 * ������� - ����� URI ��� �������� ����� ����������� ������ Middleware
 * @var string
 */
private $Prefix;
/**
 * ����� ��������� �������� � ���� COMPARE_FULL ��� COMPARE_PARTICLE
 * @var int 
 */
private $CompareMethod = 0;

/**
 * 
 * @param string $Prefix
 * @param \TRMEngine\TRMPipeLine\MiddlewareInterface $Middleware
 */
public function __construct( $Prefix, MiddlewareInterface $Middleware, $CompareMethod = self::COMPARE_FULL )
{
    // ����������� ������� ������ ������ ���������� � /
    $this->Prefix = "/" . ltrim($Prefix, "/");
    $this->Middleware = $Middleware;
    
    if( $CompareMethod !== self::COMPARE_FULL || $CompareMethod !== self::COMPARE_PARTICLE )
    {
        $this->CompareMethod = self::COMPARE_FULL;
    }
    else
    {
        $this->CompareMethod = $CompareMethod;
    }
}

/**
 * {@inheritDoc}
 * 
 * @param Request $Request
 * @param \TRMEngine\TRMPipeLine\RequestHandlerInterface $Handler
 * @return Response
 */
public function process(Request $Request, RequestHandlerInterface $Handler )
{
    $path = $Request->getPathInfo();

    // ���� ������ ������ ���������� � /
    if( !strlen($path) ) { $path = "/"; }

    // ���� ������� ���� ������ �������, �������� �� ��������,
    // �������� ���������� ������ 
    if( strlen($path) < strlen($this->Prefix) )
    {
        return $Handler->handle($Request);
    }

    // ���� ������� ���� �� �������� �������, �������� �� ��������,
    // �������� ���������� ������ 
    if( 0 !== stripos($path, $this->Prefix) )
    {
        return $Handler->handle($Request);
    }

    // ���� ������� ���� �� ��������� ������� �� �������������� ������������, ������ ��� ������� ����������,
    // �������� ���������� ������ 
    $border = $this->getBorder($path);
    if( $border && '/' !== $border )
    {
        return $Handler->handle($Request);
    }
    return $this->Middleware->process( $Request, $Handler );

}

/**
 * ��������� ������ �� URL, ������� ����� ����� ����� �������� � $this->Prefix �����,
 * ���� ��� �� ��������� ������, �.�. ������ ������, ��� �� / ,
 * �� ���� �� ���������, ����� �������, ��� � $this->Prefix
 * 
 * @param string $path
 * @return string
 */
private function getBorder( $path )
{
    if ($this->Prefix === '/') { return '/'; }

    $length = strlen($this->Prefix);
    return strlen($path) > $length ? $path[$length] : '';
}


} // TRMPathMiddlewareDecorator
