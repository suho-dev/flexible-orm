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
 * Is controlled by the setting of error_reporting(). Changing the error_reporting
 * setting at any point will change the behaviour of this class.
 *
 * <b>Usage</b>
 * @code
 * $errorHandler = new ErrorHandler();
 * $errorHandler->register();
 * 
 * // From now on any errors that match the error_reporting settings will be converted to exceptions
 * @endcode
 * 
 * @see ErrorHandler::register()
 */
class ErrorHandler {
    /**
     * Register this object as the error handler
     * 
     * @param boolean $passThroughErrors
     *      [optional] Set to true to allow errors processed by this class to then  
     *      be propogated up to the default handler also.
     */
    public function registerErrorHandler( $passThroughErrors = false ) {
        $me = $this;
        set_error_handler(function($errorType, $errorMessage, $file, $line, $context) use($me, $passThroughErrors) {
            $me->handleError($errorType, $errorMessage, $file, $line, $context);
            
            return !$passThroughErrors;
        });
    }
    
    public function registerShutdownHandler() {
        
    }
    
    /**
     * Register this object to handle errors (with default settings)
     * 
     * Will become the error handler and add to the shutdown handler stack.
     * 
     * @see registerShutdownHandler(), registerErrorHandler()
     */
    public function register() {
        $this->registerErrorHandler();
        $this->registerShutdownHandler();
    }
    
    /**
     * The function that handles the PHP error
     * 
     * @throws PHPNoticeException, PHPErrorException or PHPWarningException depending
     *         on the error type
     * 
     * @param int $errorType
     *      The error type (or number). Will be one of http://ca3.php.net/manual/en/errorfunc.constants.php
     * @param string $errorMessage
     * @param string $file
     * @param string $line
     * @param array $context 
     */
    public function handleError( $errorType, $errorMessage, $file, $line, array $context = array()) {
        if( $this->displayErrorOfType($errorType) ) {
            $exception = $this->_getException($errorType, $errorMessage);
            $exception->setFile($file);
            $exception->setLine($line);
            
            throw $exception;
        }
    }
    
    /**
     * Get the correct exception object for the specified error type
     *
     * @param int $errorType
     * @param string $errorMessage
     * @return Exceptions\PHPRaisedErrorException
     */
    private function _getException( $errorType, $errorMessage ) {
        switch ($errorType) {
            case E_USER_DEPRECATED:
            case E_USER_NOTICE:
            case E_STRICT:
            case E_NOTICE:
            case E_DEPRECATED:
                return new Exceptions\PHPNoticeException($errorMessage);

            case E_COMPILE_WARNING:
            case E_CORE_WARNING:
            case E_USER_WARNING:
            case E_WARNING:
                return new Exceptions\PHPWarningException($errorMessage);

            case E_COMPILE_ERROR:
            case E_CORE_ERROR:
            case E_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
            default:
                return new Exceptions\PHPErrorException($errorMessage);
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

