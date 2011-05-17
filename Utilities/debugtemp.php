<?php
/**
 * DEBUG Class Module
 * 
 * This file is self-contained so can be used independantly on other projects.
 * 
 * @package Utilities
 * @author Jarrod Swift
 * @file
 */
namespace ORM\Utilities;

/**
 * Simple class to help with debugging tasks
 * 
 * Using this for debugging makes it easy to disable debug remarks on production servers
 *
 * Operates as a simple factory
 *
 * @todo rewrite and document this class
 *
 * @todo add option to catch all errors (not exceptions)
 *
 * @todo add exception for unknown classname
 *
 * @todo implement backtrace
 *
 * @todo add disable output
 */
class Debug {
    /**
     * @var string $errorStoreClass
     */
    private $errorStoreClass;
    
    /**
     * @var Debug $_instance
     */
    private static $_instance;
    
    private function __construct( $errorStoreClass = false ) {
        
    }
    
    public static function Dump( $object ) {
        
    }
    
    public static function SetErrorStore( $storeClass ) {
        self::$_instance = new Debug( $storeClass );
    }
    
    public static function Get() {
        if( is_null(self::$_instance) ) {
            self::$_instance = new Debug();
        }
        
        return self::$_instance;
    }
}