<?php

namespace TRMEngine\PipeLine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TRMEngine\PipeLine\Interfaces\MiddlewareInterface;
use TRMEngine\PipeLine\Interfaces\RequestHandlerInterface;

/**
 * создает посредника, который будет выполнятся, 
 * только если в объекте Request путь начинается (или полностью совпадает, в зависимости от метода сравнения)
 * с заданным префиксом  
 * 
 * @author TRM
 */
class TRMPathMiddlewareDecorator implements MiddlewareInterface
{
/**
 * срвнивает полное совпадение пути и префикса
 * например, путь URI = /ajax-list НЕ совпадает с префиксом /ajax
 */
const COMPARE_FULL = 0;
/**
 * срвнивает частичное совпадение пути и префикса,
 * например, путь URI = /ajax-list совпадает с префиксом /ajax
 */
const COMPARE_PARTICLE = 1;

/**
 * @var MiddlewareInterface
 */
private $Middleware;
/** 
 * Префикс - часть URI для которого будет срабатывать данный Middleware
 * @var string
 */
private $Prefix;
/**
 * метод сравнения префикса и пути COMPARE_FULL или COMPARE_PARTICLE
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
    // проверяемый префикс всегда должен начинаться с /
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

    // путь всегда должен начинаться с /
    if( !strlen($path) ) { $path = "/"; }

    // если текущий путь короче прфикса, очевидно не сопадают,
    // передаем выполнение дальше 
    if( strlen($path) < strlen($this->Prefix) )
    {
        return $Handler->handle($Request);
    }

    // если текущий путь не содержит прфикса, очевидно не сопадают,
    // передаем выполнение дальше 
    if( 0 !== stripos($path, $this->Prefix) )
    {
        return $Handler->handle($Request);
    }

    // если текущий путь по окончанию прфикса не закзанчивается разделителем, значит нет точного совпадения,
    // передаем выполнение дальше 
    $border = $this->getBorder($path);
    if( $border && '/' !== $border )
    {
        return $Handler->handle($Request);
    }
    return $this->Middleware->process( $Request, $Handler );

}

/**
 * проверяет символ из URL, который стоит сразу после заданной в $this->Prefix части,
 * если это не окончание строки, т.е. пустой символ, или не / ,
 * то путь не совпадает, слово длиннее, чем в $this->Prefix
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
