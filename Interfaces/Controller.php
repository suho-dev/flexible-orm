<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Interfaces;
use ORM\Controller\Request;

/**
 * Interface that must be implemented by controller classes that you wish to use
 * with ControllerFactory and ControllerRegister.
 * 
 * @todo decouple from Request class
 * 
 * @author jarrodswift
 */
interface Controller {
    /**
     * Create a new Controller with a Request and Template object
     *
     * @param Request $request
     *      [optional] The request paramaters for the controller to use
     * @param Template $template
     *      [optional] The template class to use to prepare output
     */
    public function __construct( Request $request = null, Template $template = null );
    
    /**
     * Perform an action with the controller
     * 
     * Either uses a provided action or the default action of the controller (which
     * may be to use the Request parameters).
     * 
     * @param string $action
     *      [optional] Action name to perform
     * @return string
     *      The output of the templating for this action
     */
    public function performAction( $action = null );
}