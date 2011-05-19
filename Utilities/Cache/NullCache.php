<?php
/**
 * APCcache Implementation
 * 
 * @package Utilities
 * @author Jarrod Swift
 * @file
 */
namespace ORM\Utilities\Cache;

/**
 * This is an empty cache object, ie it implements the interface but does not cache
 * except for within a single request
 * 
 * \note $ttl is not implemented, all cached objects expire only at the end of the script
 *
 */
class NullCache implements \ORM\Interfaces\Cache {
    private static $_store = array();
    
    public function set( $name, $object, $ttl = 0 ) {
        self::$_store[$name] = $object;
    }

    public function get( $name ) {
        return isset(self::$_store[$name]) ? self::$_store[$name] : false;
    }

    public function add( $name, $object, $ttl = 0 ) {
        if( !array_key_exists( $name, self::$_store) ) {
            self::$_store[$name] = $object;
        }
    }

    public function flush() {
        self::$_store = array();
    }
    
    public function delete( $name ) {
        unset(self::$_store[$name]);
    }
}

?>
