<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Controller\Session;
use \ArrayObject;

/**
 * A wrapper for the in-built session handling
 * 
 * Designed to allow better testing. Uses all the internal session commands,
 * so will use whichever session handler is registered.
 *
 * @author jarrod.swift
 */
class SessionWrapper extends ArrayObject implements \ORM\Interfaces\SessionWrapper {
    private $_sessionStarted;
    
    /**
     * Start the session and load the session variables
     * 
     * @param string $sessionName 
     */
    public function start( $sessionName = 'PHPSESSION' ) {
        $this->_sessionStarted = true;
        session_name( $sessionName );
        session_start();
        $this->exchangeArray($_SESSION);
    }

    /**
     * Write and close the current session
     */
    public function writeClose() {
        $this->_sessionStarted = false;
        $_SESSION = $this->getArrayCopy();
        session_write_close();
    }

    /**
     * Destroy the current session
     */
    public function destroy() {
        $this->_sessionStarted = false;
        session_destroy();
    }
    
    /**
     * Has the session been started
     * @return boolean
     */
    public function started() {
        return $this->_sessionStarted;
    }
    
    public function regenerateId() {
        session_regenerate_id(true);
    }
}
