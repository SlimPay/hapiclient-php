<?php
namespace HapiClient\Http\Auth;

use HapiClient\Http;
use GuzzleHttp\Message\RequestInterface;

interface AuthenticationMethod
{
    /**
     * This is called right before sending the HTTP request.
     * @param $hapiClient	The client used to send the request.
     * @param $request  	The request before it is sent.
     * @return Request  The same Request with the authorization Headers.
     * @throws HttpException
     */
    public function authorizeRequest(Http\HapiClient $hapiClient, Http\Request $request);
}
