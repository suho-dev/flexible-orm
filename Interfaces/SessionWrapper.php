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
    public function start( $sessionName );

    public function writeClose();

    public function destroy();
}
