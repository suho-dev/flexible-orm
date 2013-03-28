<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace Suho\FlexibleOrm\Interfaces;

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
     * Regenerate session ID and destroy old session
     */
    public function regenerateId();
    
    /**
     * Has the session  been started?
     * 
     * @return boolean
     */
    public function started();        
}
