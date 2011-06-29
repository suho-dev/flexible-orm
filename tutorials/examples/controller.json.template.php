<?php
/*
 * Rather trivial template class that outputs all assigned variables as JSON data 
 * 
 * Individual template names do not alter the output for this class.
 */
class JsonTemplate implements \ORM\Interfaces\Template {
    private $_assignedVariables = array();
    
    /*
     * Assign a variable to a template
     * 
     * @param string $name
     *      The name of the variable being assigned to the template
     * @param mixed $value
     *      The value of the variable being assigned to the template
     */
    public function assign( $name, $value ) {
        $this->_assignedVariables[$name] = $value;
    }

    /*
     * Fetch the output of a template
     * 
     * Template names don't effect this class at all.
     * 
     * @param string $template
     *      The name of the template to fetch (ignored)
     * @return string
     *      The output of the template, which will be a JSON encoded string
     *      of assigned variables.
     */
    public function fetch( $template ) {
        return json_encode( $this->_assignedVariables );
    }
    
    /*
     * Test whether or not a template exists
     * 
     * Since template names do not effect this class, templateExists always
     * returns \c true
     * 
     * @param string $template
     *      The name of the template to check for
     * @return boolean
     *      True if it does exist
     */
    public function templateExists( $template ) {
        return true;
    }
}