<?php

namespace TRMEngine\Cors;

/**
 * Класс-фасад, содержит вспомогательные функции:<br>
 * <b>detectCORS</b> - определяет поступил ли запрос от другого источника (сервера) отличного от нашего
 * <b>detectPreflight</b> - определяет, является ли данный запрос проверочным от браузера, т.е. методом OPTIONS
 * 
 * @author Sergey Kolesnikov <trm@mail.ru>
 */
class TRMCorsHelpers
{
    /**
     * возвращает значение текущего $_SERVER["HTTP_ORIGIN"] 
     * для безопасности пропуская значение через фильтр FILTER_SANITIZE_URL
     *
     * @return string
     */
    public static function getCurrentOrigin()
    {
        $Origin = $_SERVER["HTTP_ORIGIN"]; // filter_input(INPUT_SERVER, "HTTP_ORIGIN", FILTER_SANITIZE_URL);
        header("TRMOrigin: " . $Origin);
        return $Origin;
    }

    /**
     * возвращает значение текущего $_SERVER["REMOTE_ADDR"] 
     * для безопасности пропуская значение через фильтр FILTER_SANITIZE_URL
     *
     * @return string
     */
    public static function getCurrentRemoteAddr()
    {
        return filter_input(INPUT_SERVER, "REMOTE_ADDR", FILTER_SANITIZE_URL);
    }

    /**
     * Определяет производится ли запрос с другого сервера (CORS запрос) или нет
     * в данной версии проверяет если есть HTTP_ORIGIN, 
     * то скорее всего запрос из другого источника
     *
     * @return bool
     */
    static public function detectCORS()
    {
        return key_exists("HTTP_ORIGIN", $_SERVER);
    }

    /**
     * проверяем preflight CORS-запрос,  
     * если это он, то просто завершаем работу, 
     * такие запросы всегда отправляются с методом OPTIONS
     *
     * @return bool
     */
    static public function detectPreflight()
    {
        return (key_exists("REQUEST_METHOD", $_SERVER) && strtoupper($_SERVER["REQUEST_METHOD"]) == "OPTIONS");
    }
}
