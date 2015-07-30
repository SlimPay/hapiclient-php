<?php
namespace SlimPay;

class Request extends AbstractRequest {
	protected $url;
	
	/**
	 * @param	$url			The target relative or absolute URL
	 * @param	$method			GET, POST, PUT or DELETE
	 * @param	$data			The data to send with the request
	 * @param	$format			The format of the data: urlencoded or json
	 *							A GET request will automatically
	 *							set the format to urlencoded.
	 * @param	$headers		Additional headers
	 */
	public function __construct($url, $method = 'GET', $data = [], $format = 'json', $headers = []) {
		parent::__construct($method, $data, $format, $headers);
		$this->url = $url;
	}
	
	public function getUrl() {
		return $this->url;
	}
	
	public function setUrl($url) {
		$this->url = $url;
	}
}

?>