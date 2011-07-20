<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\SDB;

/**
 * An alternative DataFactory for use with Amazon SDB backed models
 *
 * @todo Write SDB utility tutorial
 * @see SDBStatement, ORMModelSDB, SDBResponse
 */
class SDBFactory implements \ORM\Interfaces\DataFactory {
    /**
     * Get a prepared statement that can be executed / bound
     *
     * return SDBStatement
     */
     public static function Get( $sql, $database = null, $callingClass = null ) {
         $statement = new SDBStatement( $sql );
         if( !is_null($callingClass) && is_subclass_of($callingClass, '\ORM\SDB\ORMModelSDB') ) {
             $statement->setConsistentRead( $callingClass::EnforceReadConsistency() );
         }
         
         return $statement;
     }

    /**
     * Returns the ID of the last inserted row
     *
     * return string|null
     *      Key value
     */
    public static function LastInsertId() {
        return SDBStatement::LastInsertId();
    }
    
    /**
     * Required for DataFactory interface
     * 
     * \note not currently used
     * 
     * @param string $table
     * @return array
     */
    public function fieldNames( $table ) {
        return array();
    }
}