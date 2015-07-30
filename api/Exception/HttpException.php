<?php
namespace SlimPay\Exception;

class HttpException extends \Exception {
	private $response;
	private $statusCode;
	private $reasonPhrase;

	public function __construct(\GuzzleHttp\Message\Response $response, $statusCode, $reasonPhrase) {
		parent::__construct("$statusCode $reasonPhrase");
		$this->response = $response;
		$this->statusCode = $statusCode;
		$this->reasonPhrase = $reasonPhrase;
	}

	public function getResponse() {
		return $this->response;
	}

	public function setResponse($response) {
		$this->response = $response;
	}

	public function getStatusCode() {
		return $this->statusCode;
	}

	public function setStatusCode($statusCode) {
		$this->statusCode = $statusCode;
	}

	public function getReasonPhrase() {
		return $this->reasonPhrase;
	}

	public function setReasonPhrase($reasonPhrase) {
		$this->reasonPhrase = $reasonPhrase;
	}
}
