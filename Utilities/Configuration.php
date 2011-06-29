<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Utilities;

/**
 * The Configuration class is a simple class to keep all application settings in.
 *
 * Ini file groups allow the values to be kept in sensible groups.
 *
 * For more information see \ref configuration "Configuration Tutorial"
 *
 * @todo Exception for unloaded ini?
 *
 * @version 0.2
 */
class Configuration {
    /**
     * @var Configuration $_settings
     *      The singleton instance of the Configuration class
     */
    private static $_settings;

    /**
     * @var array $_options
     *      Associative (possibly 2-dimensional) array of settings
     */
    private $_options;
    
    /**
     * Private constructor
     * @return Configuration
     */
    private function __construct() {
        $this->_options  = array();
    }

    /**
     * @see Configuration::Value()
     * @param string $property
     *      The property name in the ini file
     * @param string $group
     *      [optional] If the ini file has groups, specify the tag
     * @return
     *      Return the value of the requested option property
     */
    private function _getValue( $property, $group = null ) {
        if( !is_null($group) ) {
            $value = isset($this->_options[$group][$property]) ? $this->_options[$group][$property] : null;
        } else {
            $value = isset($this->_options[$property]) ? $this->_options[$property] : null;
        }

        return $value === '' ? false : $value;
    }

    /**
     * Get an object with properties matching the group's set parameters
     * 
     * @param string $group
     *      The group name (ie the bit between the square brackets in the ini
     *      file
     * @return ConfigurationGroup
     */
    private function _getGroup( $group ) {
        $options = self::GroupExists($group) ? $this->_options[$group] : array();
        
        return new ConfigurationGroup( $options );
    }
    
    /**
     * Check if a group exists in the current configuration
     * @param string $group
     * @return boolean
     *      True if group name exists (although it may not have any properties
     *      in it.
     */
    public static function GroupExists( $group ) {
        return array_key_exists($group, self::Get()->_options);
    }

    /**
     * @see Configuration::Remove()
     * @param string $property
     *      The property name in the ini file
     * @param string $group
     *      [optional] If the ini file has groups, specify the tag
     * @return void
     */
    private function _removeValue( $property, $group = null ) {
        if( !is_null($group) ) {
            unset( $this->_options[$group][$property] );
        } else {
            unset( $this->_options[$property] );
        }
    }

    /**
     * Get a value of a particular setting
     *
     * Returns null if no setting stored
     *
     * <b>Usage</b>
     * @code
     * if( Configuration::value('debug') ) {
     * 	var_dump( $this );
     * }
     * @endcode
     *
     * @see Load()
     * @param string $attribute
     *      attribute from ini file
     * @param string $group
     *      [optional] If the ini file has groups, specify the tag
     * @return mixed
     */
    public static function Value( $attribute, $group = null ) {
        if( is_null(self::$_settings) ) self::$_settings = new Configuration();
        
        return self::$_settings->_getValue( $attribute, $group );
    }

    /**
     * Load an ini file into the configuration settings
     *
     * Loading multiple files will merge the options together, duplicated option
     * names will be overriden.
     *
     * <b>Usage</b>
     * @code
     * Configuration::Load( 'application.ini' );
     * @endcode
     *
     * @param string $ini_file
     *      Path to an ini file
     * @return void
     */
    public static function Load( $ini_file ) {
        if( is_null(self::$_settings) ) self::$_settings = new Configuration();

        self::$_settings->_add( parse_ini_file($ini_file, true) );
    }

    /**
     * Get the Configuration instance
     * 
     * @return Configuration
     *      Returns the single Configuration instance
     */
    public static function Get() {
        return self::$_settings;
    }
    
    /**
     * Get an instance of the configured cache object
     * 
     * To alter the cache object, add <code>[Cache]class = ""</code> to your ini
     * file or call SetCacheClass()
     * 
     * @return Cache
     */
    public static function GetCache() {
        if( $cacheClass = self::Value('cacheClass', 'Cache') ) {
            return new $cacheClass;
        }
        
        return new \ORM\Utilities\Cache\NullCache();
    }
    
    /**
     * Set the cache class for the whole system to use
     * @param string|null $className 
     */
    public static function SetCacheClass( $className ) {
        self::$_settings->_options['Cache'] = array('cacheClass' => $className);
    }

    /**
     * Clear all the configuration settings
     *
     * @see Load()
     */
    public static function Clear() {
        self::$_settings = null;
    }

    /**
     * Add options to the array
     *
     * @see Configuration::load()
     * @param array $options
     *      Associative array (which may be two-dimensional) of option names and
     *      values.
     * @return void
     */
    private function _add( $options ) {
        $this->_options = array_merge( $this->_options, $options );
    }

    /**
     * Remove a setting from the object
     *
     * @param string $attribute
     *      Attribute name from ini file
     * @param string $group
     *      [optional] If the ini file has groups, specify the tag
     * @return void
     */
    public static function Remove( $attribute, $group = null ) {
        self::$_settings->_removeValue( $attribute, $group );
    }

    /**
     * Allow ini groups to be called as static methods
     *
     * <b>Usage:</b>
     * @code
     * // Ini file:
     * [my_group]
     * option = 'value'
     *
     * [another_group]
     * option = 'no'
     * @endcode
     *
     * @code
     * // PHP
     * echo Configuration::my_group()->option; // outputs: value
     *
     * // PHP - Alternative method:
     * echo Configuration::my_group( 'option' ); // outputs: value
     * @endcode
     *
     * @param string $group
     *      The group name (eg "my_group" in the above example)
     * @param array $arguments
     *      Only the first argument is used and it is used as the property name
     * @return
     *      The requested property value
     */
    public static function __callStatic( $group, $arguments ) {
        if( is_null(self::$_settings) ) self::$_settings = new Configuration();

        if( isset($arguments[0]) ) {
            return self::$_settings->_getValue( $arguments[0], $group );
        } else {
            return self::$_settings->_getGroup( $group );
        }
    }

    /**
     * Send the current settings to the Debug class
     */
    public static function Debug() {
        Debug::Dump(self::$_settings);
    }
}
