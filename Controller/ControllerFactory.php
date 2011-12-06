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
 * Factory for controllers 
 * 
 * <b>Usage</b>
 * @code
 * $register = new ControllerRegister();
 * $register->registerNamespace( '\MyProject\Controllers' );
 * 
 * $factory = new ControllerFactory( $register );
 * 
 * // Gets a controller matching the supplied name
 * $controller = $factory->get( $_GET['controller'] );
 * @endcode 
 *
 * \n<b>Using Contructor Args</b>
 * It's also possible to pass values to the Controller constructor
 * @code
 * $factory = new ControllerFactory( $register );
 * $controller = $factory->get( 
 *      $_GET['controller'],
 *      array( new Request( $_GET, $_POST ), new SmartyTemplate )
 * );
 * @endcode
 * 
 * 
 * @see ControllerRegister, BaseController
 */
class ControllerFactory {
    /**
     * @var ClassRegister $_register
     */
    private $_register;
    
    /**
     * Construct a new factory
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
     * 
     * @param string $controllerName
     * @param array $constructorArgs
     *      [optional] Array of values to pass to the Controller's constructor
     * @return BaseController
     *      A controller object (which will be a sublass of BaseController)
     */
    public function get( $controllerName, $constructorArgs = array() ) {
        $class = $this->_register->getClassName($controllerName);
        
        if( $class === false ) {
            throw new ControllerDoesNotExistException( "Unable to load controller $controllerName" );
        } else {
            $reflection = new ReflectionClass( $class );
            return $reflection->newInstanceArgs( $constructorArgs );
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