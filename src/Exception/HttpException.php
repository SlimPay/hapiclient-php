<?php
namespace HapiClient\Exception;

use HapiClient\Resource\Resource;

use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;

class HttpException extends \Exception {
	private $request;
	private $response;

	public function __construct(RequestInterface $request, ResponseInterface $response) {
		parent::__construct(
			$response->getStatusCode() . ' ' .
			$response->getReasonPhrase()
		);
		
		$this->request = $request;
		$this->response = $response;
	}

	/**
	 * @return GuzzleHttp\Message\RequestInterface	The HTTP request causing the Exception.
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * @return GuzzleHttp\Message\ResponseInterface	The HTTP response causing the Exception.
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * The magic setter is overridden to insure immutability.
	 */
    public function __set($name, $value) { }

	/**
	 * This is basically a shortcut for for getResponse()->getStatusCode()
	 * @return	string	The HTTP status code.
	 */
	public function getStatusCode() {
		return $this->response->getStatusCode();
	}

	/**
	 * This is basically a shortcut for getResponse()->getReasonPhrase()
	 * @return	string	The HTTP reason phrase.
	 */
	public function getReasonPhrase() {
		return $this->response->getReasonPhrase();
	}

	/**
	 * This is basically a shortcut for (string) getResponse()->getBody()
	 * @return	string	The response body.
	 */
	public function getResponseBody() {
		return (string) $this->response->getBody();
	}

	/**
	 * The response message body may be a string
	 * representation of a Resource representing the error.
	 * 
	 * This is basically a shortcut for Resource::fromJson(getResponseBody()).
	 * @return	Resource	The Resource returned by the response (may be empty).
	 */
	public function getResponseResource() {
		return Resource::fromJson($this->getResponseBody());
	}
}
