<?php
namespace SlimPay;

class Token {
	protected $value;
	protected $expirationTime;
	
	function __construct ($value = null, $expirationTime = null) {
		$this->setValue($value);
		$this->setExpirationTime($expirationTime);
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function setValue($value) {
		$this->value = $value;
	}
	
	public function getExpirationTime() {
		return $this->expirationTime;
	}
	
	public function setExpirationTime($expirationTime) {
		$this->expirationTime = $expirationTime;
	}
	
	public function getTime() {
		return time();
	}
	
	public function isValid() {
		return !empty($this->value) && $this->expirationTime > $this->getTime();
	}
}

?>