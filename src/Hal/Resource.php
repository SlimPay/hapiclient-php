<?php
namespace HapiClient\Hal;

use HapiClient\Exception\RelNotFoundException;
use HapiClient\Exception\LinkUniqueException;
use HapiClient\Exception\LinkNotUniqueException;
use HapiClient\Exception\EmbeddedResourceUniqueException;
use HapiClient\Exception\EmbeddedResourceNotUniqueException;

/**
 * The Resource Object described in the
 * JSON Hypertext Application Language (draft-kelly-json-hal-07)
 * @see https://tools.ietf.org/html/draft-kelly-json-hal-07#section-4
 *
 * Note:	When trying to find a Link or an embedded Resource
 *			by their relation type (Rel), the search is done by
 *			comparing the lower-case relation name.
 * 
 * "When extension relation types are compared, they MUST be compared as
 * strings [...] in a case-insensitive fashion."
 * @see https://tools.ietf.org/html/rfc5988#section-4.2
 */
final class Resource implements ResourceInterface
{
    private $state;
    private $links;
    private $embeddedResources;
    
    public function __construct(array $state = null, array $links = null, array $embeddedResources = null)
    {
        $this->state = (array) $state;
        $this->links = (array) $links;
        $this->embeddedResources = (array) $embeddedResources;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllLinks()
    {
        return $this->links;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllEmbeddedResources()
    {
        return $this->embeddedResources;
    }

    /**
     * {@inheritDoc}
     */
    public function getLink($rel)
    {
        $link = self::findByRel($this->links, $rel);
        
        if (!$link) {
            throw new RelNotFoundException($rel, array_keys($this->links));
        }
        
        if (is_array($link)) {
            throw new LinkNotUniqueException();
        }
        
        return $link;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getLinks($rel)
    {
        $links = self::findByRel($this->links, $rel);
        
        if (!$links) {
            throw new RelNotFoundException($rel, array_keys($this->links));
        }
        
        if (!is_array($links)) {
            throw new LinkUniqueException();
        }
        
        return $links;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getEmbeddedResource($rel)
    {
        $resource = self::findByRel($this->embeddedResources, $rel);
        
        if (!$resource) {
            throw new RelNotFoundException($rel, array_keys($this->embeddedResources));
        }
        
        if (is_array($resource)) {
            throw new EmbeddedResourceNotUniqueException();
        }
        
        return $resource;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getEmbeddedResources($rel)
    {
        $resources = self::findByRel($this->embeddedResources, $rel);
        
        if (!$resources) {
            throw new RelNotFoundException($rel, array_keys($this->embeddedResources));
        }
        
        if (!is_array($resources)) {
            throw new EmbeddedResourceUniqueException();
        }
        
        return $resources;
    }
    
    /**
     * Looks for the given relation name in a case-insensitive
     * fashion and returns the corresponding value.
     * @return	mixed	The value in $a matching the relation name
     *					or null if not found.
     */
    private static function findByRel(array $a, $rel)
    {
        $relName = mb_strtolower($rel, 'UTF-8');
        foreach ($a as $name => $value) {
            if (mb_strtolower($name, 'UTF-8') === $relName) {
                return $value;
            }
        }
        
        return null;
    }
    
    /**
     * {@inheritDoc}
     */
    public static function fromJson($json)
    {
        if (!$json) {
            $json = [];
        }
        
        if (!is_array($json)) {
            if (is_object($json)) {
                $json = (array) $json;
            } elseif (is_string($json)) {
                $json = json_decode(trim($json) ? $json : '{}', true);
            } else {
                throw new \Exception("JSON must be a string, an array or an object ('" . gettype($json) . "' provided).");
            }
        }
        
        return new Resource(
            self::extractState($json),
            self::extractByRel($json, '_links'),
            self::extractByRel($json, '_embedded')
        );
    }

    private static function extractState(array $json)
    {
        unset($json['_links']);
        unset($json['_embedded']);
        
        return $json;
    }
    
    private static function extractByRel(array $json, $rel)
    {
        $out = [];
        
        if (!isset($json[$rel])) {
            return $out;
        }
        
        foreach ($json[$rel] as $name => $uniqueOrArray) {
            if (isset($uniqueOrArray[0])) { // Array
                $aArrayOfLinks = [];
                foreach ($uniqueOrArray as $unique) {
                    if ($rel == '_links') {
                        $aArrayOfLinks[] = Link::fromJson($unique);
                    } else {
                        $aArrayOfLinks[] = Resource::fromJson($unique);
                    }
                }
                
                $out[$name] = $aArrayOfLinks;
            } else { // Unique
                if ($rel == '_links') {
                    $out[$name] = Link::fromJson($uniqueOrArray);
                } else {
                    $out[$name] = Resource::fromJson($uniqueOrArray);
                }
            }
        }
        
        return $out;
    }
}
