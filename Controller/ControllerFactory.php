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
     * @var ControllerRegistry $_registeredControllers
     * @see registerControllers(), get()
     */
    protected $_registeredControllers;
    
    /**
     * Create a new factory, registering a path to find controllers
     * 
     * @param string $namespace
     *      [optional] Path to controllers. See registerControllers() for details
     *      on adding namespaces to the factory.
     * @param string $prefix
     *      [optional] If there are duplicate names, a prefix will allow you to resolve 
     *      conflicting names. If not provided, it will automatically try to use
     *      the last namespace in the $namespace
     */
    public function __construct( $namespace = null, $prefix = null ) {
        $this->_registeredControllers = new ControllerRegistry;
        
        if( !is_null($namespace) ) {
            $this->registerControllers($namespace, $prefix);
        }
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
        if(array_key_exists($controllerName, $this->_registeredControllers)) {
            
        } else {
            throw new ControllerDoesNotExistException("Unable to load a controller for $controllerName");
        }
    }
    
    /**
     * Register a path for controller files
     * 
     * \note Will only register subclasses of BaseController
     * 
     * @todo Convert this to using an interface instead of abstract class to determine
     *       the validity of a controller
     * 
     * @param string $namespace 
     * @param string $prefix
     *      [optional] If there are duplicate names, a prefix will allow you to resolve 
     *      conflicting names. If not provided, it will automatically try to use
     *      the last namespace in the $namespace
     * @return array
     *      The array of registered namespaces is returned
     */
    public function registerControllers( $namespace, $prefix = null ) {
        $this->_registeredControllers->addNamespace($namespace, $prefix);
        return $this->_registeredControllers->getArrayCopy();
    }
    
    
    /**
     * Unregister some or all of the registered controllers
     * 
     * @see registerControllers()
     * @param string $prefix 
     *      [optional] Remove all controllers that were registered with this prefix.
     *      If not provided, remove all registered controllers. If an unknown prefix
     *      is supplied, no controllers will be deregistered
     * @return array
     *      The array of registered namespaces is returned
     */
    public function unRegisterController( $prefix = null ) {
        if( is_null($prefix) ) {
            $this->_registeredControllers = new ControllerRegistry;
        } elseif(array_key_exists($prefix, $this->_registeredControllers)) {
            unset( $this->_registeredControllers[$prefix] );
        }
        
        return $this->_registeredControllers->getArrayCopy();
    }
    
}