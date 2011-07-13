<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Controller;
use \ORM\Interfaces\Template;

/**
 * Simple controller class for implementing a MVC stack
 *
 * An independent class (ie can be used without the ORM functions and vice-versa)
 * to implement the Controller part of an Model-View-Controller stack.
 * 
 * For usage instructions see \ref controller_tutorial "the controller tutorial".
 * 
 * There are two hooks available, beforeAction() and afterAction(). Overriding
 * these methods allows for controller-wide functionality such as checking
 * for a logged in user.
 * 
 */
abstract class BaseController {
    /**
     * Layout template name
     * 
     * This template will be used for all actions. The value 'action_content' will
     * be assigned to it, which contains the output of the action specific template.
     */
    const LAYOUT_TEMPLATE = 'layout';
    
    /**
     * The default action name if none provided
     */
    const DEFAULT_ACTION = 'index';
    
    /**
     * The request variables (ie GET, POST and COOKIE values)
     * @var Request $_request
     */
    protected $_request;
    
    /**
     * The request action's name
     * @var string $_actionName
     */
    protected $_actionName;
    
    /**
     * The templating object
     * @var Template $_template
     */
    protected $_template;
    
    /**
     * Whether or not to attempt to load a layout
     * 
     * @see _fetchTemplate()
     * @var boolean $_useLayout
     */
    protected $_useLayout = true;
    
    /**
     * Set the template name to override the default
     * @var string $_templateName
     */
    protected $_templateName;
    
    /**
     * The name of the called action
     * 
     * This exists for use in templates
     * @var string $actionName
     */
    public $actionName;
    
    /**
     * The name of the current controller
     * 
     * This exists for use in templates
     * @var string $controllerName
     */
    public $controllerName;
    
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
     * // run and output the template for the action specified in $_GET['action']
     * $controller = new Controller( $request, new SmartyTemplate() );
     * echo $controller->performAction();
     * @endcode
     * 
     * @param Request $request
     *      Request parameters
     * @param Template $template
     *      The templating object (eg SmartyTemplate) to use for output
     */
    public function __construct( Request $request, Template $template ) {
        $this->_request         = $request;
        $this->_actionName      = $request->get->action ?: static::DEFAULT_ACTION;
        $this->_template        = $template;
        $this->controllerName   = static::ControllerName();
    }
    
    /**
     * Perform a controller action
     * 
     * Compile the templates and return the output. Does not actually output 
     * anything itself. All public properties of the controller are assigned
     * to the template.
     * 
     * \n\n<b>Usage</b>
     * @code
     * // Use smarty for templating
     * $request     = new Request( $_GET, $_POST );
     * $controller  = new MyController( $request, new SmartyTemplate );
     * 
     * // Perform the action defined in $_GET['action'] and echo the output
     * echo $controller->performAction();
     * @endcode
     * 
     * \n\n<b>Options and Defaults</b>
     * 
     * By default, the template that will be requested from the Template class
     * will be 'controller/action'. This can be overriden by setting the
     * \c $_templateName property in the action (or the controller).
     * 
     * By default, the controller will look for a template called 'layout'. If 
     * it exists and the \c $_useLayout option is \c true then the template will be
     * called after the contents of the action's template has been fetched. The 
     * output of the action's template will be assigned to a variable \c action_content
     * in the layout template.
     * 
     * If no action name can be found (i.e. none is provided either in the $action
     * parameter nor the 'action' value of the get parameters) then \c DEFAULT_ACTION
     * is used from the current class.
     * 
     * <b><i>Example Layout File</i></b>\n
     * The following is an example Smarty template for layout.
     * 
     * \include controller.template.layout.tpl
     * \n\n
     * 
     * @see Template
     * @throws \ORM\Exceptions\InvalidActionException if a non-existant or
     *      non-public method has been requested as the action.
     * @param string $action 
     *      [optional] Force an action name to run (overrides the value of \c $_actionName)
     * @return string
     *      The output of the templating.
     */
    public function performAction( $action = null ) {
        $this->actionName = is_null($action) ? $this->_actionName : $action;
               
        if( !is_callable(array($this, $this->actionName))) {
            throw new \ORM\Exceptions\InvalidActionException("Unknown action, '$this->actionName' for the class, '".get_class($this)."'.");
        }
        
        $this->beforeAction();
        $this->{$this->actionName}();
        $this->afterAction();
        
        $this->_assignTemplateVariables();
        
        $this->_templateName = $this->_templateName ?: "$this->controllerName/$this->actionName";
        return $this->_fetchTemplate( $this->_templateName );
    }
    
    /**
     * Assign all public properties to the template object
     * 
     */
    private function _assignTemplateVariables() {
        $publicPropertiesFunction = (function( $controller ) {
            $vars = get_object_vars($controller);

            return array_keys($vars);
        });
        
        $properties = $publicPropertiesFunction( $this );
        
        foreach( $properties as $property ) {
            $this->_template->assign( $property, $this->$property );
        }
    }
    
    /**
     * Fetch the output for a template
     * 
     * Includes layout if $_useLayout is set to \c true.
     * 
     * @param string $templateName
     *      The template name to fetch
     * @return string
     *      The output of the template
     */
    private function _fetchTemplate( $templateName ) {
        $actionOutput = $this->_template->fetch($templateName);
        
        if( $this->_useLayout && $this->_template->templateExists(self::LAYOUT_TEMPLATE) ) {
            $this->_template->assign( 'action_content', $actionOutput );
            return $this->_template->fetch(self::LAYOUT_TEMPLATE);
            
        } else {
            return $actionOutput;
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
    
    /**
     * Called before the action method is called
     * 
     * Override this method for things that need to be run for each request in
     * a controller. The value of \c $actionName will be set before this is called
     * 
     * The default method does nothing
     * 
     * @see afterAction()
     */
    public function beforeAction() {
    }
    
    /**
     * Called after the action method is called
     * 
     * Override this method for things that need to be run for each request in
     * a controller, after the action. Called before variables are assigned to
     * the template.
     * 
     * The default method does nothing
     * 
     * @see beforeAction()
     */
    public function afterAction() {
    }
}
