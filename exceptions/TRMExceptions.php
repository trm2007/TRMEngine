<?php
namespace TRMEngine\Exceptions;

class TRMException extends \Exception {}

//������������� ��� �������� �����������
class AuthException extends TRMException {}

//������������� �������������, ���� ������ �� ����� ���� ������
class ObjectCreateException extends TRMException {}

//������������� ���� �� ������ Controller
class NoControllerException extends TRMException {}
//�������������, ���� �� ������ Action
class NoActionException extends TRMException {}

/**
 * �������������, ���� ����������� �� ���� �������� ������
 */
class TRMRepositoryGetObjectException extends TRMException {}

/**
 * ������������� ��� ������ SQL-�������
 */
class TRMSqlQueryException extends TRMException {}

/**
 * �������������, ���� �� ����� �������
 */
class TRMExceptionPathNotFound extends TRMException
{
    public function __construct( $URL, $Code = 404 )
    {
        parent::__construct("��������� �� ��������� ������: {$URL}", $Code );
    }
}

/**
 * ����� ����������, ������� �������������, ���� �� ������ ���������� ��� �������� URI
 *
 * @author TRM
 */
class TRMExceptionControllerNotFound extends TRMException
{
    public function __construct( $ControllrrName, $Code = 404 )
    {
        parent::__construct("�� ������ ������ [" . $ControllrrName . "]", $Code );
    }
} // TRMExceptionControllerNotFound

/**
 * ����� ����������, ������� �������������, ���� �� ������ Action � ����������� ��� �������� URI
 *
 * @author TRM
 */
class TRMExceptionActionNotFound extends TRMException
{
    public function __construct( $ControllrrName, $ActionName, $Param, $Code = 404 )
    {
        parent::__construct( "� ������ [" . $ControllrrName . "] �� ������ ����� [" 
                            . $ActionName
                            . "]<br>   ��� ��������� [" 
                            . $Param .  "]<br>",
                $Code );
    }
} // TRMExceptionControllerNotFound


class TRMMustStartOtherAction extends TRMException
{
    /**
     * @var string - ��� �������-Action, ������� ������ ���� ��������
     */
    protected $ActionName = "";
        public function __construct( $ActionName )
    {
            parent::__construct();
            $this->ActionName = $ActionName;
    }
    
    public function getActionName()
    {
        return $this->ActionName;
    }
} // TRMMustStartOtherAction