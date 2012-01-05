<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Utilities;
use \ORM\Interfaces\DebugLog;

/**
 * Simple class to help with debugging tasks
 * 
 * Using this for debugging makes it easy to disable debug remarks on production
 * servers and control how (or if) debugs are loggged)
 *
 * <b>Simple Usage</b>
 * @code
 * $myObject = Car::Find();
 * Debug::Dump($myObject);
 * 
 * // Would output the contents of $myObject
 * @endcode
 * 
 * <b>Options</b>
 * You can disable output system wide by calling \c Debug::SetDisplayOutput(false)
 * . If you don't alter the 'SetDisplayOutput' setting, it will use the PHP setting
 * for display_errors.
 * 
 * You can enable logging of errors by defining a class that implements the
 * Interfaces\\DebugLog interface and then nominating it \c Debug::SetLogStore( $classname )
 * 
 * @see DebugCorrectLog in tests/Mock
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
    
    /**
     * @var DebugLog $_lastLogObject
     */
    private $_lastLogObject;
    
    /**
     * Private constructor, this is a singleton class (sort of)
     */
    private function __construct() {
        if ( is_null(self::$_displayDebug) ) {
            self::$_displayDebug = ini_get('display_errors');
        }
    }
    
    /**
     * Dump the contents of a variable
     * 
     * @see debugObject()
     * @param mixed $object
     *      Object to debug
     * @return string
     *      The output contents (or what would have been output had it been allowed)
     */
    public static function Dump( $object ) {
        return self::Get()->debugObject( $object );
    }
    
    /**
     * A class name to use for storing logs
     * 
     * @throws Exception
     * @param string $storeClass 
     *      A class name. Must implement the Interfaces\\DebugLog
     */
    public static function SetLogStore( $storeClass ) {
        if ( !is_null($storeClass) && !in_array( 'ORM\Interfaces\DebugLog', class_implements($storeClass) ) ) {
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
    public static function SetDisplayOutput( $displayDebug = true ) {
        self::$_displayDebug = $displayDebug;
    }
    
    /**
     * Get (and create if neccasary) the instance of Debug
     * @return Debug
     */
    public static function Get() {
        if ( is_null(self::$_instance) ) {
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
        
        if ( self::$_displayDebug ) {
            echo $output;
        }
        
        if ( self::$_logStoreClass ) {
            $this->_lastLogObject = new self::$_logStoreClass;
            $this->_lastLogObject->store( $object );
        }
        
        return $output;
    }
    
    /**
     * Get the last debug log object (if it exists)
     * @return DebugLog
     *      Will be null if either debugLogStore is null or nothing has been logged
     */
    public function lastLogObject() {
        return $this->_lastLogObject;
    }
}