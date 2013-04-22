<?php
/**
 * @file
 * @author pierre.dumuid@sustainabilityhouse.com.au
 */
namespace ORM\Utilities;

/**
 * Description of PHPObject
 *
 * @author pmdumuid
 */
class PHPObject {
    /**
     * Get the public properties of a php object.
     *
     * @param object $controller
     * @return type
     */
    static function GetPublicProperties($controller) {
        $vars = get_object_vars($controller);
        return array_keys($vars);
    }
}
