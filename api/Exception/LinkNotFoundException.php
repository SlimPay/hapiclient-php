<?php
namespace SlimPay\Exception;

class LinkNotFoundException extends \Exception {
	private $rel;
	private $links;
	
	public function __construct($rel, $links) {
		parent::__construct('No link found for: ' . (string) $rel . '. Resource links: ' . implode(', ', $links));
	}
	
	public function getRel() {
		return $this->rel;
	}
	
	public function setRel($rel) {
		$this->rel = $rel;
	}
	
	public function getLinks() {
		return $this->links;
	}
	
	public function setLinks($links) {
		$this->links = $links;
	}
}
