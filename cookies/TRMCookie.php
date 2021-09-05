<?php

namespace TRMEngine\Cookies;

/**
 * Класс для работы с $_COOKIE
 *
 * @author TRM 2018
 */
class TRMCookie
{
  /**
   * функция устновки cookie
   * по умолчанию создает на год и для всего сайта каталог - "/"
   * 
   * @param string $key
   * @param string $value
   * @param int $time = 60*60*24*365 = 31 536 000
   * @param string $catalog
   * @return bool
   */
  public static function set($key, $value, $time = 31536000, $catalog = "/")
  {
    return setcookie($key, $value, time() + $time, $catalog);
  }

  public static function get($key)
  {
    if (isset($_COOKIE[$key])) {
      return $_COOKIE[$key];
    }
    return null;
  }

  public static function delete($key, $catalog = "/")
  {
    if (isset($_COOKIE[$key])) {
      setcookie($key, false, time() - 3600, $catalog);
      unset($_COOKIE[$key]);
    }
  }
}
