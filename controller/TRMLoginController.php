<?php

namespace TRMEngine\Controller;

use Symfony\Component\HttpFoundation\Request;
use TRMEngine\Cookies\TRMAuthCookie;

/**
 * ����� ����������� ��� ����� � ����������� � �������������� Cookie,
 * �� ������� ������������ ������ ���� ����������� ������ �������� ���� - ������������<=>������,
 * � �����-�������� �� ������, ���� ����� ��������� ����� ����� (�� �����������)
 * 
 * @version 2019-03-21
 */
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
 *
 * @var string - �����, �� ������� ����� ������������� ������������, 
 * ��� �����������, ���� �� ����� OriginatingUri
 */
protected $DefaultUri;


/**
 * @param Request $Request - ������ �������
 * @param string $AuthCookieName - ��� Cookie-���� ��� �����������
 * @param string $UnAuthURL - ����, �� �������� ���������������� �� �������������� �������������
 * @param string $OriginatingUriArgumentName - ��� ��������� GET-�������, � ������� ����������� �������� URI
 * @param string $DefaultUri - �����, �� ������� ����� ������������� ������������, ���� �� ����� OriginatingUri
 */
function __construct(
        Request $Request,
        $AuthCookieName = "", 
        $UnAuthURL = "/login", 
        $OriginatingUriArgumentName = "originating_uri",
        $DefaultUri = "/" )
{
    parent::__construct($Request);
    $this->AuthCookieName = $AuthCookieName;
    $this->UnAuthURL = $UnAuthURL;
    $this->OriginatingUriArgumentName = $OriginatingUriArgumentName;
    $this->DefaultUri = $DefaultUri;
}

/**
 * ���� - �������� ������������ � ������ � �������� Cookie � ������������
 */
public function actionLogin()
{
    $this->setHeaders();

    $cookie = new TRMAuthCookie( $this->AuthCookieName );
    
    $name = $cookie->getUser();
    
    if( empty($name) )
    {
        $name = $this->Request->request->get("name");

        $password = $this->Request->request->get("password");
        // �������� ������������ � ������
        // ��� ������� checkPassword ������� � ���� ����� ������������ � ����� �������
        if( !$this->checkPassword($name, $password) )
        {
            $this->renderLoginView();
            return true;
        }
        // ���� ���� ��� �����������, ������ ��������������

        // ��������� cookie � ������� �������������
        $cookie->setauth($name);
    }

    // ��� ����� originating_uri ���������� ����� GET-������,
    // ���� ������ ��������� ���, �� ���������� ������������� �� DefaultUri
    $uri = $this->Request->request->get( $this->OriginatingUriArgumentName, $this->DefaultUri );
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

/**
 * ��������� HTTP-��������� ��� ������� ����
 */
protected function setHeaders()
{
    header("Cache-Control: no-cache, no-store");
    header("Pragma: no-cache");
    header("Expires: Tue, 22 Jan 2000 21:45:35 GMT");
}

/**
 * ���������������� ����������,
 * � ��� ������ ��������������� ��������, ����� ������������ �� ����������� �,
 * ��������, ������ ������������ ��� � ������ �����
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
abstract protected function checkPassword($name, $password);


} // TRMLoginController