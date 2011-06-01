<?php
class Car extends \ORM\ORM_Model {
    public $colour;
    public $doors;
    public $manufacturer;
    
    /*
     * Check if this Car object is valid
     * 
     * Rules:
     *   Doors must be  > 0
     *   Manufacturer must be set
     */
    public function valid() {
        if( $this->doors <= 0 ) {
            $this->validationError('doors', 'must be greater than zero');
        }
        if( strlen($this->manufacturer) == 0 ) {
            $this->validationError('manufacturer', 'must be set');
        }
        
        // Model is valid if there are no error messages
        return count( $this->errorMessages() ) === 0;
    }
}