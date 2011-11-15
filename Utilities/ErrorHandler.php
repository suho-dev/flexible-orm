<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Utilities;

/**
 * To help with development, debuging and logging this class converts PHP errors
 * into exceptions.
 *
 */
class ErrorHandler {
    
    public function registerErrorHandler() {
        
    }
    
    public function registerShutdownHandler() {
        
    }
    
    public function handleError( $errorType, $errorMessage, $file, $line, array $context = array()) {
        
    }
    
    /**
     * Determine whether an error of the supplied type would be displayed with
     * the current error_reporting settings
     * 
     * @param int $errorType 
     * @return boolean
     */
    public function displayErrorOfType( $errorType ) {
        return (error_reporting() & $errorType) == true;
    }
}

