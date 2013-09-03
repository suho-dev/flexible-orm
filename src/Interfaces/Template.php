<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ORM\Interfaces;

/**
 * Defines the interface for templating classes for use with controller
 * 
 * 
 * @see BaseController, SmartyTemplate
 * @author jarrod.swift
 */
interface Template {
    /**
     * Assign a variable to a template
     * 
     * To make values available in a template, they must be assigned. This method
     * is identical to Smarty::assign() when used with 2 arguments.
     * 
     * @param string $name
     *      The name of the variable being assigned to the template
     * @param mixed $value
     *      The value of the variable being assigned to the template
     */
    public function assign( $name, $value );

    /**
     * Fetch the output of a template
     * 
     * Essentially running a template to get the output string. For example, using
     * SmartyTemplate the following is functionally equivalent:
     * 
     * @code
     * // Fetch
     * echo $smarty->display( 'myTemplate' );
     * 
     * // Dislay
     * $smarty->display( 'myTemplate.tpl' );
     * @endcode
     * 
     * @see BaseController::performAction()
     * @param string $template
     *      The name of the template to fetch
     * @return string
     *      The output of the template.
     */
    public function fetch( $template );
    
    /**
     * Test whether or not a template exists
     * 
     * @param string $template
     *      The name of the template to check for
     * @return boolean
     *      True if it does exist
     */
    public function templateExists( $template );

    /**
     * Get an array of headers that should be set before returning the action content.
     * This function will be called AFTER fetch
     *
     * @param type $template
     *      The name of the template to fetch
     * @return array
     *      An array of strings that would be used with the header() function.
     */
    public function getRawHTTPHeaders($template);

}