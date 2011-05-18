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
 * Interface for classes used to store debug logs
 * 
 * @see Debug
 * @author jarrod.swift
 */
interface DebugLog {
    /**
     * Log the contents of the supplied object
     * 
     * @param mixed $object
     *      The object being debugged
     * @return void
     */
    public function store( $object ); 
}

?>
