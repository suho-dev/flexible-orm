<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Utilities;
use ORM\Exceptions;

/**
 * To help with development, debuging and logging this class converts PHP errors
 * into exceptions.
 *
 */
class ErrorHandler {
    /**
     * Register this object as the error handler
     */
    public function registerErrorHandler() {
        $me = $this;
        set_error_handler(function($errorType, $errorMessage, $file, $line, $context) use($me) {
            $me->handleError($errorType, $errorMessage, $file, $line, $context);
        });
    }
    
    public function registerShutdownHandler() {
        
    }
    
    public function handleError( $errorType, $errorMessage, $file, $line, array $context = array()) {
        if( $this->displayErrorOfType($errorType) ) {
            switch ($errorType) {
                case E_USER_DEPRECATED:
                case E_USER_NOTICE:
                case E_STRICT:
                case E_NOTICE:
                case E_DEPRECATED:
                    throw new Exceptions\PHPNoticeException($errorMessage);
                    
                case E_COMPILE_WARNING:
                case E_CORE_WARNING:
                case E_USER_WARNING:
                case E_WARNING:
                    throw new Exceptions\PHPWarningException($errorMessage);
                    
                case E_COMPILE_ERROR:
                case E_CORE_ERROR:
                case E_ERROR:
                case E_USER_ERROR:
                case E_RECOVERABLE_ERROR:
                default:
                    throw new Exceptions\PHPErrorException($errorMessage);
            }
        }
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

