<?php

namespace TRMEngine\PathFinder;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use TRMEngine\PathFinder\Exceptions\TRMPathNotFoundedException;
use TRMEngine\PipeLine\Interfaces\MiddlewareInterface;
use TRMEngine\PipeLine\Interfaces\RequestHandlerInterface;


/**
 * ����� ��� ������ ����������� � Action ��� ������� ����
 * 
 * @author TRM 2018
 */
class TRMPathFinder implements MiddlewareInterface
{
/**
 * @var string - ��� ����������� �� ���������
 */
const DefaultControllerName = "Main";
/**
 * @var string - ��� action-������� �� ���������
 */
const DefaultActionName = "Index";
/**
 * @var array - ������� ���� ����������� �� $CurrentPath["controller"] = string, $CurrentPath["action"] = string, �������� $CurrentPath["param"] = array
 */
public static $CurrentPath = array();
/**
 * @var Request - ������� _GET, _POST, _SERVER � �.�.
 */
public $Request;


/**
 * 
 * @param Request $Request
 * @param RouteCollection $Routes
 */
public function __construct( RouteCollection $Routes )
{
    $this->Routes = $Routes;
}

/**
 * ���������� ������� (�����������) ������� ��� ������� URL
 * 
 * @return array
 */
static public function getCurrentPath()
{
    return self::$CurrentPath;
}

/**
 * ����������� ����� URL � CamelCase, ������� ������
 * 
 * @param string $Name - ��� ��� ��������������
 * @return string - ��������������� � ��������� �� ������������� �������� ������
 */
static protected function sanitizeName($Name)
{
    return str_replace(" ", "", ucwords( str_replace("-", " ", filter_var( $Name, FILTER_SANITIZE_URL ) ) ) );
}

/**
 * ����� ������ ����������� � ��� ������ (Action) ��� ������� ���� �� URI,
 * ��������� �������� "controller", "action" � "param" � ������� Request
 * 
 * @param Request $Request
 * @param RequestHandlerInterface $Handler
 * @return ResponseInterface
 * @throws Exceptions
 */
public function process( Request $Request, RequestHandlerInterface $Handler )
{
    $Context = new RequestContext();
    
    $Context->fromRequest($Request);
    
    $Matcher = new UrlMatcher($this->Routes, $Context);

    try
    {
        $parameters = $Matcher->match( $Context->getPathInfo() );
    }
    catch(ResourceNotFoundException $e)
    {
        //TRMLib::debugPrint( "�� ������ �������" . $e->getMessage() );
        throw new TRMPathNotFoundedException( $Request->getUri() );
    }
    
    $this->generateCurrentPathFromParameters($parameters);

    $Request->attributes->set( "controller", self::$CurrentPath['controller'] );
    $Request->attributes->set( "action", self::$CurrentPath['action'] );
    $Request->attributes->set( "param", self::$CurrentPath['param'] );
//    $this->runAction();
    return $Handler->handle($Request);
}

protected function generateCurrentPathFromParameters( array $parameters )
{
    self::$CurrentPath['controller'] = self::sanitizeName( $parameters["_controller"] );

    self::$CurrentPath['action'] = self::DefaultActionName;
    if( isset($parameters["action"]) )
    {
        self::$CurrentPath['action'] = self::sanitizeName( $parameters["action"] );
    }
    
    self::$CurrentPath['param'] = "";
    if( isset($parameters["param"]) )
    {
        self::$CurrentPath['param'] = $parameters["param"]; // ��� ������������� �� ������ �������
    }
}


}  // TRMPathFinder
