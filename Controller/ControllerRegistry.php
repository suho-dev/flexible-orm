<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Controller;
use ORM\AutoLoader;
use ORM\Exceptions\ControllerNamespaceUnknownException;
use \ArrayObject;

/**
 * Description of ControllerRegistry
 *
 * @author jarrodswift
 */
class ControllerRegistry extends ArrayObject {
    /**
     * The class all controllers must extend
     */
    const CONTROLLER_CLASS = '\ORM\Controller\BaseController';
    
    /**
     * @var AutoLoader $_autoloader
     */
    private $_autoloader;
    
    /**
     * Add a namespace to the controller registry
     * 
     * The registry will search for controller classes directly under the supplied
     * namespace. Classes are located using the AutoLoader class
     * 
     * @param string $namespace
     *      Namespace containing controller files
     * @param string $prefix 
     *      [optional] Prefix for the controllers in this namespace for addressing
     *      them directly (if there are conflicts). Defaults to the lowercase
     *      last namespace (eg \c \ORM\Controllers would give prefix of "controllers")
     */
    public function addNamespace( $namespace, $prefix = null ) {
        $prefix = is_null($prefix) ?  $this->_getPrefixFromNamespace($namespace) : $prefix;
        $this[$prefix] = $namespace;
    }
    
    /**
     * Determine a prefix by namespace
     * 
     * @see addNamespace()
     * @param string $namespace
     * @return string 
     */
    private function _getPrefixFromNamespace( $namespace ) {
        return basename(str_replace('\\', '//', strtolower( $namespace) ) );
    }
    
    /**
     * Get the classname matching specified controller name
     * 
     * \note If prefix is not provided, will return the first class that matches
     *       from all namespaces registered.
     * 
     * @throws ControllerNamespaceUnknownException if unknown $prefix supplied
     * 
     * @param string $controllerName
     *      The controller name (uses BaseController::ControllerName() )
     * @param string $prefix 
     *      [optional] Search only within a specified namespace. Default is to
     *      search all
     * @return string
     *      A fully qualified class name or false if none found
     */
    public function getClassName( $controllerName, $prefix = null ) {
        if( is_null($prefix) ) {
            foreach( $this as $prefix => $namespace ) {
                $className = $this->_getClassNameFromNamespace($controllerName, $namespace);
                if( $className !== false ) {
                    return $className;
                }
            }
            
        } elseif( !array_key_exists($prefix, $this)) {
           throw new ControllerNamespaceUnknownException("No registered namespace matching prefix '$prefix'");
        } else {
            return $this->_getClassNameFromNamespace( $controllerName, $this[$prefix] );
        }
    }
    
    /**
     *
     * @param string $controllerName
     * @param string $prefix 
     * @return string
     *      A fully qualified class name or false if none found
     */
    private function _getClassNameFromNamespace( $controllerName, $namespace ) {
        $mostLikelyClassPath = $namespace.'/'.ucfirst($controllerName) ;
        $location            = $this->_autoloader->locate( $mostLikelyClassPath );
        
        // Shortcut to guess most likely location for controller
        if( file_exists($location) ) {
            if( is_subclass_of($mostLikelyClassPath, self::CONTROLLER_CLASS)  && $mostLikelyClassPath::ClassName() == $controllerName ) {
                return $mostLikelyClassPath;
            }
        }
    }
    
    /**
     * Set the AutoLoader object for this registry
     * 
     * The autoloader is used to determine the location of the namespaces, so
     * the location can be scanned for Controller files.
     * 
     * @param AutoLoader $autoloader 
     */
    public function setAutoLoader( AutoLoader $autoloader ) {
        $this->_autoloader = $autoloader;
    }
}
