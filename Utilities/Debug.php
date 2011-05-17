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
use \ORM\Interfaces\DebugLog;

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
     * @var string $_logStoreClass
     */
    private static $_logStoreClass;
    
    /**
     * True to output debugs. If left null it will use the setting in ini_set()
     * @var boolean $_displayDebug
     */
    private static $_displayDebug;
    
    /**
     * @var Debug $_instance
     */
    private static $_instance;
    
    private function __construct() {
        if( is_null(self::$_displayDebug) ) {
            self::$_displayDebug = ini_get('display_errors');
        }
    }
    
    /**
     * Dump the contents of a variable
     * 
     * @see debugObject()
     * @param mixed $object
     *      Object to debug
     */
    public static function Dump( $object ) {
        self::Get()->debugObject( $object );
    }
    
    /**
     * A class name to use for storing logs
     * 
     * @param string $storeClass 
     *      A class name. Must implement the Interfaces\DebugLog
     */
    public static function SetLogStore( $storeClass ) {
        if( !in_array( 'ORM\Interfaces\DebugLog', class_implements($storeClass) ) ) {
            throw new \Exception("Class $storeClass does not implement DebugLog");
        }
        
        self::$_logStoreClass = $storeClass;
    }
    
    /**
     * Set whether or not to output debugs
     * 
     * @param boolean $displayDebug
     *      True to display debugs
     */
    public static function SetDisplayErrors( $displayDebug = true ) {
        self::$_displayDebug = $displayDebug;
    }
    
    /**
     * Get (and create if neccasary) the instance of Debug
     * @return Debug
     */
    public static function Get() {
        if( is_null(self::$_instance) ) {
            self::$_instance = new Debug();
        }
        
        return self::$_instance;
    }
    
    /**
     * Debug an object (storing it if log store has been set)
     * 
     * @param mixed $object 
     * @return string
     *      The output (or at least what would be output if $_displayDebug was
     *      true)
     */
    public function debugObject( $object ) {
        ob_start();
        print_r( $object );
        $output = ob_get_clean();
        
        if( self::$_displayDebug ) {
            echo $output;
        }
        
        if( self::$_logStoreClass ) {
            $log = new self::$_logStoreClass;
            $log->store( $object );
        }
        
        return $output;
    }
}