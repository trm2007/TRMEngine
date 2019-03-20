<?php

/**
 * � ���� ���� ���������� ����������� ���������� PSR-15,
 * �� ��� ����������� PSR ����� Composer ����� PHP > 7.0,
 * ������� ���������� ��������� ���������� ������� ��� �������������
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
    public function handle( Request $Request ); //: ResponseInterface;
}

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
    public function process( Request $Request, RequestHandlerInterface $Handler ); //: ResponseInterface;
}