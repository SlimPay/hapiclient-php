<?php
namespace SlimPay;

class Follow extends AbstractRequest {
	protected $rel;
	
	/**
	 * @param	$rel			The relation name used to identify the link.
	 * @param	$method			GET, POST, PUT or DELETE
	 * @param	$data			The data to send with the request
	 * @param	$format			The format of the data: urlencoded or json
	 *							A GET request will automatically
	 *							set the format to urlencoded.
	 * @param	$headers		Additionnal headers
	 */
	public function __construct($rel, $method = 'GET', $data = [], $format = 'json', $headers = []) {
		parent::__construct($method, $data, $format, $headers);
		$this->rel = $rel;
	}
	
	public function getRel() {
		return $this->rel;
	}
	
	public function setRel($rel) {
		$this->rel = $rel;
	}
}

?>