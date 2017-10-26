<?php
namespace HapiClient\Http;

use HapiClient\Hal\ResourceInterface;

interface FollowInterface {
    /**
     * Looks for a unique Link referenced by the set
     * relation type (Rel) and returns its href property.
     * @param $resource     ResourceInterface   The Resource containing a Link referenced
     *                                          by the set relation type (Rel).
     * @return  string  The URL in the href property of the Link.
     * @throws LinkNotUniqueException
     * @throws RelNotFoundException
     */
    public function getUrl(ResourceInterface $resource);

    /**
     * @return  Rel     The relation type.
     */
    public function getRel();

    /**
     * @return  int     GET, POST, PUT, PATCH or DELETE
     */
    public function getMethod();
    
    /**
     * @return  array   The value of the URL variables contained in the URL template.
     */
    public function getUrlVariables();
    
    /**
     * @return  MessageBody The message body to be sent with the request.
     */
    public function getMessageBody();
    
    /**
     * @return  The optional headers.
     */
    public function getHeaders();
}
