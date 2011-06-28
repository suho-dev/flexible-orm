<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Controller;

/**
 * Description of BaseController
 *
 * With PHP5.4 this would work well as a trait instead
 */
abstract class BaseController {
    /**
     * The request variables (ie GET, POST and COOKIE values)
     * @var Request $request
     */
    protected $_request;
    
    /**
     * The request action's name
     * @var string $_actionName
     */
    protected $_actionName;
    
    /**
     * Construct a new controller with request parameters
     * 
     * <b>Usage Example</b>
     * @code
     * // Build the request object
     * $request = new Request( $_GET, $_POST, $_COOKIES );
     * 
     * // Assuming Controller is an implementation of the abstract BaseController
     * // class...
     * 
     * // run the action specified in $_GET['action']
     * $controller = new Controller( $request );
     * $controller->performAction();
     * @endcode
     * 
     * @param Request $request
     *      Request parameters
     */
    public function __construct( Request $request ) {
        $this->_request     = $request;
        $this->_actionName  = $request->get->action;
    }
    
    /**
     * Perform a controller action
     * 
     * @throws \ORM\Exceptions\InvalidActionException
     * @param string $action 
     *      [optional] Force an action name to run (overrides the value of $_actionName)
     */
    public function performAction( $action = null ) {
        $actionName = is_null($action) ? $this->_actionName : $action;
        
        if( is_callable(array($this, $actionName))) {
            $this->$actionName();
        } else {
            throw new \ORM\Exceptions\InvalidActionException("Unknown action $actionName");
        }
    }
    
    /**
     * Redirect to another action and terminate execution
     * 
     * \note This function halts execution, nothing that occurs after it will 
     *       be run. If it did not terminate, then all code after a redirection
     *       still occurs, probably wasting resources.
     * 
     * @see URL(), ControllerName()
     * 
     * @param string $action
     *      The action name
     * @param string $controller
     *      [optional] The controller the action is in (see ControllerName()).
     *      Defaults to the current controller.
     * @param string|array $params
     *      The get paramaters to add to the url. Either an associative array
     *      of values or a single value (which will be used as the value of a
     *      variable named 'id')
     */
    public function redirectTo( $action, $controller = null, $params = array() ) {
        $url = static::URL( $action, $controller, $params );
        
        header("Location: $url");
        die("Redirecting to $url");
    }
    
    /**
     * Generate a URL for a controller action
     * 
     * Used by redirectTo()
     * 
     * @param string $action
     *      The action name
     * @param string $controller
     *      [optional] The controller the action is in (see ControllerName()).
     *      Defaults to the current controller.
     * @param string|array $params
     *      The get paramaters to add to the url. Either an associative array
     *      of values or a single value (which will be used as the value of a
     *      variable named 'id')
     * @return string
     *      A relative URL string
     */
    public static function URL( $action, $controller = null, $params = array() ) {
        $paramArray     = is_array($params) ? $params : array('id' => $params);
        $controllerName = $controller ?: static::ControllerName();
        
        $id     = isset($paramArray['id']) ? $paramArray['id'] : '';
        $url    = "/$controllerName/$action/$id";
        
        unset($paramArray['id']);
        
        return count($paramArray) ? "$url?".http_build_query($paramArray) : $url;
        
    }
    
    /**
     * The name of this controller
     * 
     * @see URL, redirectTo()
     * @return string
     *      The classname but lowercase without underscores or the word 
     *      'Controller' in it.
     */
    public static function ControllerName() {
        return basename( strtolower( str_replace(
                array('Controller', '_', '\\'), array('','','/'), get_called_class() 
        )));
    }
}
