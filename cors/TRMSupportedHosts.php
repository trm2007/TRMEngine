<?php

namespace TRMEngine\Cors;

use Exception;
use TRMEngine\Cors\Interfaces\TRMCorsHeadersFactoryInterface;
use TRMEngine\Cors\Interfaces\TRMSupportedHostInterface;

/**
 * Реализует методы для обработки и проверки поступивщего запроса от другого источника (сервера) 
 * и сравнение этого источника с массивом хостов, на запросы которых можно отвечать (доверительные).<br>
 * Класс реализован в виде Singleton.<br>
 * <b>getInstance</b> - получение экземпляра объекта<br>
 * <b>addHost</b> - добавляет объект хоста в массив довертиельных<br>
 * <b>run</b> - запускает проверки и в случае, если текущий запрос поступает от доверительного источника, 
 * устанавливает необходимые заголовки<br>
 * 
 * @author Sergey Kolesnikov <trm@mail.ru>
 */
class TRMSupportedHosts
{
    /**
     * Флаг. устанавливаемый в true, если определен запрос методом OPTIONS,
     * т.е. preflight-запрос от браузера
     *
     * @var boolean
     */
    protected $PreflightFlag = false;
    /**
     * массив поддерживаемых хостов
     * 
     * @var array массив объектов TRMSupportedHost
     */
    protected $SupportedHosts = [];
    /**
     * Объект хоста - TRMSupportedHost - который 
     * для текущего запроса удалось найти в массиве доверительных хостов
     *
     * @var TRMSupportedHost
     */
    protected $GoodHost = null;
    /**
     * Объект формирования заголовков для ответа на CORS-запросы
     *
     * @var TRMCorsHeadersFactoryInterface
     */
    protected $CorsHeadersFactory = null;
    /**
     * экземпляр данного класса
     *
     * @var TRMSupportedHosts
     */
    static protected $Instance = null;
    /**
     * Флаг указывающий, нужно ли проверять соответсвие хоста по значению REMOTE_ADDR
     *
     * @var boolean
     */
    protected $CheckAddressFlag = false;
    /**
     * не реализован
     *
     * @var boolean
     */
    protected $CheckMethodFlag = false;
    /**
     * не реализован
     *
     * @var boolean
     */
    protected $CheckHeaderFlag = false;

    /**
     * При создании объекта используется инъекция зависимости (dependency injection) через конструктор,
     * конструктор определен как private, поэтому объекты данного класса создаются через функцию getInstance,
     * в которой реализован паттерн singleton
     *
     * @param TRMCorsHeadersFactoryInterface $CorsHeadersFactory
     */
    private function __construct(TRMCorsHeadersFactoryInterface $CorsHeadersFactory)
    {
        $this->CorsHeadersFactory = $CorsHeadersFactory;
    }

    /**
     * Устанавливает флаг указывающий, нужно ли проверять соответсвие хоста по значению REMOTE_ADDR,
     *
     * @param boolean $CheckAddressFlag значение. в которое будет установлен флаг, 
     * по умолчнию устанавливает в значение true,
     * если нужно отключить, то в аргумент нужно явно передать false
     * @return void
     */
    public function setCheckAddressFlag($CheckAddressFlag = true)
    {
        $this->CheckAddressFlag = $CheckAddressFlag;
    }

    /**
     * Возвращает флаг. указывающий является ли текущий запрос от клиента проверкой от браузера,
     * т.е. запрос с методом OPTIONS
     *
     * @return boolean
     */
    public function isPreflight()
    {
        return $this->PreflightFlag;
    }

    /**
     * добавляет хост в массив поддерживаемых
     *
     * @param TRMSupportedHostInterface $Host
     * @return void
     */
    public function addHost(TRMSupportedHostInterface $Host)
    {
        array_push($this->SupportedHosts, $Host);
    }

    /**
     * Возвращает массив объектов поддерживаемых хостов типа TRMSupportedHost
     *
     * @return array
     */
    public function getSupportedHosts()
    {
        return $this->SupportedHosts;
    }

