<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Interfaces;
/**
 * Interface for SQL prepared statement factory classes
 *
 * @see ORM_Model::DataFactory(), DataStatement
 * @author jarrod.swift
 */
interface DataFactory {
    /**
     * Get a prepared statement that can be executed / bound
     *
     * @param string $sql
     * @param string $database
     * @param string $callingClass
     * @return DataStatement
     *      A DataFactory should return a prepared statement class that implements
     *      the DataStatement interface.
     */
     public static function Get( $sql, $database, $callingClass );

    /**
     * Returns the ID of the last inserted row, or the last value from a sequence
     * object, depending on the underlying driver.
     *
     * @param string $database
     * @return mixed
     *      Key value
     */
    public static function LastInsertId( $database );
    
    /**
     * Get the names of each field from the database table structure
     * 
     * @param string $table
     *      The table name 
     * @return array
     *      Field names in a numerically indexed array
     */
    public function fieldNames( $table );
}