<?php

namespace TRMEngine\Cors;

use TRMEngine\Cors\Interfaces\TRMCorsHeadersFactoryInterface;
use TRMEngine\Cors\Interfaces\TRMSupportedHostInterface;

/**
 * Объекты этого класса в конструкторе получают объект TRMSupportedHost,
 * и предоставляют методы для формирования на основании данных этого хоста 
 * заголовков для ответа на CORS-запросы
 */
class TRMCorsHeadersFactory implements TRMCorsHeadersFactoryInterface
{
    /**
     * Объект хоста, для которого будут формироваться заголовки (origin, metods, headers...)
     *
     * @var TRMSupportedHostInterface
     */
    protected $Host = null;

    /**
     * В конструктор TRMCorsHeadersFactory может быть сразу передан готовый объект хоста,
     * если инъекция зависимости не будет произведена через конструктор,
     * значит объект можно будет позже установить через setHost
     *
     * @param TRMSupportedHostInterface|null $Host
     */
    public function __construct(TRMSupportedHostInterface $Host = null)
    {
        if ($Host) {
            $this->setHost($Host);
        }
    }

    /**
     * Устанавливает объект хоста, для которого будут формироваться заголовки (origin, metods, headers...)
     *
     * @param TRMSupportedHostInterface $Host
     * @return void
     */
    public function setHost(TRMSupportedHostInterface $Host)
    {
        $this->Host = $Host;
    }

    /**
     * устанавливает заголовок Access-Control-Allow-Methods из массива $this->Host->getMethods,
     * этот массив должен быть принициализирован на момент вызова этого метода
     *
     * @return void
     */
    public function setAllowMethodsHeader()
    {
        header("Access-Control-Allow-Methods: " . implode(', ', $this->Host->getMethods()));
    }
    /**
     * устанавливает заголовок Access-Control-Allow-Headers из массива $this->Host->getHeaders,
     * этот массив должен быть принициализирован на момент вызова этого метода
     *
     * @return void
     */
    public function setAllowHeadersHeader()
    {
        header("Access-Control-Allow-Headers: " . implode(', ', $this->Host->getHeaders()));
    }
    /**
     * устанавливает заголовок Access-Control-Allow-Credentials в значение true
     *
     * @return void
     */
    public function setAllowCredentialsHeader()
    {
        header("Access-Control-Allow-Credentials: true");
    }

    /**
     * устанавливает заголовки Access-Control-Allow-Origin и Vary,
     * информация берется из $this->Host->Address, 
     * поэтому перед вызовом этой функции необходимо провести инициализацию параметров хоста
     * заголовок Vary устанавливается в Origin,
     * 
     * @return void
     */
    public function setAllowOriginHeader()
    {
        // этот заголовок должен всегда отправляться при CORS-запросах,
        // сейчас он установлен в .htaccess и здесь должен быть отключен,
        // чтобы не сзодавать множественные (multiple) значения
        // header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        // header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Origin: " . $this->Host->getOrigin());
        /**
         * Если сервер послал ответ со значением Access-Control-Allow-Origin, 
         * которое содержит явное указание источника (а не шаблонное значение "*"), 
         * тогда ответ также должен включать в себя заголовок Vary со значением Origin 
         * — чтобы указать браузеру, 
         * что ответы с сервера могут отличаться в зависимости от заголовка запроса Origin.
         */
        header("Vary: Origin");
    }

    /**
     * устанавливает все заголовки, необходимые для корректного ответа на CORS-запрос от другого источника,
     * включая:
     * Access-Control-Allow-Credentials,
     * Access-Control-Allow-Origin + Vary,
     * Access-Control-Allow-Methods,
     * Access-Control-Allow-Headers
     * вся информация берется из текущих значений объекта, 
     * поэтому перед вызовом этой функции необходимо провести инициализацию всех параметров хоста
     * 
     * @return void
     */
    public function setAllCorsHeaders()
    {
        $this->setAllowCredentialsHeader();
        $this->setAllowOriginHeader();
        $this->setAllowMethodsHeader();
        $this->setAllowHeadersHeader();
    }
}
