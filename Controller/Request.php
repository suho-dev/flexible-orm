<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Controller;
use ORM\Controller\Request\Variables;
use \LogicException;

/**
 * Represents the request parameters
 * 
 * Provides convenience methods for setting defaults (reducing the number of
 * times you have to write things like <code>$id = isset($_POST['id']) ? $_POST['id'] : 0;</code>)
 * and for checking values against a rule for security.
 * 
 * It also facilitates better testing of controllers and security by allowing
 * modification of the request variables used by the controller.
 * 
 * <b>Usage</b>
 * @code
 * $request = new Request( $_GET, $_POST );
 * 
 * // Using default values for a possibly present $_GET value
 * $name = $request->get->name( 'none provided' );
 * // -- The same but without using the Request class
 * $name = isset($_GET['name']) ? $_GET['name'] : 'none provided';
 * 
 * // Forcing data to match rules or be ignored (for security, not really validation)
 * // -- ID must be an integer, otherwise set id to null:
 * $id = $request->get->id( null, 'ctype_digit' );
 * @endcode
 *
 * @see Variables
 */
class Request {
    /**
     * The get variables assigned to this Request
     * 
     * Can be accessed (read-only) with <code>$request->get</code>
     * 
     * @var Variables $_get
     */
    private $_get;
    
    /**
     * The post variables assigned to this Request
     * 
     * Can be accessed (read-only) with <code>$request->post</code>
     * 
     * @var Variables $_post
     */
    private $_post;
    
    /**
     * The cookie variables assigned to this Request
     * 
     * Can be accessed (read-only) with <code>$request->cookies</code>
     * 
     * @var Variables $_cookies
     */
    private $_cookies;
    
    /**
     * A set of variables to mimick the behaviour of the superglobal $_REQUEST
     * 
     * Can be altered by the ini settings that effect $_REQUEST.
     * 
     * @var Variables $_request
     */
    private $_request;
    
    /**
     * A temporary array used for generating the $_request variable.
     * 
     * @see _initRequest()
     * @var array $_requestArray
     */
    private $_requestArray = array();
    
    /**
     * Initialise the request values and set the $_request values
     * 
     * It may be a good idea to unset the globals after you've instantiated this
     * class to prevent access to them.
     * 
     * @param array $get
     *      Usually this would be set to the value of \c $_GET (maybe with 
     *      some modifications.
     * @param array $post
     *      Usually this would be set to the value of \c $_POST (maybe with 
     *      some modifications.
     * @param array $cookies 
     *      Usually this would be set to the value of \c $_COOKIES (maybe with 
     *      some modifications.
     */
    public function __construct( array $get = array(), array $post = array(), array $cookies = array() ) {
        $this->_get     = new Variables( $get );
        $this->_post    = new Variables( $post );
        $this->_cookies = new Variables( $cookies );
        
        $this->_initRequest();
    }
    
    /**
     * Shortcut for accessing the $_request Variables
     * 
     * <b>Example</b>
     * @code
     * // the following commands are functionally identical
     * $name = $request->name( 'none specified' );
     * $name = $request->request->name( 'none specified' );
     * @endcode
     * 
     * @param string $name
     * @param array $args
     * @return mixed 
     */
    public function __call( $name, $args ) {
        $arguments = array_pad( $args, 2, null );
        return $this->_request->$name( $arguments[0], $arguments[1] );
    }
    
    /**
     * Get the request params or a specific value
     * 
     * <b>Usage Examples:</b>
     * @code
     * $request = new Request( $_GET, $_POST );
     * 
     * // Get value of 'id' from the POST variables
     * echo $request->post->id;
     * 
     * // Get the value of 'id' from anywhere (using the same variable order as $_REQUEST)
     * echo $request->id;
     * @endcode
     * 
     * @param string $name
     *      If the value is 'get', 'post', 'cookies' or 'request' then the
     *      Variables object will be returned that matches the type.\n\n
     *      If the value is anything else, it is the equivalent of calling
     *      this function on the \c $_request object.
     * @return Variables|string
     *      Either a set of variables or the value of a request variable (see
     *      usage). The value from a html form is always a string.
     */
    public function __get( $name ) {
        switch($name) {
        case 'get':     return $this->_get;
        case 'post':    return $this->_post;
        case 'cookies': return $this->_cookies;
        case 'request': return $this->_request;
        default:
            return $this->_request->$name;
        }
    }
    
    /**
     * Prevent altering of request values
     * 
     * Request values should not be altered.
     * 
     * @throws LogicException if you attempt to set the value of a property (ie
     *         all properties are read-only and this exception will always be
     *         thrown).
     * @param string $name
     * @param mixed $value 
     */
    public function __set( $name, $value ) {
        throw new LogicException("Unable to change value of request parameters. Tried to change $name");
    }
    
    /**
     * Set up the $_request values
     * 
     * Order is defined by the ini settings \c request_order (or \c variables_order
     * if not available).
     */
    private function _initRequest() {
        $order = ini_get('request_order') ?: ini_get('variables_order');
        
        for ( $i=0; $i < strlen($order); $i++ ) {
            switch( $order[$i] ) {
            case 'G':
                $this->_mergeRequest( $this->_get );
                break;
            case 'P':
                $this->_mergeRequest( $this->_post );
                break;
            case 'C':
                $this->_mergeRequest( $this->_cookies );
                break;
            }
        }
        
        $this->_request = new Variables( $this->_requestArray );
    }
    
    /**
     * Merge an associative array of variables into the request array
     * 
     * Existing values are overriden.
     * 
     * @param Variables $request 
     *      Merge the variables from this object into the \c $_requestArray property.
     *      New values will override existing ones.
     */
    private function _mergeRequest( Variables $request ) {
        $this->_requestArray = array_merge($this->_requestArray, $request->getArrayCopy() );
    }
}
