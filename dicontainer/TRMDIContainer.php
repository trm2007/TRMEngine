<?php

namespace TRMEngine\DiContainer;

use TRMEngine\DiContainer\Exceptions\TRMDiCanNotCreateObjectException;
use TRMEngine\DiContainer\Exceptions\TRMDiClassNotFoundedException;
use TRMEngine\DiContainer\Exceptions\TRMDiNoDefaultArgsException;
use TRMEngine\DiContainer\Exceptions\TRMDiNotPublicConstructorException;

/**
 * Depended injection container
 * ���������� ��������� �������� �������� ���� Singletone
 * � ��� ��������� ������������,
 * ��������� ��� singletone
 */
class TRMDIContainer
{
/**
 * @var TRMDIContainer - ������������ ��������� ������� ���� TRMDIContainer
 */
protected static $instance;
/**
 * ��������� �����������, 
 * ����� ������ ���� ������� ������ ����� new
 */
protected function __construct(){}

/**
 * ���������� ������ ������, ������������ �� TRMSingletone,
 * � ����������� ������ ���� ��������� ����������� ���������� �������� - $instance
 * 
 * @return Object
 */
public static function getInstance()
{
    if(!isset(static::$instance)) { static::$instance = new static; }
    return static::$instance;
}


private static $FuncName = array( "__construct", "getInstance" );
    /**
 * @var array - ������ ��� ����������� �������� - ���������� singletone
 */
private static $Container = array();
/**
 * @var array - ������ � ����������� ������������ ��� ����������� �������� 
 */
private static $Params = array();

/**
 * ���������, ���� �� ������ ���� $classname � ���������� ����������� ��������
 * 
 * @param string $classname
 */
public static function has( $classname )
{
    return array_key_exists( $classname, self::$Container );
}

/**
 * ������� ����������� ������, ���������� singletone, 
 * ���� �� ��� ��� ����� ��������, �� ������������ �� ����������
 * 
 * @param string $classname - ��� ������ ������������ �������
 * @param array $params - ��������� ������������
 * 
 * @return Object - ��������� ������
 */
public static function get( $classname, array $params = array() )
{
    // ���� ������ ������� ������ ������������ ��� � ����������, ���������� ���
    if(isset(static::$Container[$classname]))
    {
        return static::$Container[$classname];
    }
    // ����� ����� ����� � �������� ��������� ������ � ���������
    return static::$Container[$classname] = static::getNew($classname, $params);
}

/**
 * ������������� �������� ���������� � �������� ����� ������ ������, 
 * ���� ��� �� ����� ������� ������ ���������� � ������ get
 * 
 * @param string $classname - ��� ������ �������, ��� �������� ��������������� ���������
 * @param array $params - ��������� ��� ������������ ��� �������� ������ �������
 */
public static function set( $classname, array $params = array() )
{
    static::$Params[$classname] = $params;
}

/**
 * ������� ����� ������ ������ $classname, �� ������� ��� � ���������, 
 * ��� ������ ������, ������ ������ ����� ������ ���� $classname
 * 
 * @param string $classname - ��� ������ ������������ �������
 * @param array $params - ��������� ������������
 * 
 * @return \TRMEngine\DiContainer\classname
 * 
 * @throws TRMDiClassNotFoundedException
 * @throws TRMDiNotPublicConstructorException
 * @throws TRMDiCanNotCreateObjectException
 */
public static function getNew( $classname, $params = array() )
{
    // ��������� ������� ������ $classname � ����� ����������,
    // ���� ��� ���, �� ����������� ����������
    if(!class_exists($classname))
    {
        throw new TRMDiClassNotFoundedException( $classname );
    }

    $NewObj = null;
    $nonpublicconstruct = false;
    // ����� ������������, 
    // ������ ��������� ������� ������������, 
    // ���� ������ �������, ������� ����� ������� ������������� ������
    // ������ ���� ������� �������� � ����������� ������� static::$FuncName,
    // ��������, "__construct", "getInstance"
    foreach( static::$FuncName as $funcname )
    {
        if(method_exists($classname, $funcname ) ) // "__construct"))
        {
            // �������� ������ ����������� ����� $classname
            $RefObj = new ReflectionClass($classname);
            // ���������, ���� ����� $funcname � �������� ���� $classname �� ���������, 
            // �� ������� ��� �� ���������, ����� ���������� ���� ������
            if( !($RefObj->getMethod($funcname)->isPublic()) )
            {
                // ���� ����������� �� ���������, �� ������ �������������� ����
                if( $funcname == "__construct" ) { $nonpublicconstruct = true; }
                continue;
            }

            // ���� �� �������� ���������-��������� ������������, � �������� ����� ������� ������, 
            // �� ��������� ��������� � ���������� (�������� � ������������ �������� ������� set)
            if( empty($params) )
            {
                // ���� ������� ��������� ��� $classname, �� ����� ��
                if( isset(static::$Params[$classname]) ){ $params = static::$Params[$classname]; }
                // ����� �������� ������� ����� � ����������� �����������, 
                // �������� ��������� � ���������� ���������� ������������ ������� ������� ����������
                else { $params = static::getMethodParams($classname, $funcname); }// �������� ������ �������� ��� ���������� ������ "__construct"
            }

            // ������� � ���������� ������ ���� $classname � ����������� $params
            if( !$nonpublicconstruct ) { $NewObj = $RefObj->newInstanceArgs($params); }
            // ���� ����������� ����������, ������ �������, ������� ������ �����
            else { $NewObj = call_user_func_array( array($classname, $funcname), $params ); }

            // ���� ������ ������ ��� ��������� $classname, ��������� ����
            if( $NewObj instanceof $classname ) { break; }
        }
    }
    // ���� ������ ��� �� ������
    if(!$NewObj)
    {
        if( $nonpublicconstruct )
        {
            throw new TRMDiNotPublicConstructorException( $classname );
        }
        // ���� ��� ������������, � ������� ��� ��������� ���������� �������, 
        // �� ������ ������� ������ ���������� new ��� ����������
        if( !($NewObj = new $classname) )
        {
            throw new TRMDiCanNotCreateObjectException( $classname );
        }
    }
    // ���� ����������� ���������, 
    // �� �������� �� ���������� ������� ������������� �������-�������,
    // ���� ��� ���� � ���������� �������
    if( isset(static::$Params[$classname]) )
    {
        static::tryToSetParams($NewObj, static::$Params[$classname]);
    }

    return $NewObj;
}

/**
 * ������� ����������� ������, ���������� singletone, 
 * ���� �� ��� ��� ����� ��������, �� ������������ �� ����������
 * 
 * @param string $classname - ��� ������ ������������ �������
 * @param array $params - ��������� ������������
 * 
 * @return Object - ��������� ������
 */
public static function getStatic( $classname, array $params = array() )
{
    return static::get($classname, $params);
}

/**
 * ������� ������ � ����������� �����������, 
 * ���� ������ � ���������� ���������, ���� ����� ��������� � ������ $methodname
 * � �� ������� �������
 * 
 * @param string $classname
 * @param string $methodname
 * 
 * @return array - ������ � ������������ ��� ������ $methodname �����������
 * 
 * @throws TRMDiNoDefaultArgsException - ���� � ���������� ����������� �������� ��� �������� �����������, �� ������������� ����������
 */
private static function getMethodParams( $classname, $methodname )
{
    $reflect = new ReflectionMethod($classname, $methodname);
    $params = $reflect->getParameters();

    $args = array();
    // ���������� � ����� �� ��� ���������� ����������� ��� �������� ������� �������������� ������
    foreach ($params as $key => $param)
    {
        // ���� � ���������� ��������� ���� �������� �� ���������, ������� ���������� � �����������, �� �������� ���
        if($param->isDefaultValueAvailable())
        {
            $args[$param->name] = $param->getDefaultValue();
        }
        else
        {
            $tmpclass = $param->getClass();
            // ���� ��� ������, �� ��� ������ ����� �������� (string, int , boolean, ��� ���-�� �������), � ��� ��� ����� ����������� �������� ��������
            if($tmpclass === NULL)
            {
                throw new TRMDiNoDefaultArgsException(" [{$param->name}] � ������ [{$methodname}] ������� [{$classname}] ");
            }
            //���� ������ ����� ���������� ��������� � ������������, �� ������� ���� �����, 
            //������� ��� ���� ��� ����� get(...)
            $args[$param->name] = static::get($tmpclass->name);
        }
    }

    return $args;
}

/**
 * �������� ����� ������� ��� ���������� ������� $object � ������� $params 
 * � ������� �� ��������� �������� �� �������
 * ��� ������� ������ �� ������� setXXXXXX -  "set" . $paramname
 * 
 * @param object $object - ������, ��� �������� ������������ ������� ������� �������
 * @param array $params - ������ ���������� ��� ������ - array( $paramname => $paramvalue, ... )
 */
private static function tryToSetParams( $object, array $params )
{
    foreach( $params as $paramname => $paramvalue )
    {
        $methodname = "set" . $paramname;
        if( method_exists($object, $methodname) )
        {
            $object->$methodname($paramvalue);
        }
    }
}

} // TRMDIContainer
