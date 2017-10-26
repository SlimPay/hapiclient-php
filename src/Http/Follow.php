<?php
namespace HapiClient\Http;

use HapiClient\Hal\ResourceInterface;

final class Follow extends AbstractRequest implements FollowInterface
{
    private $rel;
    
    /**
     * @param $rel			Rel			The relation type.
     * @param $method		string		GET, POST, PUT, PATCH or DELETE
     * @param $urlVariables	array		The value of the URL variables contained in the URL template
     * @param $messageBody	MessageBody	The messageBody to send with the request
     * @param $headers		array		Optional headers
     */
    public function __construct($rel, $method = 'GET', array $urlVariables = null, MessageBody $messageBody = null, array $headers = null)
    {
        parent::__construct($method, $urlVariables, $messageBody, $headers);
        $this->rel = $rel;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getUrl(ResourceInterface $resource)
    {
        return $resource->getLink($this->rel)->getHref();
    }
    
    /**
     * {@inheritDoc}
     */
    public function getRel()
    {
        return $this->rel;
    }
}
