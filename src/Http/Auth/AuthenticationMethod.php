<?php
namespace HapiClient\Http\Auth;

use HapiClient\Http;

use GuzzleHttp\Message\RequestInterface;

interface AuthenticationMethod {
	/**
	 * This is called right before sending the HTTP request.
	 * @param $hapiClient	The client used to send the request.
	 * @param $httpRequest	The HTTP request before it is sent.
	 * @throws HttpException
	 */
	public function authorizeRequest(Http\HapiClient $hapiClient, RequestInterface &$httpRequest);
}
