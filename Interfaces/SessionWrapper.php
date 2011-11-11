<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Interfaces;

/**
 * Simple interface to define a session wrapper
 * 
 * This exists mainly to allow mocking for testing
 * 
 * @author jarrod.swift
 */
interface SessionWrapper extends \ArrayAccess {
    /**
     * Set the name and start the session
     */
    public function start( $sessionName );

    /**
     * Write and close the session
     */
    public function writeClose();

    /**
     * Destroy the currently open session
     */
    public function destroy();
    
    /**
     * Has the session  been started?
     * 
     * @return boolean
     */
    public function started();        
}
