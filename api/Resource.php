<?php
namespace SlimPay;

class Resource {
	private $body;
	
	public function __construct(\GuzzleHttp\Message\Response $response) {
		$this->body = (string) $response->getBody();
	}
	
	public function getBody($format = 'string') {
		if (in_array($format, ['array', 'object'])) {
			if (($body = json_decode($this->body, $format == 'array')) !== null)
				return $body;
			else
				throw new \Exception('Unable to parse JSON data. Raw data: ' . $body);
		} elseif ($format == 'string')
			return $this->body;
		else
			throw new \Exception('$format must be array, object or string. Format provided: ' . $format);
	}
	
	public function getLinks() {
		$body = $this->getBody('array');
		
		$links = [];
		if (isset($body['_links']))
			foreach ($body['_links'] as $rel => $attributes)
				$links[] = new Link($rel, $attributes);
		
		return $links;
	}
	
	public function getLink($rel, $strict = false) {
		if ($links = $this->getLinks())
			foreach ($links as $link)
				if ($link->getRel() == $rel || (!$strict && preg_match('#\#' . $rel . '$#i', $link->getRel())))
					return $link;
		
		throw new Exception\LinkNotFoundException($rel, $this->getLinks());
	}
}

?>