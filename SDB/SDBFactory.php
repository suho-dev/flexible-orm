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
     public static function Get( $sql, $database = null ) {
         return new SDBStatement( $sql );
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
}
?>
