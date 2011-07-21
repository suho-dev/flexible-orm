<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Utilities;

/**
 * Simple class top allow the Object-oriented method of accessing configuration
 * options
 *
 * Mainly exists simply to prevent errors when accessing non-existant options
 *
 * @see Configuration
 */
class ConfigurationGroup {
    /**
     * Create a new object based on an array of options (usually loaded from
     * and ini file)
     * 
     * @param array $options
     *      Associative array of option names and values
     */
    public function __construct( array $options ) {
        foreach ( $options as $key => $value ) {
            $this->$key = $value;
        }
    }

    /**
     * Return the value of a property or null if it does not exist
     *
     * @param string $name
     *      Property name requested
     * @return mixed
     *      Will return null in the even the property does not exist
     */
    public function __get( $name ) {
        return isset($this->$name) ? $this->$name : null;
    }
}
?>
