<?php

namespace TRMEngine\Cors\Interfaces;

interface TRMSupportedHostInterface
{
    /**
     * Устанавливает адрес источника (адрес откуда должен проиходить запрос) для объекта хоста
     *
     * @param string $Origin
     * @return void
     */
    public function setOrigin($Origin);
    /**
     * Название или адрес (URL или IP) этого хоста (источника)
     *
     * @return string
     */
    public function getOrigin();
    /**
     * добавляет метод, который доступен этому хосту для запросов
     *
     * @param string $Method
     * @return void
     */
    public function addMethod($Method);
    /**
     * добавляет заголовок, который доступен этому хосту для запросов
     *
     * @param string $Header
     * @return void
     */
    public function addHeader($Header);
    /**
     * Возвращает массив методов (названий), которые обрабатываются при запросе от этого источника
     *
     * @return array
     */
    public function getMethods();
    /**
     * Возвращает массив заголовков (названий), которые обрабатываются при запросе от этого источника
     *
     * @return array
     */
    public function getHeaders();
    /**
     * Сравнивает установленный адрес хоста с HTTP_ORIGIN из запроса, в данном случае сравнение URL
     *
     * @return bool
     */
    public function checkOrigin();
}
