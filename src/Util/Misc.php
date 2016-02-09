<?php
namespace HapiClient\Util;

class Misc
{

    /**
     * Recursively removes all the keys
     * containing an empty string as value.
     * Also removes empty arrays in the process.
     * @param	$array	The array to filter
     *
     * @return	the filtered array
     */
    public static function removeEmptyStrings($array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::removeEmptyStrings($value);
                
                if (empty($array[$key])) {
                    unset($array[$key]);
                }
            } elseif ($value === '') {
                unset($array[$key]);
            }
        }
        
        return $array;
    }
}
