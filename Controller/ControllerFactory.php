<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Controller;
use ORM\Exceptions\ControllerDoesNotExistException;

/**
 * Description of ControllerFactory
 *
 * @author jarrodswift
 */
class ControllerFactory {
    /**
     * Associative array of registered controller
     * 
     * Keys are controller "names" and values are class names
     * 
     * @var array $_registerControllers
     * @see registerControllers(), get()
     */
    protected $_registerControllers = array();
    
    /**
     * Create a new factory, registering a path to find controllers
     * 
     * @param string $classPath
     *      [optional] Path to controllers. See registerControllers() for details
     *      on adding class paths to controllers.
     */
    public function __construct( $classPath = null ) {
        
    }
    
    /**
     * Get the correct controller object for a given controller name
     * 
     * This is the factory method.
     * 
     * @throws ControllerDoesNotExistException if unable to resolve $controlleName
     *         to a class
     * 
     * @param string $controllerName 
     * @return BaseController
     *      A subclass of BaseController is always returned
     */
    public function get( $controllerName ) {
        if(array_key_exists($controllerName, $this->_registerControllers)) {
            
        } else {
            throw new ControllerDoesNotExistException("Unable to load a controller for $controllerName");
        }
    }
    
    /**
     *
     * \note Will only register subclasses of BaseController
     * 
     * @todo Convert this to using an interface instead of abstract class to determine
     *       the validity of a controller
     * 
     * @param string $classPath 
     * @return array
     *      The array of registered controllers is returned
     */
    public function registerControllers( $classPath ) {
        
        return $this->_registerControllers;
    }
}