    /**
     * проверяет содержится ли Origin или Ip-адрес клиента в массиве допустимых хостов,
     * если находит соответсвие, то возвращает объект с адресом HostName
     *
     * @return TRMSupportedHostInterface|null
     */
    public function findHost()
    {
        foreach ($this->SupportedHosts as $Host) {
            $GoodHost = null;
            // сначала проверяем, найдено ли соответсвие очередного хоста со значение из http-заголовка Origin...
            if ($Host->checkOrigin()) {
                // если найдено соответствие, 
                // то создаем клон хоста, что бы не менялись значения исходного объекта,
                $GoodHost = clone $Host;
                // ВАЖНО! Значение Origin в заголовке ответа и запроса должны совпадать,
                // например, если не будет указан протокол (http или https), то это будут разные источники,
                // поэтому в новом объекте устанавливаем Origin точно как в текущем запросе
                $GoodHost->setOrigin(TRMCorsHelpers::getCurrentOrigin());
                // }
                // // если соответсвие не найдено, тогда проверяем нужно ли сравнивать REMOT_ARRD (флаг CheckAddressFlag),
                // // если флаг установлен, то проверяем соответсвие хоста значению в http-заголовке REMOT_ARRD,
                // else if ($this->CheckAddressFlag && $Host->checkAddress()) {
                //     // повторояем операции как для Origin
                //     $GoodHost = clone $Host;
                //     $GoodHost->setOrigin(TRMCorsHelpers::getCurrentRemoteAddr());
                // }
                // // если соответствие адреса найдено
                // if ($GoodHost) {
                // возвращаем клонированный объект
                return $GoodHost;
            }
        }
        return null;
    }

    /**
     * Проверяет является ли запрос кросс-платформенным (CORS),
     * если является, то поступил он доверенного источника или нет,
     * если нет, то вернет false, если от доверенного, то установит нужные для ответа заголовки.
     * Так же проверяет, если запрос preflight (с методом OPTIONS),
     * то устанавливает флаг, который можно проверить функцией isPreflight
     *
     * @return boolean
     */
    public function check()
    {
        if (TRMCorsHelpers::detectCORS()) {
            // пытаемся найти соответсвие адреса запроса одному из доверительных хостов
            $this->GoodHost = $this->findHost();

            // если запрос поступил от хоста (или адреса) которого нет в массиве доверительных,
            // то завершаем работу, возвращая false
            if (empty($this->GoodHost)) {
                return false;
            }

            // далее выполняется код, если проверка хоста прошла успешно,
            // т.е. запросы от этого хоста разрешены на нашем сервере
            // header("TRMCors: verified at " . date("Y-m-d H:i:s"));

            $this->CorsHeadersFactory->setHost($this->GoodHost);

            // если это запрос проверки от браузера, то устанавливаем все заголовки
            // в тос числе с указанием доступных методов и заголовков, а так же флаг
            if (TRMCorsHelpers::detectPreflight()) {
                $this->PreflightFlag = true;
                // устанавливает нужные заголовки для найденного хоста
                $this->CorsHeadersFactory->setAllCorsHeaders();
            }
            // если это уже сам запрос, то устанавливаем только заголовки 
            // Access-Control-Allow-Credentials и Access-Control-Allow-Origin + Vary
            else {
                $this->CorsHeadersFactory->setAllowCredentialsHeader();
                $this->CorsHeadersFactory->setAllowOriginHeader();
            }
        }
        // при успешной обработке, т.е. если CORS-запросы от поступивщего источника разрешены,
        // то возвращаем true
        return true;
    }

    /**
     * Возвращает ранее созданный экземпляр TRMSupportedHosts,
     * если вызывается первый раз, то создает новый и возвращает его.
     * Реализует механизм Singleton
     *
     * @param TRMCorsHeadersFactoryInterface|null $CorsHeadersFactory - объект, 
     * который устанавливает нужные заголовки, в зависимости от соответсвующего хоста
     * @return TRMSupportedHosts
     */
    static public function getInstance(TRMCorsHeadersFactoryInterface $CorsHeadersFactory = null)
    {
        if (self::$Instance == null) {
            if (!$CorsHeadersFactory) {
                $CorsHeadersFactory = new TRMCorsHeadersFactory();
            }
            self::$Instance = new self($CorsHeadersFactory);
        }
        return self::$Instance;
    }
}
