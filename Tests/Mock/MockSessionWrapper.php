<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Tests\Mock;
use ORM\Interfaces\SessionWrapper;
/**
 * Description of MockSessionWrapper
 *
 * @author jarrod.swift
 */
class MockSessionWrapper implements SessionWrapper {
    public $sessionStarted      = false;
    public $sessionDestroyed    = false;
    public $sessionName;
    
    private $_session;
    
    public function __construct( array $sessionVariables = array() ) {
        $this->_session = $sessionVariables;
    }
    
    public function start( $sessionName ) {
        if( $this->sessionStarted ) {
            throw new \RuntimeException("Attempt to start a session that had already been started");
        }
        
        $this->sessionName      = $sessionName;
        $this->sessionStarted   = true;
        $this->sessionDestroyed = false;
    }

    public function writeClose() {
        $this->sessionStarted   = false;
    }

    public function destroy() {
        $this->sessionDestroyed = true;
    }
    
    public function offsetExists ( $offset ) {
        $this->_checkSessionStatus();
        return array_key_exists( $offset, $this->_session );
    }
    
    public function offsetGet ( $offset ) {
        $this->_checkSessionStatus();
        return $this->_session[$offset];
    }
    
    public function offsetSet ( $offset , $value ) {
        $this->_checkSessionStatus();
        return $this->_session[$offset] = $value;
    }
    
    public function offsetUnset ( $offset ) {
        $this->_checkSessionStatus();
        unset( $this->_session[$offset] );
    }
    
    private function _checkSessionStatus() {
        if( !$this->sessionStarted ) {
            throw new \LogicException("Attempt to access a session that has not been started");
        }
    }
}
