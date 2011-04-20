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
 * Object-oriented wrapper for APCcache
 *
 * Very simple cache interface, the main purpose of which is to allow simple switching
 * between alternative caching methods, such as APCCache and Memcached.
 *
 *
 * @see Cache
 *
 */
class APCCache implements \ORM\Interfaces\Cache {
    /**
     * Prefix to add to all objects cached (allows other systems to use APC
     * simultaneously.
     */
    const PREFIX = "ORM:";

    /**
     * \copydoc Cache::set()
     * 
     * @see get(), add()
     * @return void
     * @param string $name
     *      Unique identifier for this item in cache
     * @param mixed $object
     *      The object to store in cache
     * @param int $ttl
     *      [optional] Seconds until cache expires (0 == never). Default = 0
     */
    public function set( $name, $object, $ttl = 0 ) {
        apc_store( self::PREFIX.$name, $object, $ttl );
    }

    /**
     * \copydoc Cache::get()
     *
     * @return mixed
     * @param string $name
     *      Unique identifier for this item in cache
     */
    public function get( $name ) {
        return apc_fetch( self::PREFIX.$name );
    }

    /**
     * \copydoc Cache::add()
     *
     * @see set(), get()
     * @return void
     * @param string $name
     *      Unique identifier for this item in cache
     * @param mixed $object
     *      The object to store in cache
     * @param int $ttl
     *      [optional] Seconds until cache expires (0 == never). Default = 0
     */
    public function add( $name, $object, $ttl = 0 ) {
        apc_add( self::PREFIX.$name, $object, $ttl );
    }

    /**
     * Clear the cache
     *
     * @see delete()
     * @return void
     */
    public function flush() {
        apc_clear_cache();
    }

    /**
     * \copydoc Cache::delete()
     *
     * @see flush()
     * @return void
     * @param string $name
     *      Unique identifier for this item in cache
     */
    public function delete( $name ) {
        apc_delete( self::PREFIX.$name );
    }
}
