<?php
namespace HapiClient\Http;

abstract class AbstractRequest
{
    protected $method;
    protected $urlVariables;
    protected $messageBody;
    protected $headers;
    
    /**
     * @param $method		string		GET, POST, PUT, PATCH or DELETE
     * @param $urlVariables	array		The value of the URL variables contained in the URL template
     * @param $messageBody	MessageBody	The messageBody to send with the request
     * @param $headers		array		Optional headers
     */
    protected function __construct($method = 'GET', array $urlVariables = null, MessageBody $messageBody = null, array $headers = null)
    {
        $method = strtoupper(trim($method));
        if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
            throw new \InvalidArgumentException('Method must be one of GET, POST, PUT, PATCH or DELETE.');
        }
        
        $this->method = $method;
        $this->urlVariables = (array) $urlVariables;
        $this->messageBody = $messageBody;
        $this->headers = (array) $headers;
    }
    
    /**
     * @return	int 	GET, POST, PUT, PATCH or DELETE
     */
    public function getMethod()
    {
        return $this->method;
    }
    
    /**
     * @return	array	The value of the URL variables contained in the URL template.
     */
    public function getUrlVariables()
    {
        return $this->urlVariables;
    }
    
    /**
     * @return	MessageBody	The message body to be sent with the request.
     */
    public function getMessageBody()
    {
        return $this->messageBody;
    }
    
    /**
     * @return	The optional headers.
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * The magic setter is overridden to insure immutability.
     */
    final public function __set($name, $value)
    {
    }
}
