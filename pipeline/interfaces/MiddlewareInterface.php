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
 * Participant in processing a server request and response.
 *
 * An HTTP middleware component participates in processing an HTTP message:
 * by acting on the request, generating the response, or forwarding the
 * request to a subsequent middleware and possibly acting on its response.
 */
interface MiddlewareInterface
{
    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     * 
     * @param Request $Request
     * @param RequestHandlerInterface $Handler
     * @return Response
     */
    public function process(Request $Request, RequestHandlerInterface $Handler); //: ResponseInterface;
}
