<?php
namespace HapiClient\Http;

use HapiClient\Http\Auth\AuthenticationMethod;
use HapiClient\Hal\ResourceInterface;
use \GuzzleHttp\ClientInterface;

interface HapiClientInterface
{
    /**
     * @return	string	The URL pointing to the API server.
     */
    public function getApiUrl();

    /**
     * @return	string	The URL to the entry point Resource.
     */
    public function getEntryPointUrl();

    /**
     * @return	string	The URL pointing to the HAL profile.
     */
    public function getProfile();

    /**
     * @return	AuthenticationMethod	The authentication method
     */
    public function getAuthenticationMethod();

    /**
     * The HAPI Client uses a Guzzle client internally
     * to send all the HTTP requests.
     *
     * @return	\GuzzleHttp\ClientInterface	The Guzzle client (passed by reference)
     */
    public function &getClient();

    /**
     * @param $request	RequestInterface	The Request object containing all the parameters
     *										necessary for the HTTP request.
     *
     * @return ResourceInterface	The Resource object returned by the server.
     */
    public function sendRequest(RequestInterface $request);

    /**
     * @param $follow	array|FollowInterface	The Follow object or an array of Follow objects containing
     *											the parameters necessary for the HTTP request(s).
     * @param $resource	null|ResourceInterface	The resource containing the link you want to follow.
     *											If null, the entry point Resource will be used.
     *
     * @return ResourceInterface	The Resource object contained in the last response.
     */
    public function sendFollow($follow, ResourceInterface $resource = null);

    /**
     * Sends a request to the API entry point URL ("/" by default)
     * and returns its Resource object.
     * 
     * The entry point Resource is only retrieved if needed
     * and only once per HapiClient instance.
     * @return	ResourceInterface	The entry point Resource.
     * @throws HttpException 
     */
    public function getEntryPointResource();

    /**
     * Attempts to refresh the Resource by sending a GET request
     * to the URL referenced by the "self" relation type.
     * If the resource doesn't have such relation type or the request fails,
     * the same resource is returned.
     * @param $resource	ResourceInterface	The Resource to refresh.
     * @return	The refreshed Resource or the same Resource if failed to refresh it.
     */
    public function refresh($resource);
}
