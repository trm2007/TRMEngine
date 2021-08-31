<?php

/**
 * в этом фале содержатся стандартные интерфейсы PSR-15,
 * но для подключения PSR через Composer нужен PHP > 7.0,
 * поэтому приходится создавать интерфейсы вручную для совместимости с PHP 5.6
 */

namespace TRMEngine\PipeLine\Interfaces;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles a server request and produces a response.
 *
 * An HTTP request handler process an HTTP request in order to produce an
 * HTTP response.
 */
interface RequestHandlerInterface
{
    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     * 
     * @param Request $Request
     * @return Response
     */
    public function handle(Request $Request); //: ResponseInterface;
}
