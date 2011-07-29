<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM;

/**
 * An abstract class which defines the actions of an
 * ORM-like object.
 * 
 * Allows classes to be subclassed to behave like the basics of ORM_Model, but
 * without the back-end database connection. To create an Alternative to ORM_Model
 * that can be dropped in-place, use the ORM_Interface.
 * 
 */
abstract class ORM_Core {
    /**
     * Array of key > values of fields with errors.
     * 
     * @see errorMessages()
     * @var array $_errorMessages
     */
    protected 			$_errorMessages = array();

    /**
     * The values this object held originally as an associative array
     *
     * @var array $_originalValues
     */
    protected  			$_originalValues = array();

    /**
     * Instantiate the object with supplied default values
     *
     * Also populates the _originalValues array
     *
     * @param array $values
     *      Key->Value pairs of initial values
     */
    public function __construct( $values = array() ) {
        $this->setValues( $values );
    }

    /**
     * Array of all the attributes of this object
     *
     * @return array
     * @see values()
     */
    public function attributes() {
        return array_keys( $this->_originalValues );
    }

    /**
     * Get an associative array of all the values of the public attributes of this object
     *
     * Keys will be attribute names
     * 
     * @return array
     * @see attributes()
     */
    public function values() {
        $properties = $this->attributes();
        $values     = array();
        
        foreach ( $properties as $property ) {
            $values[$property] = property_exists( $this, $property ) ? $this->$property : null;
        }

        return $values;
    }

    /**
     * Return array of all error messages
     * 
     * If there are no error messages, this will return an empty array.
     * 
     * The array will be associative, with the keys being object properties and
     * the values being the actual error message.
     * 
     * <b>Usage</b>
     * @code
     * if ( !$car->save() ) {
     *     // Validation issue, echo each error message
     *     foreach ( $car->errorMessages() as $property => $message ) { 
     *         echo "Error with the $property field - $message \n";
     *     }
     * 
     *     // Log all the errors to a log file
     *     error_log( $car->errorMessagesString() );
     * }
     * @endcode
     * 
     * \note This is only populated if you use validationError(). See 
     *       \ref validation "Model Validation" for more information.
     * 
     * @see errorMessage()
     * @return array
     */
    public function errorMessages() {
        if ( !is_array($this->_errorMessages) ) {
            $this->_errorMessages = array();
        }
        return $this->_errorMessages;
    }

    /**
     * Return a string of error messages
     * 
     * Each error is listed comma seperated as "'PropertyName' ErrorMessage"
     *
     * See \ref validation "Model Validation" for more information.
     * 
     * @see errorMessages()
     * @return string
     */
    public function errorMessagesString() {
        $msgs = array();
        foreach ( $this->_errorMessages as $key => $value ) {
            $msgs[] = "'$key' $value";
        }

        return implode( ', ', $msgs );
    }

    /**
     * Return the error message for a specific field
     *
     * Will return false if no error exists for the selected field
     *
     * <b>Usage</b>
     * @code
     * if ( $error_msg = $person->errorMessage( 'name' ) ) {
     *   echo 'There is an error with the name field. Details: '. $error_msg;
     * } else {
     *   echo "There was no error on the name field";
     * }
     * @endcode
     * 
     * See \ref validation "Model Validation" for more information.
     *
     * @return string
     * @param string $field
     */
    public function errorMessage( $field ) {
        return isset($this->_errorMessages[$field]) ? $this->_errorMessages[$field] : false;
    }

    /**
     * Add an error to the array of error messages
     *
     * <b>Usage</b>
     *
     * @code
     * // Usually within a classes valid() function...
     * if ( strlen( $this->name ) < 5 ) {
     *   $this->validationError( 'name', 'Must be at least 5 characters long' );
     * }
     *
     * echo $this->errorMessage( 'name' );
     * // will print 'Must be at least 5 characters long' if name was less than 5 chars long
     * @endcode
     * 
     * See \ref validation "Model Validation" for more information.
     *
     * @param string $field
     * @param string $message
     * @see base_model::errorMessage(), base_model::valid()
     */
    public function validationError( $field, $message ) {
        if ( !is_array($this->_errorMessages) ) {
            $this->_errorMessages = array();
        }
        $this->_errorMessages[$field] = $message;
    }

    /**
     * Clear all existing validation errors
     * 
     * See \ref validation "Model Validation" for more information.
     */
    public function clearValidationErrors() {
        $this->_errorMessages = array();
    }

    /**
     * Get array of field names that have changed
     *
     * <b>Usage:</b>
     * @code
     * $person = Person::find('last');
     * $person->name = 'Different Name';
     *
     * print_r( $person->changed_fields() );
     *
     * // Outputs--> array( 'name' )
     * @endcode
     *
     * @return array
     */
    public function changedFields() {
        $changed = array();
        $original_values = count($this->_originalValues) ? $this->_originalValues : array();

        $attributes = $this->values();

        foreach ( $attributes as $key => $value ) {
            if ( !isset($original_values[$key]) || $original_values[$key] != $value ) {
                $changed[] = $key;
            }
        }
        return $changed;
    }

    /**
     * Get the originally fetched value of a property
     * 
     * @param string $property
     *      Name of the property
     * @return mixed
     *      The original value of the property or NULL if there is none
     */
    public function originalValue( $property ) {
        return array_key_exists( $property, $this->_originalValues ) ? $this->_originalValues[$property] : null;
    }

    /**
     * Get the original values of this object
     * @return array
     */
    public function originalValues() {
        return $this->_originalValues;
    }

    /**
     * Set an individual properties "original" value
     * 
     * @param string $property
     *      The name of the attribute to set
     * @param mixed $value
     *      The value to set
     */
    public function setOriginalValue( $property, $value ) {
        $this->_originalValues[$property] = $value;
    }

    /**
     * (re)sets the all the attribute values for this object
     *
     * @param array $values
     *      Associative array of field names and their values
     */
    public function setValues( array $values = null ) {
        if ( !is_null($values) ) {
            foreach ( $values as $field => $value ) {
                $this->$field = $value;
            }
        }
    }
}