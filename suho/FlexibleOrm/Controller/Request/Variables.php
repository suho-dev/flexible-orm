<?php
/**
 * @file
 * @author jarrod.swift
 */
/**
 * HTTP request handling
 */
namespace Suho\FlexibleOrm\Controller\Request;

/**
 * Read-only variable access with filters and defaults
 * 
 * Given an array of values, allows access (read-only) as properties. Can
 * be looped through and used as an array.
 * 
 * Default values can be specified if the variable is not set or doesn't meet
 * the criteria of the rule (see matchesRule()).
 * 
 * Used by the Request class to represent each set of request variables.
 */
class Variables extends \ArrayObject {
    /**
     * Get a variable value as a property
     * 
     * <b>Usage Example</b>
     * @code
     * $variables = new Variables(array(
     *      'id' => 23, 'name' => 'jarrod', 'age' => '31 years'
     * ));
     * 
     * $id = $variables->id;
     * 
     * $variables->id = $id * 10; // Throws exception
     * @endcode
     * 
     * @param string $name
     *      The variable name to look for
     * @return mixed
     *      The value of the specified variable or \c null if it doesn't exist.
     */
    public function __get( $name ) {
        if ( array_key_exists($name, $this) ) {
            return $this[$name];
        }
        
        return null;
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
        throw new \LogicException("Unable to change value of request parameters. Tried to change $name");
    }
    
    /**
     * Variables can be accessed as methods also
     *
     * @see _getParam()
     * @param string $name
     *      The variable name to retrieve
     * @param array $args
     *      Zero to 2 arguments, the first is used as $default and the second
     *      as $rule when calling _getParam()
     * @return mixed
     *      The output of _getParam() 
     */
    public function __call( $name, $args ) {
        $arguments = array_pad( $args, 2, null );
        
        return $this->_getParam( $name, $arguments[0], $arguments[1] );
    }
    
    /**
     * Test a value against a rule
     * 
     * Rules can either be functions names that take the value as an argument or a
     * regular expression pattern. It may use inbuilt (such as \c ctype_digit
     * ), user defined or anonymous functions.
     * 
     * \n\n<b>Example: Function</b>
     * @code
     * $vars = new Variables();
     * 
     * // User Function
     * function myTest( $value ) {
     *      return $value > 32;
     * }
     * 
     * $test = $vars->matchesRule( 'myTest', 54 ); // true as 54 > 32
     * 
     * // Anonymous function (closure)
     * $test = $vars->matchesRule(function( $value ){
     *      return $value < 32;
     * }, 54 ); // false as 54 > 32
     * 
     * // Built-in function
     * $test = $vars->matchesRule( 'ctype_alpha', 'hello' ); // true
     * $test = $vars->matchesRule( 'ctype_digit', 'hello' ); // false
     * @endcode
     * 
     * 
     * \n\n<b>Example: REGEX</b>
     * @code
     * $vars = new Variables();
     * 
     * $matches = $vars->matchesRule( '/^j\w+$/', 'jarrod' ); // true
     * $matches = $vars->matchesRule( '/^a\w+$/', 'jarrod' ); // false
     * @endcode
     * 
     * @param string $rule
     *      The rule to test. Either a function name or REGEX pattern.
     * @param mixed $value
     *      The value to test
     * @return boolean
     *      True if the rule is passed, false otherwise.
     */
    public function matchesRule( $rule, $value ) {
        if ( function_exists($rule) ) {
            return $rule($value);
        } else {
            return preg_match( $rule, $value );
        }
    }
    
    /**
     * Retrieve a parameter optionally using a default and a rule
     * 
     * <b>Usage Examples</b>
     * @code
     * $vars = new Variables(array('id' => 1234));
     *
     * // No rule with a default
     * $id   = $vars->id( 20 ); // id will be 1234
     * $name = $vars->name( 'not specified' ); // name will be 'not specified'
     *
     * // Rule and a default
     * $id  = $vars->id( 20, 'ctype_digit' ); // will be 1234 since it matches the rule
     * $id  = $vars->id( 20, 'ctype_alpha' ); // will be 20 since it doesn't match the rule
     * @endcode
     * 
     * @see matchesRule() for more examples of rules.
     * 
     * @param string $name
     *      The variable name to attempt to retrieve
     * @param mixed $default
     *      [optional] The default value to return if the variable is not found
     *      or does not meet the specified rule. Defaults to \c null.
     * @param string $rule
     *      [optional] The rule to match the value against. See matchesRule() for
     *      details on rules. Defaults to \c null, meaning no rule to match against
     * @return mixed
     *      Either the value of the \c $name variable or the value of \c $default.
     */
    private function _getParam( $name, $default = null, $rule = null ) {
        if (array_key_exists( $name, $this )
            && ( is_null($rule) || $this->matchesRule( $rule, $this[$name] ) )
        ) {
            return $this[$name];
        }
        
        return $default;
    }
}
