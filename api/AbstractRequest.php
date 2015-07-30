<?php
namespace SlimPay;

abstract class AbstractRequest {
	protected $method;
	protected $data;
	protected $format;
	protected $headers;
	
	/**
	 * @param	$method			GET, POST, PUT or DELETE
	 * @param	$data			The data to send with the request
	 * @param	$format			The format of the data: urlencoded or json
	 *							A GET request will automatically
	 *							set the format to urlencoded.
	 * @param	$headers		Additional headers
	 */
	public function __construct($method = 'GET', $data = [], $format = 'json', $headers = []) {
		$this->method = $method;
		$this->data = $data;
		$this->format = $format;
		$this->headers = $headers;
	}
	
	public function getMethod() {
		return $this->method;
	}
	
	public function setMethod($method) {
		$this->method = $method;
	}
	
	public function getData() {
		return $this->data;
	}
	
	public function setData($data) {
		$this->data = $data;
	}
	
	public function getFormat() {
		return $this->format;
	}
	
	public function setFormat($format) {
		$this->format = $format;
	}
	
	public function getHeaders() {
		return $this->headers;
	}
	
	public function setHeaders($headers) {
		$this->headers = $headers;
	}
}

?>