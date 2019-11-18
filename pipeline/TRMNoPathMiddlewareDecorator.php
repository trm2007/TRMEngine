<?php

namespace TRMEngine\PipeLine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TRMEngine\Helpers\TRMLib;
use TRMEngine\PipeLine\Interfaces\RequestHandlerInterface;

/**
 * создает посредника, который будет выполнятся, 
 * только если в объекте Request путь НЕ начинается (или полностью НЕ совпадает, в зависимости от метода сравнения)
 * с заданным префиксом
 */
class TRMNoPathMiddlewareDecorator extends TRMPathMiddlewareDecorator
{

/**
 * {@inheritDoc}
 * 
 * @param Request $Request
 * @param RequestHandlerInterface $Handler
 * @return Response
 */
public function process(Request $Request, RequestHandlerInterface $Handler )
{
    // путь всегда должен начинаться со слеша  /
    $path = "/" . trim($Request->getPathInfo(), "/");

    // цикл для проверки
    foreach( $this->Prefixes as $Prefix )
    {
        // если нашлось совпадение, 
        // т.е. путь совпадает с адресом, 
        // для которого не должен работать этот Middleware
        if( $this->match( $path, $Prefix[0], $Prefix[1] ) )
        {
            // передаем выполнение дальше
            return $Handler->handle($Request);
        }
    }
    
    // иначе НЕ нашлось совпадения, 
    // тогда передаем выполнение предназначенному для этого посреднику
    return $this->Middleware->process( $Request, $Handler );
}

/**
 * проверяет, соответсвует ли путь шаблону,
 * на основании установленного метода сравниения $this->CompareMethod
 * (полное совпадение или допускается частичное - только начало)
 * 
 * @param string $path
 * @param string $Prefix
 * @return bool
 */
//protected function match($path, $Prefix, $CompareMethod)
//{
//TRMLib::sp("path = " . $path . " , Prefix = " . $Prefix . " , CompareMethod = " . $CompareMethod);
//TRMLib::ip(parent::match($path, $Prefix, $CompareMethod));
//    return !parent::match($path, $Prefix, $CompareMethod);
//}


} // TRMNoPathMiddlewareDecorator
