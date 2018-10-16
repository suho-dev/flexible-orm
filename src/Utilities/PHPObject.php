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

    static function GetBasenameFromClass($class) {
        return static::CleanControllerName(
            get_class($class)
        );
    }

    static function GetBasenameFromClassname($className) {
        return basename( strtolower( str_replace(
            array('Controller', '_',    '\\'),
            array('',           '',     '/'),
            $className
        )));
    }
}
