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
 * @todo Document the required methods
 */
interface DataStatement {
    public function execute( $values );
    public function bindParam( $placeholder, &$value );
    public function bindValue( $placeholder, $param );
    public function bindObject( $className, array $params );
    public function fetchInto( $className );
    public function fetchAllInto( $className );
}
?>
