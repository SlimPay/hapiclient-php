<?php
namespace HapiClient\Http;

use HapiClient\Http\Auth\AuthenticationMethod;
use HapiClient\Hal\Resource;
use HapiClient\Hal\RegisteredRel;
use HapiClient\Exception;
use \GuzzleHttp\Client;
use \GuzzleHttp\ClientInterface;
use \GuzzleHttp\Message\RequestInterface;
use \GuzzleHttp\Stream\Stream;
use \GuzzleHttp\UriTemplate;

final class HapiClient
{
    private $apiUrl;
    private $entryPointUrl;
    private $profile;
    private $authenticationMethod;
    
    private $client;
    
    private $entryPointResource;
    
    /**
     * @param $apiUrl			string	The URL pointing to the API server.
     * @param $entryPointUrl	string	The URL to the entry point Resource.
     * @param $profile			string	The URL pointing to the HAL profile containing
     *									the resources and their descriptors.
     *									If specified, the client will send an Accept header
     *									with application/hal+json and a profile attribute
     *									containing the value set here.
     * @param $authenticationMethod	AuthenticationMethod	The authentication method.
     */
    public function __construct(
            $apiUrl = null,
            $entryPointUrl = '/',
            $profile = null,
            AuthenticationMethod $authenticationMethod = null)
    {
        $this->apiUrl                = trim($apiUrl);
        $this->entryPointUrl        = trim($entryPointUrl);
        $this->profile                = trim($profile);
        $this->authenticationMethod    = $authenticationMethod;
        
        if ($this->apiUrl) {
            $baseUrl = rtrim($this->apiUrl, '/') . '/';
            $this->client = new Client(['base_url' => $baseUrl]);
        } else {
            $this->client = new Client();
        }
    }
    
    /**
     * @return	string	The URL pointing to the API server.
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }
    
    /**
     * @return	string	The URL to the entry point Resource.
     */
    public function getEntryPointUrl()
    {
        return $this->entryPointUrl;
    }
    
    /**
     * @return	string	The URL pointing to the HAL profile.
     */
    public function getProfile()
    {
        return $this->profile;
    }
    
    /**
     * @return	AuthenticationMethod	The authentication method
     */
    public function getAuthenticationMethod()
    {
        return $this->authenticationMethod;
    }
    
    /**
     * The HAPI Client uses a Guzzle client internally
     * to send all the HTTP requests.
     *
     * @return	\GuzzleHttp\Client	The Guzzle client (passed by reference)
     */
    public function &getClient()
    {
        return $this->client;
    }
    
    /**
     * The magic setter is overridden to insure immutability.
     */
    final public function __set($name, $value)
    {
    }
    
    /**
     * @param $request	Request	The Request object containing all the parameters
     *							necessary for the HTTP request.
     *
     * @return Resource	The Resource object returned by the server.
     */
    public function sendRequest(Request $request)
    {
        // Create the HTTP request
        $httpRequest = $this->createHttpRequest($request);
        
        // Send the request
        $httpResponse = $this->executeHttpRequest($httpRequest);
        
        // Check the status code (must be 2xx)
        $statusCode = $httpResponse->getStatusCode();
        if ($statusCode >= 200 && $statusCode < 300) {
            return Resource::fromJson((string) $httpResponse->getBody());
        }
        
        // Exception depending on status code for 3xx, 4xx and 5xx
        if ($statusCode >= 300 && $statusCode < 400) {
            throw new Exception\HttpRedirectionException($httpRequest, $httpResponse);
        } elseif ($statusCode >= 400 && $statusCode < 500) {
            throw new Exception\HttpClientErrorException($httpRequest, $httpResponse);
        } elseif ($statusCode >= 500 && $statusCode < 600) {
            throw new Exception\HttpServerErrorException($httpRequest, $httpResponse);
        } else {
            throw new Exception\HttpException($httpRequest, $httpResponse);
        }
    }
    
    /**
     * @param $follow	array|Follow	The Follow object or an array of Follow objects containing
     *									the parameters necessary for the HTTP request(s).
     * @param $resource	null|Resource	The resource containing the link you want to follow.
     *									If null, the entry point Resource will be used.
     *
     * @return Resource	The Resource object contained in the last response.
     */
    public function sendFollow($follow, Resource $resource = null)
    {
        if (!$resource) {
            $resource = $this->getEntryPointResource();
        }
        
        if (!is_array($follow)) {
            $follow = array($follow);
        }
        
        foreach ($follow as $hop) {
            $resource = $this->sendRequest(new Request(
                $hop->getUrl($resource),
                $hop->getMethod(),
                $hop->getUrlVariables(),
                $hop->getMessageBody(),
                $hop->getHeaders()
            ));
        }
        
        return $resource;
    }

