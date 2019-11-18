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
protected $Middleware;
/**
 * @var array - массив с проверямыми префиксами-шаблонами - начальными частями URI 
 */
protected $Prefixes = array();
/**
 * метод сравнения префикса и пути COMPARE_FULL или COMPARE_PARTICLE
 * @var int 
 */
protected $CompareMethod = 0;

/**
 * 
 * @param array $Prefixes - массив вида : 
 * array(
 * 0 => префикс-шаблон, он же начало пути для обработки, 
 * или весь путь, в зависимости от значения флага $CompareMethod,
 * 1 => метод сравнения, если установлен 
 * в TRMPathMiddlewareDecorator::COMPARE_FULL, то проверяется полное совпадение пути и префикса-шаблона,
 * если в TRMPathMiddlewareDecorator::COMPARE_PARTICLE, 
 * то проверяется совпадение только начала пути с префиксом-шаблоном,
 * последний режим установлен по умолчанию...
 * )
 * @param MiddlewareInterface $Middleware - посредник-обработчик, 
 * который выполняется для этого пути
 */
public function __construct( array $Prefixes, MiddlewareInterface $Middleware )
{
    foreach( $Prefixes as $Prefix )
    {
        $Arr = array();
        // проверяемый префикс всегда должен начинаться с /
        // и не должен им заканчиваться 
        $Arr[0] = "/" . trim($Prefix[0], "/");
        if( isset($Prefix[1]) && ($Prefix[1] === self::COMPARE_FULL || $Prefix[1] === self::COMPARE_PARTICLE) )
        {
            $Arr[1] = $Prefix[1];
        }
        else
        {
            $Arr[1] = self::COMPARE_PARTICLE;
        }
        $this->Prefixes[] = $Arr;
    }
    $this->Middleware = $Middleware;
}

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
        // если нашлось совпадение
        if( $this->match( $path, $Prefix[0], $Prefix[1] ) )
        {
            // то передаем выполнение предназначенному для этого посреднику
            return $this->Middleware->process( $Request, $Handler );
        }
    }
    // иначе НЕ нашлось совпадение, 
    // передаем выполнение дальше по цепочке не выполняя данный посредник
    return $Handler->handle($Request);
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
protected function match($path, $Prefix, $CompareMethod)
{
    // если установлен флаг проверки полного совпадения,
    if( $CompareMethod === self::COMPARE_FULL )
    {
        // проверяем полное совпадение пути и префикса (он же шаблон)
        if( $path === $Prefix ) { return true; }
        else { return false; }
    }
    // далее выполняется только если $CompareMethod === self::COMPARE_PARTICLE
    // если текущий путь короче префикса, очевидно сопадения нет,
    if( strlen($path) < strlen($Prefix) )
    {
        return false;
    }

    // если текущий путь не начинается с префикса, очевидно не сопадают,
    // передаем выполнение дальше 
    if( 0 !== stripos($path, $Prefix) )
    {
        return false;
    }

    // получаем символ в текущем пути по окончанию префикса 
    $border = $this->getBorder($path, $Prefix);

    // если символ в текущем пути по окончанию префикса 
    // не является пустым (т.е. это не окончание строки), 
    // и не является разделителем пути / 
    if( $border && '/' !== $border )
    {
        // значит нет совпадения,
         return false;
    }

    // иначе нашлось совпанедние маршрута с Prefix (шаблоном)
    return true;
}

/**
 * проверяет символ из URL, который стоит сразу после заданной в $Prefix части,
 * если это окончание строки 
 * (т.е. длина $path совпадает или меньше $Prefix), 
 * то вернется пустой символ
 * 
 * @param string $path
 * @param string $Prefix
 * 
 * @return char
 */
protected function getBorder( $path, $Prefix )
{
    if ($Prefix === '/') { return '/'; }

    $length = strlen($Prefix);
    return strlen($path) > $length ? $path[$length] : '';
}


} // TRMPathMiddlewareDecorator
