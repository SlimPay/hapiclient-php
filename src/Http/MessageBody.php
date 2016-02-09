<?php
namespace HapiClient\Http;

abstract class MessageBody
{
    /**
     * @return	string	The Content-Type header.
     */
    abstract public function getContentType();
    
    /**
     * @return	string	The Content-Length header.
     */
    abstract public function getContentLength();
    
    /**
     * @return	string	The content.
     */
    abstract public function getContent();
    
    /**
     * The magic setter is overridden to insure immutability.
     */
    final public function __set($name, $value)
    {
    }
    
    /**
     * @return	string	The content.
     */
    final public function __toString()
    {
        return $this->getContent();
    }
}
