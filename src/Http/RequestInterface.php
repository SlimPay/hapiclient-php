<?php
namespace HapiClient\Http;

interface RequestInterface
{
    /**
     * @return	string	The URL
     */
    public function getUrl();

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
}
