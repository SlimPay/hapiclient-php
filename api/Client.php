<?php
namespace SlimPay;

class Client {
	  # The amount of calls to the request method
	private static $count = 0;
	
	  # The path to the SSL certificates (relative to this folder)
	private static $sandboxCertificate		= 'CA/GlobalSignRootCA.crt';
	private static $productionCertificate	= 'CA/GlobalSign.crt';
	
	private $apiUrl;
	private $profileUrl;
	private $exceptionsEnabled;
	
	private $client;
	private $token;
	
	private $appId;
	private $appSecret;
	
	/**
	 * @param	$apiUrl			The URL pointing to the SlimPay server:
	 *							Sandbox:		https://api-sandbox.slimpay.net
	 *							Production:		https://api.slimpay.net
	 * @param	$profileUrl	The URL pointing to the HAL profile containing
	 *							the resources and their descriptors.
	 *							If null, the Accept header sent
	 *							will be json instead of hal+json.
	 * @param	$exceptionsEnabled		If this is set to true, we will throw (depending on status code):
	 *							-	a RedirectionException for a 3xx
	 *							-	a ClientErrorException for a 4xx
	 *							-	a ServerErrorException for a 5xx
	 *							-	an HttpException for anything else not 2xx
	 */
	public function __construct($apiUrl, $profileUrl = null, $exceptionsEnabled = true) {
		$this->apiUrl = trim($apiUrl);
		$this->profileUrl = trim($profileUrl);
		$this->exceptionsEnabled = (boolean) $exceptionsEnabled;
		
		$this->client = new \GuzzleHttp\Client();
		$this->token = new Token();
	}
	
	/**
	 * @return	The URL pointing to the SlimPay server
	 */
	public function getApiUrl() {
		return $this->apiUrl;
	}
	
	/**
	 * @param	$apiUrl			The URL pointing to the SlimPay server:
	 *							Sandbox:		https://api-sandbox.slimpay.net
	 *							Production:		https://api.slimpay.net
	 */
	public function setApiUrl($apiUrl) {
		$this->apiUrl = $apiUrl;
	}
	
	/**
	 * @return	The URL pointing to the HAL profile
	 */
	public function getProfileUrl() {
		return $this->profileUrl;
	}
	
	/**
	 * @param	$profileUrl	The URL pointing to the HAL profile containing
	 *							the resources and their descriptors.
	 *							If null, the Accept header sent
	 *							will be json instead of hal+json.
	 */
	public function setProfileUrl($profileUrl) {
		$this->profileUrl = $profileUrl;
	}
	
	/**
	 * @return	true if we may throw exceptions
	 *			depending on the HTTP status code
	 */
	public function isExceptionsEnabled() {
		return $this->exceptionsEnabled;
	}
	
	/**
	 * @param	$exceptionsEnabled		If this is set to true, we will throw (depending on status code):
	 *							-	a RedirectionException for a 3xx
	 *							-	a ClientErrorException for a 4xx
	 *							-	a ServerErrorException for a 5xx
	 *							-	an HttpException for anything else not 2xx
	 */
	public function setExceptionsEnabled($exceptionsEnabled) {
		$this->exceptionsEnabled = (boolean) $exceptionsEnabled;
	}
	
	/**
	 * The Guzzle client used for all the HTTP requests.
	 *
	 * @return	The Guzzle client (passed by reference)
	 */
	public function &getClient() {
		return $this->client;
	}
	
	/**
	 * After using the oauth2 method, a Token object is returned.
	 *
	 * @return	The last token retrieved (passed by reference)
	 */
	public function &getToken() {
		return $this->token;
	}
	
	/**
	 * You may want to reuse a token. This method
	 * must be called before oauth2() but you still
	 * need to call it with your appId/appSecret
	 * so the client can refresh the token in
	 * case it expires.
	 *
	 * @param	$token		The token to use
	 */
	public function setToken(Token $token) {
		$this->token = $token;
	}
	
