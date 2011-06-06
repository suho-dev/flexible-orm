<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\SDB;

/**
 * An model class where the backend is the Amazon simpleDB service.
 * 
 * Almost identical to the ORM_Model class with a few exceptions:
 *  # Primary key is always itemName()
 *  # Foreign keys (ie FindWith requests) require extra lookups, as there are no
 * joins in SimpleDB
 *  # Requests that return a lot of results or use large offset values may be
 * slow
 * 
 * If you are using Amazon Web Services, you might want to consider using the
 * SDBSessionHandler also.
 *
 * @see ORM_Model, SDBSessionHandler
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
     * @return boolean
     */
    public static function EnforceReadConsistency() {
        return defined("static::ENFORCE_READ_CONSISTENCY") ? static::ENFORCE_READ_CONSISTENCY : true;
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

                $this->$field = $sanitizedValue;
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

    /**
     * Override the ORM_Model::_BuildSQLFindWith() to work with SDB
     *
     * Uses the SDBStatment::$findWith static variable, which is not a very nice
     * way to do this.
     *
     * @param string $table
     * @param array|string $findWith
     * @return string
     *      SQL Query
     */
    protected static function _BuildSQLFindWith( $table, $findWith ) {
        SDBStatement::$findWith = (array)$findWith;
        $className  = static::ClassName();
        
        return "SELECT `$className`.* FROM `$table` AS `$className` ";
    }
}
?>
