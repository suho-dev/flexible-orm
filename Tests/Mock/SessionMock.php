<?php
namespace FlexibleORMTests\Mock;
use ORM\Controller\Session;

/**
 * Extend the Session class to allow the removal of the singleton instance
 * to trigger the destructor
 * 
 * @author jarrod.swift
 */
class SessionMock extends Session {
    /**
     * Remove the current Session instance
     * 
     * Helpful for testing
     */
    public static function Clear() {
        self::$_staticSession = null;
    }
}
