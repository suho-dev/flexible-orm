<?php
/**
 * Define interface for ORM objects
 *
 * @file 
 * @package ORM
 * @author Jarrod Swift
 */
namespace ORM\Interfaces;
/**
 * The interface to define the minimum external methods implemented
 * by an ORM object.
 * 
 */
interface ORMInterface {
    /**
     * Find one object
     *
     * @return ORM_Core
     * @param mixed $id		Or an option array as in base_model::save()
     */
    static function Find( $id );

    /**
     * Find all instances
     * @return ModelCollection
     */
    static function FindAll( $options );

    /**
     * Delete an item (static method)
     *
     * @return void
     * @param mixed $id 	The row id
     */
    static function Destroy( $id );

    /**
     * Remove this object from the database
     *
     * Leaves the current object populated, but deletes the database record
     *
     */
    public function delete();

    /**
     * Save the changes to (or create) this object
     *
     * @return mixed
     */
    function save();

    /**
     * Check whether or not this object is
     * in a valid state
     *
     * @return bool
     */
    function valid();

    /**
     * Return current error messages
     * @return array
     */
    function errorMessages();

    /**
     * Return the error message for a specific field
     *
     * @return string
     * @param string $field
     */
    function errorMessage( $field );

    /**
     * Add an error regarding validation
     *
     * @param string $field		The field name that is invalid
     * @param string $message	The message regarding why it is invalid
     * @return string
     */
    function validationError( $field, $message );

    /**
     * Return the unique identifier for this object
     * @return mixed
     */
    function id();
}