	/**
	 * @param	$resource	The resource that contains the link you want to follow.
	 * @param	$aFollows	The Follow object or array of Follow objects containing
	 *						the parameters necessary for the HTTP request.
	 *
	 * @return the Resource object contained in the last response
	 */
	public function follow($resource, $aFollows) {
		if (!is_array($aFollows))
			$aFollows = array($aFollows);
		
		foreach ($aFollows as $follow) {
			if (get_class($follow) != 'SlimPay\Follow')
				throw new \Exception('Unexpected class name for the follow object. ' .
									 'Expected Follow, given: ' . get_class($follow) . '.');
			
			$this->refreshOauth2();
			$url = $resource->getLink($follow->getRel())->getAttribute('href');
			$resource = $this->request(new Request($url, $follow->getMethod(), $follow->getData(), $follow->getFormat(), $follow->getHeaders()));
		}
		
		return $resource;
	}
	
	/**
	 * @param	$request	The Request object containing all the parameters
	 *						necessary for the HTTP request.
	 *
	 * @return the Resource object contained in the response
	 */
	public function request(Request $request) {
		  # We can handle both a relative or absolute URL
		$url = $request->getUrl();
		$url = $this->apiUrl . str_replace($this->apiUrl, '', $url);
		
		  # If we're asked to send data through a GET request,
		  # handle it before passing the url and reset the data
		$method = $request->getMethod();
		$data = $request->getData();
		if ($data && $method == 'GET') {
			$url = preg_replace('#\{[?&]?[^}]+\}#', '', $url);
			$url .= (strpos($url, '?') === false ? '?' : '&') . $this->parseData($data, 'urlencoded');
			$data = [];
		}
		
		  # Create the request (we will handle the exceptions)
		$guzzleRequest = $this->client->createRequest($method, $url, ['exceptions' => false]);
		
		  # SSL certificate
		$host = strtolower(parse_url($url, PHP_URL_HOST));
		if (strpos($host, 'slimpay.net') === false)
			$guzzleRequest->getConfig()->set('verify', false);
		elseif (strpos($host, 'api-sandbox') !== false)
			$guzzleRequest->getConfig()->set('verify', __DIR__ . '/' . self::$sandboxCertificate);
		else
			$guzzleRequest->getConfig()->set('verify', __DIR__ . '/' . self::$productionCertificate);
		
		  # Accept hal+json response
		if ($this->profileUrl)
			$guzzleRequest->setHeader('Accept', 'application/hal+json; profile="' . $this->profileUrl . '"');
		
		  # Authorization
		if ($this->token->isValid())
			$guzzleRequest->setHeader('Authorization', 'Bearer ' . $this->token->getValue());
		
		  # Data in any request method but GET
		if ($data) {
			$format = $request->getFormat();
			$guzzleRequest->setHeader('Content-Type', 'application/' . ($format == 'json' ? 'json' : 'x-www-form-urlencoded'));
			$guzzleRequest->setHeader('Content-Length', strlen($content = $this->parseData($data, $format)));
			$guzzleRequest->setBody(\GuzzleHttp\Stream\Stream::factory($content));
		}
		
		  # Additional headers if specified
		foreach ((array) $request->getHeaders() as $key => $value)
			$guzzleRequest->setHeader($key, $value);
		
		  # Send the request
		$response = $this->client->send($guzzleRequest);
		self::$count++;
		
		  # Exception depending on status code
		$statusCode = $response->getStatusCode();
		if ($this->exceptionsEnabled && ($statusCode < 200 || $statusCode >= 300)) {
			$reasonPhrase = $response->getReasonPhrase();
			if ($statusCode >= 300 && $statusCode < 400)
				throw new Exception\RedirectionException($response, $statusCode, $reasonPhrase);
			else if ($statusCode >= 400 && $statusCode < 500)
				throw new Exception\ClientErrorException($response, $statusCode, $reasonPhrase);
			else if ($statusCode >= 500 && $statusCode < 600)
				throw new Exception\ServerErrorException($response, $statusCode, $reasonPhrase);
			else
				throw new Exception\HttpException($response, $statusCode, $reasonPhrase);
		}
		
		  # Return a Resource object
		return new Resource($response);
	}
	
