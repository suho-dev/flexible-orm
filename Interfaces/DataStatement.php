<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Interfaces;
/**
 * All prepared statements returned by a DataFactory class must implement this
 * interface
 *
 * @see DataFactory::Get()
 * @author jarrod.swift
 *
 */
interface DataStatement {
    /**
     * Execute the prepared statement
     * 
     * @see http://www.php.net/manual/en/pdostatement.execute.php
     */
    public function execute( $values );
    
    /**
     * Bind a variable to a placeholder
     * 
     * @see http://www.php.net/manual/en/pdostatement.bindparam.php
     */
    public function bindParam( $placeholder, &$value );
    
    /**
     * Bind a value to a placeholder
     */
    public function bindValue( $placeholder, $param );
    
    /**
     * Bind all placeholders to the corresponding object properties
     * 
     * e.g.
     * @code
     * UPDATE car SET doors = :doors, name = :name
     * @endcode
     * 
     * Would attempt to bind \c $object->doors to :doors and \c $object->name to
     * :name
     */
    public function bindObject( $object, array $params );
    
    /**
     * Fetch the results of the query in to an object of this classname
     */
    public function fetchInto( $className );
    
    /**
     * Fetch all rows returned as instances of class $className
     * 
     * @return ModelCollection
     */
    public function fetchAllInto( $className );
}
?>