    /**
     * Sends a request to the API entry point URL ("/" by default)
     * and returns its Resource object.
     * 
     * The entry point Resource is only retrieved if needed
     * and only once per HapiClient instance.
     * @return	Resource	The entry point Resource.
     * @throws HttpException 
     */
    public function getEntryPointResource()
    {
        if ($this->entryPointResource) {
            return $this->entryPointResource;
        }
        
        return $this->entryPointResource = $this->sendRequest(new Request($this->entryPointUrl));
    }
    
    /**
     * Attempts to refresh the Resource by sending a GET request
     * to the URL referenced by the "self" relation type.
     * If the resource doesn't have such relation type or the request fails,
     * the same resource is returned.
     * @param $resource	Resource	The Resource to refresh.
     * @return	The refreshed Resource or the same Resource if failed to refresh it.
     */
    public function refresh($resource)
    {
        try {
            $url = $resource->getLink(RegisteredRel::SELF)->getHref();
            return $this->sendRequest(new Request($url));
        } catch (\Exception $ignored) {
            return $resource;
        }
    }
    
    /**
     * Instantiates the HttpRequest depending on the
     * configuration from the given Request.
     * @param $request	Request	The Request configuration.
     * @return	The HTTP request.
     */
    private function createHttpRequest(Request $request)
    {
        // The URL
        $url = ltrim(trim($request->getUrl()), '/');
        
        // Handle templated URLs
        if ($urlVariables = $request->getUrlVariables()) {
            $url = (new UriTemplate())->expand($url, $urlVariables);
        }
        
        // Create the request (we will handle the exceptions)
        $httpRequest = $this->client->createRequest($request->getMethod(), $url, ['exceptions' => false]);
        
        // The message body
        if ($messageBody = $request->getMessageBody()) {
            $httpRequest->setHeader('Content-Type', $messageBody->getContentType());
            $httpRequest->setHeader('Content-Length', $messageBody->getContentLength());
            $httpRequest->setBody(Stream::factory($messageBody->getContent()));
        }
        
        // Accept hal+json response
        if ($this->profile) {
            $accept = 'application/hal+json; profile="' . $this->profile . '"';
        } else {
            $accept = 'application/json';
        }
        
        $httpRequest->setHeader('Accept', $accept);
        
        // Additional headers if specified
        foreach ($request->getHeaders() as $key => $value) {
            $httpRequest->setHeader($key, $value);
        }
        
        // "verify" the request if needed
        self::verify($httpRequest);
        
        return $httpRequest;
    }
    
    /**
     * Looks for a Certificate Authority file in the CA folder
     * that matches the host and update the 'verify' option
     * to its full path.
     * If no specific file regarding a host is found, uses
     * curl-ca-bundle.crt by default.
     */
    private static function verify(RequestInterface &$httpRequest)
    {
        $extensions = ['crt', 'pem', 'cer', 'der'];
        $caDir = __DIR__ . '/../CA/';
        
        // Must be https
        $url = $httpRequest->getUrl();
        if (substr($url, 0, 5) != 'https') {
            $httpRequest->getConfig()->set('verify', false);
            return;
        }
        
        // Default
        $httpRequest->getConfig()->set('verify', $caDir . 'curl-ca-bundle.crt');
        
        // Look for a host specific CA file
        $host = strtolower(parse_url($url, PHP_URL_HOST));
        if (!$host) {
            return;
        }
        
        $filename = $host;
        do {
            // Look for the possible extensions
            foreach ($extensions as $ext) {
                if (file_exists($verify = $caDir . $filename . '.' . $ext)) {
                    $httpRequest->getConfig()->set('verify', $verify);
                    return;
                }
            }
            
            // Remove a subdomain each time
            $filename = substr($filename, strpos($filename, '.') + 1);
        } while (substr_count($filename, '.') > 0);
        
        // No specific match
        return;
    }
    
    /**
     * Sends the HTTP request.
     * @param $httpRequest	The HTTP request to send.
     * @return	The HTTP response.
     * @throws HttpException	May be raised by the authentication method.
     */
    private function executeHttpRequest(RequestInterface $httpRequest)
    {
        // Authorization
        if ($this->authenticationMethod) {
            $this->authenticationMethod->authorizeRequest($this, $httpRequest);
        }
        
        // Execution
        $httpResponse = $this->client->send($httpRequest);
        
        // If Unauthorized, maybe the authorization just timed out.
        // Try it again to be sure.
        if ($httpResponse->getStatusCode() == 401 && $this->authenticationMethod != null) {
            // Authorize again
            $this->authenticationMethod->authorizeRequest($this, $httpRequest);
            
            // Execute again
            $httpResponse = $this->client->send($httpRequest);
        }
        
        return $httpResponse;
    }
}
