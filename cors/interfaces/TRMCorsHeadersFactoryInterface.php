<?php

namespace TRMEngine\Cors\Interfaces;

interface TRMCorsHeadersFactoryInterface
{
    /**
     * Устанавливает объект хоста, для которого будут формироваться заголовки (origin, metods, headers...)
     *
     * @param TRMSupportedHostInterface $Host
     * @return void
     */
    public function setHost(TRMSupportedHostInterface $Host);
    /**
     * устанавливает заголовок Access-Control-Allow-Methods из массива $this->Host->getMethods,
     * этот массив должен быть принициализирован на момент вызова этого метода
     *
     * @return void
     */
    public function setAllowMethodsHeader();
    /**
     * устанавливает заголовок Access-Control-Allow-Headers из массива $this->Host->getHeaders,
     * этот массив должен быть принициализирован на момент вызова этого метода
     *
     * @return void
     */
    public function setAllowHeadersHeader();
    /**
     * устанавливает заголовок Access-Control-Allow-Credentials в значение true
     *
     * @return void
     */
    public function setAllowCredentialsHeader();
    /**
     * устанавливает заголовки Access-Control-Allow-Origin и Vary,
     * информация берется из $this->Host->Address, 
     * поэтому перед вызовом этой функции необходимо провести инициализацию параметров хоста
     * заголовок Vary устанавливается в Origin,
     * 
     * @return void
     */
    public function setAllowOriginHeader();
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
    public function setAllCorsHeaders();
}
