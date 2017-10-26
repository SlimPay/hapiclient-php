<?php
namespace HapiClient\Hal;

use HapiClient\Exception\RelNotFoundException;
use HapiClient\Exception\LinkUniqueException;
use HapiClient\Exception\LinkNotUniqueException;
use HapiClient\Exception\EmbeddedResourceUniqueException;
use HapiClient\Exception\EmbeddedResourceNotUniqueException;

interface ResourceInterface
{
    /**
     * All the properties of the resource
     * ("_links" and "_embedded" not included).
     * @return	Associative array
     */
    public function getState();

    /**
     * All the links directly available in the resource.
     * The key is the relation type (Rel) and the value
     * can be either a Link or a numeric array of Links.
     * 
     * Note that there is no guarantees as to the order of the links. 
     * @return	Associative array
     */
    public function getAllLinks();

    /**
     * All the embedded resources directly available in the resource.
     * The key is the relation type (Rel) and the value
     * can be either a Resource or a numeric array of Resources.
     * 
     * Note that there is no guarantees as to the order of the embedded resources. 
     * @return	Associative array
     */
    public function getAllEmbeddedResources();

    /**
     * Finds a unique link by its relation type.
     * @param $rel	RegisteredRel|CustomRel		The relation type.
     * @return	Link	The Link referenced by the given rel.
     * @throws LinkNotUniqueException
     * @throws RelNotFoundException
     */
    public function getLink($rel);

    /**
     * Finds an array of links by their relation type.
     * Note that there is no guarantees as to the order of the links.
     * @param $rel	RegisteredRel|CustomRel		The relation type.
     * @return	Numeric array of links referenced by the given rel
     * @throws LinkUniqueException
     * @throws RelNotFoundException
     */
    public function getLinks($rel);

    /**
     * Finds a unique embedded resource by its relation type.
     * @param $rel	RegisteredRel|CustomRel		The relation type.
     * @return	Resource	The Resource referenced by the given rel.
     * @throws EmbeddedResourceNotUniqueException
     * @throws RelNotFoundException
     */
    public function getEmbeddedResource($rel);

    /**
     * Finds an array of embedded resources by their relation type.
     * Note that there is no guarantees as to the order of the resources. 
     * @param $rel	RegisteredRel|CustomRel		The relation type.
     * @return	Numeric array of embedded resources referenced by the given rel.
     * @throws EmbeddedResourceUniqueException
     * @throws RelNotFoundException
     */
    public function getEmbeddedResources($rel);

    /**
     * Builds a Resource from its JSON representation.
     * @param $json		string|array|object		A JSON representing the resource.
     * @return	Resource
     */
    public static function fromJson($json);
}
