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
namespace ORM;

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
    static $defaultDebugClassName = 'HTMLDebug';

    /**
     * Set the Debug class for when one has not been specified.
     *
     * When the debug class is called directly, it returns an instance of the
     * default class name. This allows you to controll the way an application
     * debugs in different environments, so as to not expose the internals of
     * your application.
     *
     * <b>Usage</b>
     * @code
     * Debug::SetDefaultDebugClass( '\MyApp\MyDebugBuffer' );
     *
     * $debugger = Debug::Get();
     *
     * // Is exactly the same as:
     * $debugger = \MyApp\MyDebugBuffer::Get();
     *
     * // Setting to the Debug class means no output
     * Debug::SetDefaultDebugClass( 'Debug' );
     * @endcode
     *
     * @note Will not raise an error if the class does not exist. An error will be
     *      raised when Get() is called.
     *
     * @param string $className
     *      Class name for the debug output class if none is specified. If the
     *      classname cannot be found, then it assumes the class is in the same
     *      namespace as this class.
     */
    public static function SetDefaultDebugClass( $className ) {
        self::$defaultDebugClassName = class_exists($className) ? $className : __NAMESPACE__.'\\'.$className;
    }

    /**
     * Get a Debug class instance
     *
     * <b>Usage</b>
     * @code
     * // Use the default debugger
     * Debug::Get()->dump( $myObject );
     *
     * // Or use a specific debugger
     * HTMLDebug::Get()->dump( $myObject );
     * @endcode
     *
     * @todo Exceptions for this class
     * @return Debug
     *      Returns an instance of the current debug class, either using the
     *      default (see SetDefaultDebugClass()) or the called class
     */
    public static function Get() {
        $className = get_called_class() == __NAMESPACE__.'\Debug' ? self::$defaultDebugClassName : get_called_class();

        return new $className;
    }

    /**
     * Dump the contents of an object for debugging
     *
     * A wrapper function for PHP's print_r().
     *
     * @param mixed $object
     *      The item that you want to inspect
     * @param string $title
     *      [optional] A title for the output (can make it easier if you are
     *      outputting multiple objects). If not supplied the title will be the
     *      object cast to type \e string
     * @return mixed
     *      Depends on the debug class. For classes that output to stdout (eg
     *      HTMLDebug) this will be the length of the output string.
     */
    public function dump( $object, $title = false ) {
        ob_start();
        print_r($object);
        $output =  ob_get_clean();
        
        return $this->_output( $output, $title ?: (string)$object );
    }

    /**
     * Output a string for debugging, encoding it
     *
     * @see For non-encoded output, see show()
     *
     * @param string $message
     *      Message to display. Will be HTML encoded
     * @return mixed
     *      Depends on the debug class. For classes that output to stdout (eg
     *      HTMLDebug) this will be the length of the output string.
     */
    public function show( $message ) {
        return $this->_output( $message );
    }

    /**
     * Output a string for debugging
     *
     * Simple text debugger
     *
     * @param string $message
     *      Message to display
     * @return mixed
     *      Depends on the debug class. For classes that output to stdout (eg
     *      HTMLDebug) this will be the length of the output string.
     */
    public function message( $message ) {
        return $this->_output( htmlentities($message) );
    }

    public function backtrace( $title = 'Debug Backtrace' ) {

    }

    /**
     * The output control method
     *
     * This will be overriden in child classes to determine how they output. The
     * debug class itself merely returns the output string, nothing will be logged
     * or added to the output itself.
     *
     * @param string $string
     *      The string to debug
     * @param string $title
     *      [optional] The title if this output has a title
     * @return string
     */
    protected function _output( $string, $title = false ) {
        return $string;
    }
}