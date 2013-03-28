<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace Suho\FlexibleOrm\Utilities;
use \ORM\Exceptions\PHPErrorException;
use \ORM\Exceptions\PHPErrorNoticeException;
use \ORM\Exceptions\PHPFatalErrorException;
use \ORM\Exceptions\PHPErrorWarningException;

/**
 * Description of ErrorHandler
 *
 * @author jarrod.swift
 */
class ErrorHandler {
    /**
     * Has this object be registered
     * @var boolean $_registered
     */
    private $_registered = false;
    
    /**
     * The name of the shutdown handler function for deregistering
     * @var string $_shutdownHandlerFunction
     */
    private $_shutdownHandlerFunction;
    
    /**
     * Checks if a specific error level is currently included in the error_reporting
     * 
     * \note This figure will be irrelevant when a function is silenced (i.e. called with
     *       an @ symbol), as that will always set error checking to 0.
     * 
     * @param int $errorLevel 
     * @return boolean
     */
    public function displayError( $errorLevel ) {
        return (error_reporting() & $errorLevel) == $errorLevel;
    }
    
    /**
     * Function that handles the error, converting it to a catchable exception
     * 
     * An exception is raised if the error level meets the current setting of
     * error_reporting.
     * 
     * \note If a function has been called with the silencer (the @ symbol) then 
     *       the exception will not be thrown.
     * 
     * @see register()
     * 
     * @throws ORM\Exceptions\PHPErrorException or a subclass of.
     * 
     * @param int $errorLevel
     * @param string $errorMessage
     * @param string $file
     * @param string $line
     * @param array $context 
     * @return boolean
     *      Alwats \c true
     */
    public function error( $errorLevel, $errorMessage, $file, $line, array $context = array() ) {
        if( $this->displayError($errorLevel) ) {
            switch ($errorLevel) {
                case E_NOTICE:
                case E_USER_NOTICE:
                case E_DEPRECATED:
                case E_USER_DEPRECATED:
                case E_STRICT:
                    throw new PHPErrorNoticeException( $errorMessage );

                case E_WARNING:
                case E_USER_WARNING:
                    throw new PHPErrorWarningException( $errorMessage );

                case E_ERROR:
                case E_USER_ERROR:
                case E_PARSE:
                    throw new PHPFatalErrorException( $errorMessage );
                    
                default:
                    throw new PHPErrorException( $errorMessage );
            }

        }
        
        return true;
    }
    
    /**
     * Handle FATAL and PARSE errors at shutdown
     */
    public function shutdownHandler() {
        $lastError = error_get_last();
        
        if (!is_null($lastError)){
            list($errorLevel, $errorMessage, $file, $line) = $lastError;
            $this->error($errorLevel, $errorMessage, $file, $line);
        }
    }
    
    
    /**
     * Register this class as the error handler for PHP
     * 
     * Registers an error handler plus a shutdown handler
     * 
     * @see deregister()
     */
    public function register() {
        $this->_registered  = true;
        $errorHandler       = $this; // until annonymous functions can use "this"
        
        set_error_handler(function($errorLevel, $errorMessage, $file, $line, $context) use ($errorHandler) {
            return $errorHandler->error($errorLevel, $errorMessage, $file, $line, $context);
        });
        
        $this->_shutdownHandlerFunction = function() use($errorHandler){
            $errorHandler->shutdownHandler();
        };
        
        register_shutdown_function($this->_shutdownHandlerFunction);
        
    }
    
    /**
     * Deregister this error handler
     * 
     * \note Will deregister ALL error handlers and return control to the default handler
     * 
     * @see register()
     */
    public function deregister() {
        if( !$this->_registered ) {
            throw new \RuntimeException('Cannot deregister ErrorHandler as it is not currently registered');
        }
        
        set_error_handler(function($errorLevel, $errorMessage, $file, $line, $context) {
            return false;
        });
        
        $this->_registered = false;
    }
}