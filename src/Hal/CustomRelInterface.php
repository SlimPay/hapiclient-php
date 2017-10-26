<?php
namespace HapiClient\Hal;

/**
 * The Extension Relation Type described in:
 * - section 8.2 of the HAL specification
 * - section 4 of the RFC5988 - Web Linking document
 * 
 * @see https://tools.ietf.org/html/draft-kelly-json-hal-07#section-8.2
 * @see https://tools.ietf.org/html/rfc5988#section-4
 */
interface CustomRelInterface
{
    /**
     * @return  string  The relation name.
     */
    public function getName();
}
