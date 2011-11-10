<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Controller\Session;

/**
 * A wrapper for the in-built session handling
 * 
 * Designed to allow better testing. Uses all the internal session commands,
 * so will use whichever session handler is registered.
 *
 * @author jarrod.swift
 */
class SessionWrapper extends ArrayObject implements ORM\Interfaces\SessionWrapper {
    /**
     * Start the session
     * 
     * @param string $sessionName 
     */
    public function start( $sessionName = 'PHPSESSION' ) {
        session_name( $sessionName );
        session_start();
    }

    /**
     * Write and close the current session
     */
    public function writeClose() {
        session_write_close();
    }

    /**
     * Destroy the current session
     */
    public function destroy() {
        session_destroy();
    }
}
