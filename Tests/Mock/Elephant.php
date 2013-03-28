<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace FlexibleORMTests\Mock;
use Suho\FlexibleOrm\ORM_Model;
/**
 * Description of Elephant
 *
 * @author jarrod.swift
 */
class Elephant extends ORM_Model {
    const TABLE         = 'my_elephants';
    const PRIMARY_KEY   = 'name';

    public $weight;

    /**
     * Override this method to perform an action \e before an object is saved to
     * the database (creating and updating).
     */
    public function beforeSave() {
        $GLOBALS['beforeSave'] = true;
    }

    /**
     * Override this method to perform an action \e before an existing object is
     * updated in the database.
     */
    public function beforeUpdate() {
        $GLOBALS['beforeUpdate'] = true;
    }

    /**
     * Override this method to perform an action \e before a new object is
     * created in the database.
     */
    public function beforeCreate() {
        $GLOBALS['beforeCreate'] = true;
    }

    /**
     * Override this method to perform an \e after before a new object is
     * created in the database. Only called on success. The object will have its
     * $_id field populated.
     */
    public function afterCreate() {
        $GLOBALS['afterCreate'] = true;
    }

    /**
     * Override this method to perform an action \e after an existing object is
     * updated in the database. Only called on success.
     */
    public function afterUpdate() {
        $GLOBALS['afterUpdate'] = true;
    }

    /**
     * Override this method to perform an action \e after an object is saved to
     * the database (creating and updating). Only called on success.
     */
    public function afterSave() {
        $GLOBALS['afterSave'] = true;
    }


    /**
     * Override the default valid with some rules, then call the default valid()
     * function to check validity
     * 
     * @return boolean
     */
    public function valid() {
        $this->clearValidationErrors();

        if ( $this->weight <= 0 ) {
            $this->validationError('weight', 'must be more than zero');
        }
        
        return parent::valid();
    }
}

?>
