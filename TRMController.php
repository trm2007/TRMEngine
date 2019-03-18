<?php
namespace TRMEngine;

use Symfony\Component\HttpFoundation\Request;

use TRMEngine\Exceptions\NoControllerException;
use TRMEngine\Exceptions\NoActionException;
/**
 * ������� ����� ��� ���� ������������
 */
abstract class TRMController
{
const DefaultPageName = "page";
/**
 * @var array ���� ����������� ���� ������ ������ (controller - actinon - param - ...) �� PathFinder-a
 */
protected $path;
/**
 * @var Request - ������ ������� �� �������
 */
protected $Request;
/**
 * @var TRMView - ������ ���� ��� �������� ����������� ������, 
 * �� ��������� ���������� ��������� ������ �� ������ URL - ��� ���� ��������,
 * � ��� ������ ���� ����, ����� ���� ������ �� ������ ������
 */
protected $view;
/**
 * @var integer - ����� ��������, ���� ���� ���������
 */
protected $page = 1;
/**
 * @var string - ��� �������� Action
 */
protected $CurrentAction = '';
/**
 * @var string - ��� �������� Controller
 */
protected $CurrentControllerName = '';

protected $ConfigArray; //������ �� ������ � ����������������� �������

public function __construct(Request $Request)
{
    $this->Request = $Request;
    // ��� �������� �������� ��� Controller � ��������� ����������
    $this->CurrentControllerName = $this->Request->attributes->get("controller");
    if( empty($this->CurrentControllerName) )
    {
        throw new NoControllerException( __METHOD__ . " �� ��������� ������������������ Controller", 404);
    }
    // ��� �������� �������� ��� Action � ��������� ����������
    $this->CurrentActionName = $this->Request->attributes->get("action");
    if( !isset($this->CurrentActionName) )
    {
        throw new NoActionException( __METHOD__ . " �� ������ Action", 404);
    }

    // � ��� �� ��� �������� �������� ����� �������� (��� ���������) � ��������� ����������
    $this->page = $this->Request->query->getInt(defined ("PAGE_NUMERIC_NAME") ? PAGE_NUMERIC_NAME : self::DefaultPageName, 1);
}


} // TRMController