	/**
	 * Send a request to /oauth/token in order to
	 * retrieve a token.
	 *
	 * @param	$appId		Your appId
	 * @param	$appSecret	The appSecret for this appId
	 
	 * @return	true if the authentication succeeded
	 */
	public function oauth2($appId, $appSecret, $scope = 'api') {
		if (empty($appId))
			throw new \Exception("App ID can't be empty");
		
		if (empty($appSecret))
			throw new \Exception("App secret can't be empty");
		
		if ($scope !== 'api' && $scope !== 'api_admin')
			throw new \Exception("Scope must be one of 'api' and 'api_admin'");
		
		  # We save the credentials for the refreshOauth2 method
		$this->appId = $appId;
		$this->appSecret = $appSecret;
		
		  # Read the cached token
		$filename = 'token_' . hash('sha256', $appId);
		if (!$this->token->getValue() && $cached = Cache::read($filename))
			$this->token = clone $cached;
		
		  # If the token is still valid, no need to get a new one
		if ($this->token->isValid())
			return true;
		
		  # Get a new token
		$header = ['Authorization' => 'Basic ' . base64_encode($appId . ':' . $appSecret)];
		$body = $this->request(new Request('/oauth/token', 'POST', "grant_type=client_credentials&scope=$scope", 'urlencoded', $header))->getBody('array');
		
		  # Check the response
		if (!isset($body['access_token'], $body['expires_in'])) {
			$this->appId = $this->appSecret = null;
			return false;
		} else {
			  # We fill the Token object
			$this->token->setValue($body['access_token']);
			$this->token->setExpirationTime($this->token->getTime() + $body['expires_in']);
			
			  # We save the token
			Cache::save($filename, $this->token);
			
			return true;
		}
	}
	
	/**
	 * In case some requests take some time, the
	 * token may expire. This method makes sure
	 * it is still valid and if not, refreshes it.
	 *
	 */
	private function refreshOauth2() {
		if (!$this->token->isValid() && $this->appId)
			$this->oauth2($this->appId, $this->appSecret);
	}
	
	/**
	 * @param	$data	The data to parse.
	 *					Can be an object, an array or a string.
	 *
	 * @return	A json representation of the data given as a string
	 */
	private function parseJson($data) {
		if (is_array($data) || is_object($data)) {
			$result = (array) $data;
			$result = $this->filterEmptyStrings($result);
			$result = json_encode($result, JSON_UNESCAPED_UNICODE);
			$result = str_replace(array('"true"', '"false"'), array('true', 'false'), $result);
			
			return $result;
		} elseif (is_string($data))
			return $data;
		else
			throw new \Exception("json data must be an array, an object or a string representation of a valid json ('" . gettype($data) . "' provided).");
	}
	
	/**
	 * @param	$data	The data to parse.
	 *					Can be an object, an array or a string.
	 *
	 * @return	A query string representation of the data given as a string
	 */
	private function parseUrlencoded($data) {
		if (is_array($data) || is_object($data))
			return http_build_query($data);
		elseif (is_string($data))
			return $data;
		else
			throw new \Exception("urlencoded data must be an array, an object or a valid query string ('" . gettype($data) . "' provided).");
	}
	
	/**
	 * @param	$data	The data to parse.
	 *					Can be an object, an array or a string.
	 * @param	$format	json or urlencoded
	 *
	 * @return	A json or a query string representation
	 *			of the data given depending on $format as a string
	 */
	private function parseData($data, $format) {
		if ($format == 'json')
			return $this->parseJson($data);
		elseif ($format == 'urlencoded')
			return $this->parseUrlencoded($data);
		else
			throw new \Exception("Format not valid. Must be 'json' or 'urlencoded' ('$format' provided).");
	}
	
	
	/**
	 * Removes all the keys containing an empty string as value.
	 * @param	$array	The array to filter
	 *
	 * @return	the filtered array
	 */
	private function filterEmptyStrings($array) {
		foreach ($array as $key => $value)
			if (is_array($value)) {
				$array[$key] = $this->filterEmptyStrings($value);
				
				if (empty($array[$key]))
					unset($array[$key]);
			} elseif ($value === '')
				unset($array[$key]);
		
		return $array;
	}
	
	/**
	 * @return	The amount of calls to the request method
	 */
	public function getCount() {
		return self::$count;
	}
}

?>