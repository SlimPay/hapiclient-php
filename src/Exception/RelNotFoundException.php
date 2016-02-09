<?php
namespace HapiClient\Exception;

/**
 * Raised when trying to get a link or an embedded
 * resource by a non-existing relation type (Rel).
 */
class RelNotFoundException extends \Exception
{
    private $missingRel;
    private $availableRels;

    /**
     * @param $missingRel		string|Rel				The missing relation type.
     * @param $availableRels	Numeric array	The list of available relation types.
     */
    public function __construct($missingRel, array $availableRels)
    {
        parent::__construct(
            'Rel not found: ' . $missingRel . '. ' .
            'Relation types available: ' . implode(', ', $availableRels) . '.'
        );
        
        $this->missingRel = $missingRel;
        $this->availableRels = $availableRels;
    }
    
    /**
     * @return	Rel		The missing relation type.
     */
    public function getMissingRel()
    {
        return $this->missingRel;
    }

    /**
     * Returns the list of available relation types available in the
     * _links or _embedded property the given Rel was missing in.
     * @return Numeric array.
     */
    public function getAvailableRels()
    {
        return $this->availableRels;
    }
}
