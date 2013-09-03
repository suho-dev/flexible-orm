<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Controller;
use \Smarty;

/**
 * Class wrapper for Smarty to implement the Template interface
 *
 * Requires that the Smarty class has been loaded, or will be auto-loaded (obviously).
 * 
 * @see BaseController
 */
class SmartyTemplate extends Smarty implements \ORM\Interfaces\Template {
    /**
     * The extension to add to the end of the template name
     * 
     * Does not include the trailing period, i.e. the default setting 'tpl' 
     * means that '.tpl' is appended to the template name in fetch()
     * 
     * @see fetch()
     * @var string $templateExtension
     */
    public $templateExtension = 'tpl';

    /**
     * An array of rawHTTP Headers
     * @var array
     */
    protected $_rawHTTPHeaders = array();

    /**
     * Override the Smarty fetch() method to allow for automatic file name 
     * extensions
     * 
     * @see $templateExtension, templateExists()
     * @param string $template
     *      Template name (without extension)
     * @return string
     *      Template output
     */
    public function fetch( $template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false ) {
        return parent::fetch( "$template.{$this->templateExtension}" );
    }
    
    /**
     * Override the Smarty templateExists() method to allow for automatic file name 
     * extensions
     * 
     * @see $templateExtension, fetch()
     * @param string $template
     *      Template name (without extension)
     * @return boolean
     */
    public function templateExists( $template ) {
        return parent::templateExists( "$template.{$this->templateExtension}" );
    }

    /**
     * Get an array of headers that should be set before returning the action content.
     * This function will be called AFTER fetch.
     *
     * @param type $template
     * @return type
     */
    public function getRawHTTPHeaders($template) {
        return $this->_rawHTTPHeaders;
    }

}
