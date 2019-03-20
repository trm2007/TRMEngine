<?php

namespace TRMEngine\Cookies;

/**
 * ����� ��� ������ � $_COOKIE
 *
 * @author TRM 2018
 */
class TRMCookie
{
/**
 * ������� �������� cookie
 * �� ��������� ������� �� ��� � ��� ����� ����� ������� - "/"
 * 
 * @param type $key
 * @param type $value
 * @param type $time = 60*60*24*365 = 31 536 000
 * @param type $catalog
 * @return type
 */
public static function set($key, $value, $time = 31536000, $catalog = "/")
{
    return setcookie($key, $value, time() + $time, $catalog) ;
}

public static function get($key)
{
    if ( isset($_COOKIE[$key]) )
    {
        return $_COOKIE[$key];
    }
    return null;
}

public static function delete($key, $catalog = "/")
{
    if ( isset($_COOKIE[$key]) )
    {
        setcookie($key, false, time()-3600, $catalog);
        unset($_COOKIE[$key]);
    }
}


} // TRMCookie
