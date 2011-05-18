<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\SDB;

use \ORM\Utilities\Cache\APCCache;

/**
 * Description of NextTokenCache
 * Simple container for some token caching
 */
class NextTokenCache {
    /**
     * @var APCCache $_cache
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
    
    public static function GetNearestToken( $query, $limit, $offset ) {
        $noToken = array( 0, false );
        do {
            $token = self::GetToken( $query, $limit, $offset-- );
        } while( $offset > 0 && $token === false );
        
        return $token ? array( ++$offset, $token ) : $noToken;
    }
    
    /**
     * Get the cache object
     * 
     * @return APCCache
     */
    private static function _Cache() {
        if( is_null(self::$_cache) ) {
            self::$_cache = new APCCache();
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
        return "$query-$limit-$offset";
    }
}
?>
