<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Controller;
use ORM\Exceptions\ControllerDoesNotExistException;
use ORM\Interfaces\ClassRegister;
use ReflectionClass;

/**
 * Description of ControllerFactory
 *
 * @todo usage example (FrontController)
 * 
 * @author jarrodswift
 * @see ControllerRegister, BaseController
 */
class ControllerFactory {
    /**
     * @var ClassRegister $_register
     */
    private $_register;
    
    /**
     * Construct anew factory
     * 
     * @param ClassRegister $register 
     *      Register of controllers
     */
    public function __construct( ClassRegister $register ) {
        $this->_register = $register;
    }
    
    /**
     * Get a controller object to match the supplied controller name
     * 
     * @throw ControllerDoesNotExistException if controller class cannot be located
     *        or the class found does not subclass BaseController
     * @param string $controllerName
     * @return BaseController
     *      A controller object (which will be a sublass of BaseController)
     */
    public function get( $controllerName, $constructorArgs = array() ) {
        $class = $this->_register->getClassName($controllerName);
        
        if( $class === false ) {
            throw new ControllerDoesNotExistException( "Unable to load controller $controllerName" );
        } elseif( count($constructorArgs)) {
            $reflection = new ReflectionClass( $class );
            return $reflection->newInstanceArgs( $constructorArgs );
        } else {
            return new $class;
        }
    }
    
    /**
     * Set the register for this factory
     * @param ClassRegister $register 
     */
    public function setRegister( ClassRegister $register ) {
        $this->_register = $register;
    }
    
    /**
     * Get the current controller register
     * @return ClassRegister
     */
    public function getRegister() {
        return $this->_register;
    }
}