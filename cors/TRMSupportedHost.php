<?php

namespace TRMEngine\Cors;

use TRMEngine\Cors\Interfaces\TRMSupportedHostInterface;

/**
 * Простая обертка для сохранения и обработки параметров хоста, используемого при CORS-запросах,
 * содержит методы:<br>
 * <b>compareWith</b> - сравнивает имя с другим хостом<br>
 * <b>setCORSHeaders</b> - устанавливает заголовки, необходимые для корректного ответа при CORS-запросе от данного хоста
 * 
 * @author Sergey Kolesnikov <trm@mail.ru>
 */
class TRMSupportedHost implements TRMSupportedHostInterface
{
    /**
     * все доступные методы для http-запроса
     */
    public const ALL_METHODS = ["GET", "PUT", "POST", "PATCH", "DELETE", "OPTIONS", "CONNECT", "TRACE", "HEAD"];
    /**
     * URL или IP-адрес хоста
     *
     * @var string
     */
    protected $Origin = "";
    /**
     * массив методов, которые этот хост может использовать при запросе
     *
     * @var array
     */
    protected $Methods = [];
    /**
     * массив заголовков, которые этот хост может использовать в запросе
     *
     * @var array
     */
    protected $Headers = [];

    /**
     * создает объект с параметрами поддерживаемого хоста
     *
     * @param string $Origin адрес или имя хоста
     * @param array $Methods методы, которые он может использовать при запросах к нашему ресурсу
     * @param array $Headers заголовки, которые может использовать хост к нашему серверу
     */
    public function __construct($Origin = "", $Methods = [], $Headers = [])
    {
        $this->Origin = $Origin;
        foreach ($Methods as $Method) {
            $this->addMethod($Method);
        }
        foreach ($Headers as $Header) {
            $this->addHeader($Header);
        }
    }

    /**
     * Устанавливает адрес источника (адрес откуда должен проиходить запрос) для объекта хоста
     *
     * @param string $Origin
     * @return void
     */
    public function setOrigin($Origin)
    {
        $this->Origin = $Origin;
    }

    /**
     * Название или адрес (URL или IP) этого хоста (источника)
     *
     * @return string
     */
    public function getOrigin()
    {
        return $this->Origin;
    }

    /**
     * добавляет метод, который доступен этому хосту для запросов
     *
     * @param string $Method
     * @return void
     */
    public function addMethod($Method)
    {
        // методы преобразуются в верхний регистр
        array_push($this->Methods, strtoupper($Method));
    }
    /**
     * добавляет заголовок, который доступен этому хосту для запросов
     *
     * @param string $Header
     * @return void
     */
    public function addHeader($Header)
    {
        // заголовки по определению регистронезависимые, все названия преобразуются в нижний регистр
        array_push($this->Headers, strtolower($Header));
    }

    /**
     * Возвращает массив методов (названий), которые обрабатываются при запросе от этого источника
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->Methods;
    }

    /**
     * Возвращает массив заголовков (названий), которые обрабатываются при запросе от этого источника
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->Headers;
    }

    /**
     * Проверяет (сравнивает) $HostName с текущим $this->Origin
     *
     * @param string $HostName URL или IP-адрес, который будет проверяться с текущим хостом
     * @return bool
     */
    protected function compareWith($HostName)
    {
        return (strpos($HostName, $this->Origin) !== false);
    }

    /**
     * Сравнивает установленный адрес хоста с HTTP_ORIGIN из запроса, в данном случае сравнение URL
     *
     * @return bool
     */
    public function checkOrigin()
    {
        return $this->compareWith($_SERVER["HTTP_ORIGIN"]);
    }

    /**
     * Сравнивает установленный адрес хоста с REMOTE_ADDR из запроса, в данном случае сравнение IP
     *
     * @return bool
     */
    public function checkAddress()
    {
        return $this->compareWith($_SERVER["REMOTE_ADDR"]);
    }

    /**
     * Проверяет поддерживается ли запрошенный метод, т.е.
     * проверяется наличие HTTP_ACCESS_CONTROL_REQUEST_METHOD в массиве $this->Methods. 
     * Если в массиве $_SERVER не будет значения под ключом HTTP_ACCESS_CONTROL_REQUEST_METHOD,
     * то метод все-равно вернет true
     * т.е. пустое значение проходит проверку
     *
     * @return bool
     */
    public function checkMethod()
    {
        if (!array_key_exists("HTTP_ACCESS_CONTROL_REQUEST_METHOD", $_SERVER)) {
            return true;
        }
        return in_array(strtoupper($_SERVER["HTTP_ACCESS_CONTROL_REQUEST_METHOD"]), $this->Methods);
    }

    /**
     * Проверяет поддерживаются ли запрошенные заголовки, т.е.
     * проверяется наличие каждого заголовка из строки HTTP_ACCESS_CONTROL_REQUEST_HEADERS в массиве $this->Headers. 
     * Если в массиве $_SERVER не будет значения под ключом HTTP_ACCESS_CONTROL_REQUEST_HEADERS,
     * то метод все-равно вернет true,
     * т.е. пустое значение проходит проверку
     *
     * @return bool
     */
    public function checkHeaders()
    {
        if (!array_key_exists("HTTP_ACCESS_CONTROL_REQUEST_HEADERS", $_SERVER)) {
            return true;
        }

        $HeadersArray = explode(",", $_SERVER["HTTP_ACCESS_CONTROL_REQUEST_HEADERS"]);
        foreach ($HeadersArray as $Header) {
            if (!in_array($Header, $this->Headers)) {
                return false;
            }
        }
        return true;
    }
}
