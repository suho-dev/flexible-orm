<?php
class Table extends \ORM\ORM_Model {
    // The table name
    public $name;
    
    // 2 dimensional array of table data.
    // When saved, this array is serialised, then unserialised when retrieved
    public $data = array();
    
    /*
     * Serialise the table data
     */
    public function beforeSave() {
        $this->data = serialize( $this->data );
    }
    
    /*
     * Unserialise the table data
     */
    public function afterGet() {
        $this->data = unserialize( $this->data );
    }
}