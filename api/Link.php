<?php
namespace SlimPay;

class Link {
	protected $rel;
	protected $attributes;
	
	function __construct ($rel = null, $attributes = null) {
		$this->setRel($rel);
		$this->setAttributes($attributes ? $attributes : []);
	}
	
	public function getRel() {
		return $this->rel;
	}
	
	public function setRel($rel) {
		$this->rel = $rel;
	}
	
	public function getAttributes() {
		return $this->attributes;
	}
	
	public function setAttributes($attributes) {
		$this->attributes = $attributes;
	}
	
	public function getAttribute($key) {
		return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
	}
	
	public function setAttribute($key, $value) {
		$this->attributes[$key] = $value;
	}
	
	public function __toString() {
		return $this->rel . ' (href=' . $this->getAttribute('href') . ')';
	}
}

?>