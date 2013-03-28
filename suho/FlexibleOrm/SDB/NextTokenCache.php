<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace Suho\FlexibleOrm\SDB;

use \ORM\Utilities\Cache\APCCache;

/**
 * Simple container for some token caching
 * 
 * Currently requires a cache class to be defined in Configuration for caching
 * 
 */
class NextTokenCache {
    /**
     * @var Cache $_cache
     */
    private static $_cache;
    
    /**
     * Store a discovered nextToken
     * @param string $query
     * @param int $limit
     * @param int $offset
     * @param string $token 
     */
    public static function Store( $query, $limit, $offset, $token ) {
        self::_Cache()->set( 
            self::_CacheName($query, $limit, $offset),
            $token,
            180
        );
    }
    
    /**
     * Get the "nextToken" for a query/offset/limit combination if known
     * 
     * @see GetNearestToken
     * @param string $query
     * @param int $limit
     * @param int $offset 
     * @return string|false
     *      The next token if available
     */
    public static function GetToken( $query, $limit, $offset ) {
        return self::_Cache()->get(
            self::_CacheName($query, $limit, $offset)
        );
    }

    /**
     * Get the closest nextToken value for a given query and offset
     * 
     * Search the cache for any known tokens to limit the number of requests 
     * required to reach the requested offset.
     * 
     * @see GetToken()
     * @param string $query
     * @param int $limit
     * @param int $offset
     * @return array
     *      Array in the format of ([0] => offset, [1] => token). If none found
     *      will return array(0, false)
     */
    public static function GetNearestToken( $query, $limit, $offset ) {
        do {
            $token = self::GetToken( $query, $limit, $offset-- );
        } while ( $offset > 0 && $token === false );
        
        return $token ? array( ++$offset, $token ) : array( 0, false );
    }
    
    /**
     * Get the cache object
     * 
     * Cache object defined by Configuration::SetCacheClass()
     * @return Cache
     */
    private static function _Cache() {
        if ( is_null(self::$_cache) ) {
            self::$_cache = \ORM\Utilities\Configuration::GetCache();
        }
        
        return self::$_cache;
    }
    
    /**
     * Generate a name to use in cache for this combination
     * 
     * @param string $query
     * @param int $limit
     * @param int $offset
     * @return string
     *      A unique name for this combination 
     */
    private static function _CacheName( $query, $limit, $offset ) {
        $query = preg_replace("/LIMIT \d+/", '', $query);
        return trim($query)."-$limit-$offset";
    }
}