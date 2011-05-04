<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\SDB;
/**
 * Description of ORMModelSDB
 *
 */
class ORMModelSDB extends \ORM\ORM_Model {
    /**
     * Set the datafactor class to SDBFactory
     */
    const DATAFACTORY = 'SDB\SDBFactory';

    /**
     * The primary key for an SDB object is always 'itemName()'
     */
    const PRIMARY_KEY = 'itemName()';

    /**
     * Get an array listing all the fields for this object
     *
     * For SDB models, all public properties are stored.
     *
     * @return array
     *      An array of field names representing each field
     */
    public static function DescribeTable() {
        $className  = get_called_class();
        $item       = new $className;

        // get_object_vars() must be called from outside the object scope
        // to ensure it only gets the publicly accessible attributes
        $publicPropertiesFunction = (function() use( $item ) {
            $vars = get_object_vars($item);
//            unset($vars['itemName()']);

            return array_keys($vars);
        });

        return $publicPropertiesFunction();
    }

    /**
     * Check if this model should enforce SDB read consistency
     *
     * By default, reads are inconsistent in AmazonSDB. This is faster for
     * distributed systems, but it means that reads shortly after writes may
     * not return the updated values. By enforcing read consistency, all reads
     * wait for the updates to be completed.
     *
     * To enforce read consistency, simply set a class constant
     * @code
     * const ENFORCE_READ_CONSISTENCY = true
     * @endcode
     *
     * @return string
     *      Either 'true' or 'false' for use with AmazonSDB
     */
    public static function EnforceReadConsistency() {
        $enforce = defined("static::ENFORCE_READ_CONSISTENCY") ? static::ENFORCE_READ_CONSISTENCY : true;

        return $enforce ? 'true' : 'false';
    }

    /**
     * Create the SDB domain for this model
     *
     * This action is idempotent (can be called repeatedly with no ill-effect)
     * though it should not be called every time the script is run, since it
     * may be slightly slower.
     *
     * @return string
     *      Returns the domain name created (will be the same as calling
     *      ORM_Model::TableName())
     */
    public static function CreateDomain() {
        $domain_name = static::TableName();
        SDBStatement::GetSDBConnection()->create_domain($domain_name);

        return $domain_name;
    }

    /**
     * (re)sets the all the attribute values for this object
     *
     * Overrides the core version to allow decoding of manually escaped values
     *
     * @param array $values
     *      Associative array of field names and their values
     */
    public function setValues( array $values = null) {
        if( !is_null($values) ) {
            foreach( $values as $field => $sanitizedValue ) {
                $decodedValue = SDBStatement::DecodeValue($sanitizedValue);

                $this->$field = $decodedValue;
            }
        }
    }

    /**
     * Rtturn the attributes of this object that will be stored
     * @return array
     */
    public function attributes() {
        return empty($this->_originalValues) ? self::DescribeTable() : array_keys($this->_originalValues);
    }
}
?>
