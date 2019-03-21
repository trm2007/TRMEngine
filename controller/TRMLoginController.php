<?php

namespace TRMEngine\Controller;

use Symfony\Component\HttpFoundation\Request;
use TRMEngine\Cookies\TRMAuthCookie;
use TRMEngine\TRMController;

abstract class TRMLoginController extends TRMController
{
/**
 * @var string - ��� ��������� � GET-������ � �������-URI,
 * ���� ����� ��������� ������������ ����� �����������,
 * ��������������� � ������������ ������������
 */
protected $OriginatingUriArgumentName;
/**
 * @var string - ��� Cookie � ������� ����� �������� ���������� �� ����������� ������������,
 * ���� �� ����� ����, ����� ����������� ������������� �� www_��������_ru_auth
 */
protected $AuthCookieName;
/**
 * @var string - ����, �� �������� ����� ������������� �� �������������� ������������,
 * ��� ������� ��� �������� ��� ����� ������ � ������ - /login
 */
protected $UnAuthURL;


/**
 * @param Request $Request - ������ �������
 * @param string $AuthCookieName - ��� Cookie-���� ��� �����������
 * @param string $UnAuthURL - ����, �� �������� ���������������� �� �������������� �������������
 * @param string $OriginatingUriArgumentName - ��� ��������� GET-�������, � ������� ����������� �������� URI
 */
function __construct(
        Request $Request,
        $AuthCookieName = "", 
        $UnAuthURL = "/login", 
        $OriginatingUriArgumentName = "originating_uri" )
{
    parent::__construct($Request);
    $this->AuthCookieName = $AuthCookieName;
    $this->UnAuthURL = $UnAuthURL;
    $this->OriginatingUriArgumentName = $OriginatingUriArgumentName;
}

/**
 * ���� - �������� ������������ � ������ � �������� Cookie � ������������
 */
public function actionLogin()
{
    $this->setHeaders();

    $name = $this->Request->request->get("name");

    $password = $this->Request->request->get("password");
    // �������� ������������ � ������
    // ��� ������� checkPassword ������� � ���� ����� ������������ � ����� �������
    if( $this->checkPassword($name, $password) )
    {
        $this->renderLoginView();
        return true;
    }
    // ��� ����� originating_uri ���������� �� �����
    $uri = $this->Request->request->get( $this->OriginatingUriArgumentName, $this->DefaultUri );

    // ���� ���� ��� �����������, ������ ��������������, ��������� cookie � ������� �������������
    $cookie = new AuthCookie($name);

    $cookie->setauth();

    // ������ ������������ �� ������������� ��������
    header("Location: {$uri}");
    exit;
}

/**
 * ����� - �������� Cookie � ������������
 */
public function actionLogout()
{
    if( empty($this->AuthCookieName) )
    {
        $this->AuthCookieName = str_replace( ".", "_", $this->Request->server->get("REQUEST_URI") ) . "_auth";
    }
    
    $cookie = new TRMAuthCookie($this->AuthCookieName);
    $cookie->logout();
    //��������� �����������!
    header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
    $this->setHeaders();

    header("Location: {$this->UnAuthURL}");
    exit;
}

protected function setHeaders()
{
    // ������� �����������
    header("Cache-Control: no-cache, no-store");
    header("Pragma: no-cache");
    header("Expires: Tue, 22 Jan 2000 21:45:35 GMT");
}

/**
 * ���������������� ����������,
 * ������ ������������ ����� �����
 */
abstract public function renderLoginView();

/**
 * ���������������� ���������� �������,
 * ������ ��������� ������������ ������ � ������������
 * 
 * @param string $name
 * @param string $password
 * @return boolean
 */
abstract public function checkPassword($name, $password);


} // TRMLoginController