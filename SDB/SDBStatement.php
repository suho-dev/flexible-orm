<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\SDB;
use \ORM\Utilities\Configuration;
/**
 * Mimick the behaviour of ORM_PDOStatement for AmazonSDB
 *
 * Adds SQL support for \c UPDATE, \c INSERT and \c DELETE, which are not natively
 * supported AmazonSDB operations.
 *
 * For more information see \ref utilities_SDB_statement "SDBStatement Class Tutorial".
 *
 * <b>Limitations</b>
 *
 * Only simple SQL statements can be processed by this class, as AmazonSDB is
 * designed to be a simple, scalable datastore. This class avoids some of these
 * shortcommings, but most still exist.
 *
 * Basically the limitations are:
 * - No table (domain) joins
 * - Backticks aren't supported
 * - Subqueries have limited support
 * - All values must be in single quotes
 *
 * <b>Usage</b>
 * \include sdb.sdbstatement.example.php
 *
 *
 * @see SDBFactory, ORMModelSDB, SDBResponse
 */
class SDBStatement implements \ORM\Interfaces\DataStatement {
    /**
     * The maximum size a single attribute is allowed to be (in bytes)
     * Currently the Amazon SimpleDB limit is 1K
     */
    const MAX_ATTRIBUTE_SIZE = 1024;

    /**
     * SQL statement
     *
     * \note Amazon SDB only supports a simple subset of SQL. See class description
     *       for more details.
     *
     * @var string $_queryString
     */
    private $_queryString;

    /**
     * An associative array of placeholders and values
     * @var array $_binds
     */
    private $_binds = array();

    /**
     * @var AmazonSDB $_sdb
     */
    private static $_sdb;

    /**
     * @var SDBResponse $_result
     */
    private $_result;

    /**
     * Used to reduce the number of regular expressions executed in queryType()
     * @var string $_queryType
     */
    private $_queryType;

    /**
     * The last inserted itemName()
     * @var string $_lastInsertID
     */
    private static $_lastInsertID;

    /**
     * Ugly method of implementing the ORM_Model findWith option
     *
     * Set this value to an array of models to fetch for each base model class
     * fetched. It will be reset to a blank array after fetching.
     *
     * @var array $findWith
     */
    public static $findWith = array();

    /**
     * The instance property version of the static $findWith, this is used internally
     * to represent the statically set array of models when the objec is intantiated
     * @var array $_findWith
     */
    private $_findWith;
    
    /**
     * The maximum number of results to return, if null then return all
     * @var int limit
     */
    private $_limit;
    
    /**
     * The number of records to skip (for pagination in combination with LIMIT
     * @var int $_offset
     */
    private $_offset = 0;

    /**
     * Create an SDBStatement object for a query
     *
     * @todo Change this to not be forcing the APAC region
     *
     * @param string $sql
     *      Simple SQL statement. See class description for detailed information
     *      about the types of SQL statements that are available for SDB.
     */
    public function __construct( $sql ) {
        $this->_queryString = $sql;
        self::_InitSDBConnection();

        $this->_findWith    = self::$findWith;
        self::$findWith     = array();
    }

    /**
     * Setup the SDB connection
     *
     *  * Use the configuration value AWS->region to set the region for the SDB
     *  * Use the configuration value AWS->apc_enabled to enable/disable APC
     */
    private static function _InitSDBConnection() {
        if( is_null(self::$_sdb) ){
            self::$_sdb = new \AmazonSDB();
            self::$_sdb->set_response_class( __NAMESPACE__.'\SDBResponse');
            $region = Configuration::AWS()->region;
            self::$_sdb->set_region( $region ?: \AmazonSDB::REGION_APAC_SE1);
            
            if( Configuration::AWS()->apc_enabled ) {
                self::$_sdb->set_cache_config('apc');
            }
        }
    }

    /**
     * Bind a paramater to a placeholder
     *
     * Behaves exactly like PDOStatement::bindParam();
     *
     * @param string $placeholder
     *      The SQL placeholder name (including the colon).
     * @param mixed $variable
     *      The variable (passed by reference) that should be bound to this
     *      placeholder when the statement is executed (or fetched, depending)
     */
    public function bindParam( $placeholder, &$variable ) {
        $this->_binds[$placeholder] = &$variable;
    }

    /**
     * Bind a value to a placeholder in the query string
     * 
     * Behaves exactly like PDOStatement::bindValue(); Escapes the values, so
     * values have to be decoded (see DecodeValue()).
     * 
     * Bind is different for update and create actions
     *
     * @param string $placeholder
     *      The SQL placeholder name (including the colon).
     * @param string $value
     *      Value to place where the placeholder was
     */
    public function bindValue( $placeholder, $value ) {
        $queryType = $this->queryType();
        if( $queryType == 'SELECT' || $queryType == 'DELETE' ) {
            $this->_bindToSQL($placeholder, $value);
        } else {
            $this->_bindToArray($placeholder, $value);
        }
    }

    /**
     * Bind the requested parameter by inserting the escaped value into the query
     *
     * @see _bindValue()
     * @param string $placeholder
     * @param string $value
     */
    private function _bindToSQL( $placeholder, $value ) {
        $sanitizedValue = $this->_sanitizeValue($value);
        
        // REGEX for finding the placeholder
        $regex = ($placeholder == ':itemName()') ? "/:itemName\(\)/": "/$placeholder(?!\w)/";

        // Make a replacement placeholder that can't exist. Can't put the value
        // straight in since REGEX would change the escaping
        $tempPlaceholder    = 'myPlaceholder'.rand(1, getrandmax()).'endPlaceHolder';
        $this->_queryString = preg_replace($regex, $tempPlaceholder, $this->_queryString );

        $this->_queryString = str_replace(
                $tempPlaceholder,
                "'$sanitizedValue'",
                $this->_queryString
        );
    }

    /**
     * For updates and inserts, the parameters to be bound are stored in an
     * array rather than in the query string for simplicity
     *
     * This is entirely redudant if the values were bound as params already
     *
     * @param string $placeholder
     * @param string $value
     */
    private function _bindToArray( $placeholder, $value ) {
        $this->_binds[$placeholder] = $value;
    }

    /**
     * Deal with attributes that are too large for SDB by splitting them up
     *
     * @param array $attributes
     *      An array of key pairs
     * @return array
     *      An array of key pairs where values that are larger than MAX_ATTRIBUTE_SIZE
     *      have been split into multiple values and an index number applied to
     *      their key name (ie "name" would become "name[1]", "name[2]", etc)
     */
    private function _chunkLargeAttributes( array $attributes ) {
        $chunkedAttributes = array();
        
        foreach( $attributes as $field => $value ) {
            if( strlen($field) && strlen($value) > self::MAX_ATTRIBUTE_SIZE ) {
                $chunks = str_split( $value, self::MAX_ATTRIBUTE_SIZE );
                foreach($chunks as $i => $chunk ) {
                    $chunkedAttributes["{$field}[$i]"] = $chunk;
                }
                
            } elseif( strlen($field) ) {
                $chunkedAttributes[$field] = $value;
            }
        }
        
        return $chunkedAttributes;
    }

    /**
     * Sanitize string for use with SDB
     * 
     * @param string $value
     * @return string
     */
    private function _sanitizeValue( $value ) {
        if( $this->queryType() == 'SELECT' ) {
            $sanitizedValue = str_replace("'", "''", $value );
        } else {
            $sanitizedValue = str_replace(
                array('\\', "\0", "\n", "\r", "'", '"', "\x1a"),
                array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"'),
                $value
            );
        }

        return $sanitizedValue;
    }

    /**
     * Un-escape a value stored using SDBStatement
     *
     * @see bindValue()
     * @param string $sanitizedValue
     *      A value that has been stored using bindValue
     * @return string
     *      That should match the originally saved data
     */
    public static function DecodeValue( $sanitizedValue ) {
        return str_replace(
            array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"'),
            array('\\', "\0", "\n", "\r", "'", '"', "\x1a"),
            $sanitizedValue
        );
    }

    /**
     * Bind all of an object's properties to corresponding placeholders
     *
     * See ORM_PDOStatement::bindObject()
     *
     * @param object $object
     *      Object whose properties will be bound to the querystring
     * @param array $params
     *      [optional] array of properties to map. If not provided the functino
     *      will attempt to bind \e all placeholders
     * @return SDBStatement
     */
    public function bindObject( $object, array $params = null ) {
        $me     = $this; //!<- This is needed for the anonymous function, which cannot use $this
        $params = is_null($params) ? $this->placeholders() : $params;

        array_walk( $params, function($param) use( $object, $me ){
            $me->bindValue( ":$param", $object->$param );
        });

        return $this;
    }

    /**
     * Get all the placeholders in the current query string
     * @return array
     *      of placeholder names
     */
    public function placeholders() {
        $pattern = '/(?<=:)([a-zA-Z0-9_]+(?![^\'"]*["\']))/';

        if( strstr($this->_queryString, "'") !== false || strstr($this->_queryString, '"') !== false ) {
            $query = $this->_removeQuotedValues();
        } else {
            $query = $this->_queryString;
        }

        if( preg_match_all( $pattern, $query, $placeholders ) ) {
            return $placeholders[1];
        }

        return array();
    }

    /**
     * Remove the any values in the query string that are between quotes
     * and return the resulting string
     *
     * Used to get the placeholders from the query string and make sure that
     * any placeholders in the data are removed.
     *
     * @return string
     *      An altered version of the query string that has removed all items
     *      in quotes
     */
    private function _removeQuotedValues() {
        $quotedToken = strtok( $this->_queryString, '"\'' );
        $output      = '';
        $count       = 1;

        while( $quotedToken !== false ) {
            if( $count++ % 2 ) {
                $output .= $quotedToken;
            }
            $quotedToken = strtok( '"\'' );
        }

        unset($quotedToken); //!< ensure that the tokeniser is destroyed

        return $output;
    }

    /**
     * Execute the query
     *
     * Designed so that this class mimicks ORM_PDOStatement::execute(). This
     * only actually performs a query for \c INSERT and \c UPDATE operations,
     * for \c SELECT operations it binds the values. To actually perform the
     * select operation, see fetchInto() and fetchAllInto().
     *
     * @todo throw an exception if all placeholders aren't bound yet
     * @todo Define exception for this action
     * 
     * @todo work with non-named placeholders also
     *
     * @param array $values
     *      [optional] Array of values to bind.
     * @return boolean
     *      True on success (only valid for \c INSERT and \c UPDATE operations).
     */
    public function execute( $values = array() ) {
        foreach( $values as $key => $value ) {
            $this->bindValue( $key, $value );
        }

        $this->_bind();
        $queryType = $this->queryType();

        switch( $queryType ) {
            case 'INSERT':
                return $this->_emulateInsert();
            case 'UPDATE':
                return $this->_emulateUpdate();
            case 'DELETE':
                return $this->_emulateDelete();
            case 'SELECT':
                return true;
            default:
                throw new \ORM\Exceptions\ORMPDOException("Unsupported query type $queryType");
        }
    }
    
    /**
     * Get the type of SQL statement
     * 
     * @return string
     *      Either 'DELETE', 'INSERT', 'UPDATE' or 'SELECT'
     */
    public function queryType() {
        if( is_null($this->_queryType) ) {
            preg_match('/^\w+/i', $this->_queryString, $matches );
            $this->_queryType = strtoupper($matches[0]);
        }
        
        return $this->_queryType;
    }

    /**
     * Emulate the SQL INSERT functionality
     *
     * SDB does not natively support \c INSERT, so we emulate it here. Converts
     * the <code>INSERT INTO</code> string into a put_attributes() operation.
     *
     * \note This function will set the $_lastInsertID value.
     *       See SDBStatement::LastInsertId()
     *
     * @throws ORMInsertException if there is an error putting the attributes
     * @return boolean
     *      True on success
     */
    private function _emulateInsert() {
        $this->_simplifyQuery();

        $attributes = $this->_getAttributesFromInsertQuery( $this->_queryString );
        $domain     = $this->_getDomainFromQuery();

        // Remove itemName and store it or generate our own itemName
        if( isset($attributes['itemName()']) ) {
            $itemName = $attributes['itemName()'];
            unset($attributes['itemName()']);
        } else {
            $itemName = $this->_generateItemName( $domain );
        }

        self::$_lastInsertID = $itemName;

        $response = self::$_sdb->put_attributes( $domain, $itemName, $attributes );

        if( !$response->isOK() ) {
            throw new \ORM\Exceptions\ORMInsertException( $response->errorMessage() );
        }
        
        return true; //<! since we got past the exception, response is OK
    }

    /**
     * Convert a SQL INSERT statment to an array for use with AmazonSDB
     * 
     * "Binds" should be used to ensure that this function works
     * 
     * @param string $query
     *      An SQL \c INSERT query (in simple form)
     * @return array
     *      Associative array where keys will be used as field names
     */
    private function _getAttributesFromInsertQuery( $query ) {
        $query = str_replace('itemName()', 'itemName[]', $query, $itemNamePresent);
        preg_match_all('/INTO ([a-z_0-9A-Z]+) \(([^)]+)\) VALUES \((.+) \)/i', $query, $matches );
        $fields = explode( ', ', $matches[2][0] );
        $values = explode( ', ', $matches[3][0] );

        if( $itemNamePresent ) {
            $i = array_search('itemName[]', $fields);
            $fields[$i] = 'itemName()';
        }

        if( $itemNamePresent > 1 ) {
            $i = array_search(':itemName[]', $values);
            if( $i !== false ) $values[$i] = ':itemName()';
        }

        $attributes = array();
        foreach($values as $i => $value ) {
            if( array_key_exists( trim($value), $this->_binds) ) {
                $attributes[$fields[$i]] = $this->_binds[trim($value)];
            } else {
                $attributes[$fields[$i]] = substr($value, 1, -1);
            }
        }

        return $this->_chunkLargeAttributes( $attributes );
    }

    /**
     * Convert a SQL UPDATE statment to an array for use with AmazonSDB
     *
     * @return array
     *      Associative array where keys will be used as field names. Large values
     *      will be split over mutiple keys
     */
    private function _getAttributesFromUpdateQuery() {
        // This could probably be done with one expression, but I couldn't work
        // it out today, so I did it as two.
        preg_match('/^UPDATE ([a-z_0-9A-Z]+) SET (.+) WHERE/i', $this->_queryString, $matches );
        $setString = $matches[2];

        $regex = '/[a-z_]+ = (\'.*?(?<!\\\)\'|:[^, )]+)/i';
        preg_match_all($regex, $setString, $matches);
        $set = $matches[0];
        
        $attributes = array();
        foreach($set as $pair) {
            list( $field, $value ) = explode( ' = ', trim($pair), 2 );
            
            if( array_key_exists(trim($value), $this->_binds) ) {
                $attributes[$field] = $this->_binds[trim($value)];
            } else {
                $attributes[$field] = substr($value, 1, -1);
            }
        }

        return $attributes;
    }

    /**
     * Get the domain name (the table name in normal SQL) from the current
     * queryString
     *
     * \note Currently only works for INSERT and UPDATE statements (because it
     *       is not needed for SELECT or DELETE
     *
     * @return string
     */
    private function _getDomainFromQuery() {
        if( !preg_match('/^UPDATE ([a-z_0-9A-Z]+) /i', $this->_queryString, $matches ) ) {
            preg_match('/INTO ([a-z_0-9A-Z]+) /i', $this->_queryString, $matches );
        }

        return $matches[1];
    }

    /**
     * Get the itemName from the query
     *
     * @return string|false
     *      The itemName value or false if it is not discoverable from the current
     *      query string
     */
    private function _getItemNameFromQuery() {
        if( preg_match('/itemName\(\) = \'(.+)\'/i', $this->_queryString, $matches ) ) {
            return $matches[1];
        } elseif(isset($this->_binds[':itemName()'])) { 
            return $this->_binds[':itemName()'];
        } else {
            return false;
        }
    }

    /**
     * Emulate the SQL UPDATE functionality
     *
     * SDB does not natively support \c UPDATE, so we emulate it here. Converts
     * the \c UPDATE string into a put_attributes() operation.
     *
     * @return boolean
     *      True on success
     */
    private function _emulateUpdate() {
        $this->_simplifyQuery();

        $domain     = $this->_getDomainFromQuery();
        $itemName   = $this->_getItemNameFromQuery();
        $attributes = $this->_getAttributesFromUpdateQuery();
        $result     = self::$_sdb->put_attributes( $domain, $itemName, $attributes, true );

        if( !$result->isOK() ) {
            throw new \ORM\Exceptions\ORMUpdateException( $result->errorMessage() );
        }
        
        return $result->isOK();
    }

    /**
     * Emulate the SQL DELETE functionality
     *
     * SDB does not natively support \c DELETE, so we emulate it here. Converts
     * the \c DELETE string into a delete_attributes() operation.
     *
     * \note Only has very primative support for deleting items using constraints
     *       other than itemName (primary key). Will only delete a maximum of 25
     *       results at a time.
     *
     * @return boolean
     *      True on success
     */
    private function _emulateDelete() {
        $this->_simplifyQuery();
        
        if( preg_match( '/DELETE FROM ([a-z_0-9A-Z]+) WHERE itemName\(\) = \'([^\']*)/i', $this->_queryString, $matches ) ){
            // There is only going to be one item to delete
            return self::$_sdb->delete_attributes( $matches[1], $matches[2] )->isOK();
        } else {
            // May be many returned items, we will find them then delete each one
            preg_match( '/^DELETE FROM ([a-z_0-9A-Z]+) WHERE (.*)$/', $this->_queryString, $matches );
            $sql = "SELECT * FROM {$matches[1]} WHERE {$matches[2]} LIMIT 25";

            $items = self::$_sdb->select( $sql );
            self::$_sdb->batch_delete_attributes( $matches[1], $items->itemNames() );
        }
    }

    /**
     * Generate a unique itemName for a specific domain
     *
     * There is no \c AUTOINCREMENT option for Amazon SDB, so we implement this
     * simple random name generator. It checks to ensure it hasn't randomly
     * selected an existing name.
     *
     * \note This would not be very good for extremely large tables, as the chance
     *      of selecting existing names increases with table size.
     *
     * @param string $domain
     *      The domain (or tableName) of the class we wish to automatically
     *      generate an itemName for
     * @return string
     */
    private function _generateItemName( $domain ) {
        $count = 0;
        do {
            $itemName = mt_rand( 1, mt_getrandmax() );
        } while( count(self::$_sdb->get_attributes($domain, $itemName)) && ++$count < 20 );

        return $itemName;
    }

    /**
     * Fetch results into the specified class
     *
     * Actually peform the lookup and return the result as an object of the
     * specified class. Behaves like ORM_PDOStatement::fetchInto().
     *
     * \note This function is effected by the ORMModelSDB::EnforceReadConsistency()
     *      setting.
     *
     * @throws Exceptions\ORMFetchIntoClassNotFoundException
     *
     * @param string $className
     *      Object type to fetch into
     * @return object
     *      Object of type $className
     */
    public function fetchInto( $className ) {
        if( !class_exists($className) ) {
            throw new Exceptions\ORMFetchIntoClassNotFoundException("Unknown class $className requested");
        }

        $this->_simplifyQuery($className);
        $this->_result = self::$_sdb->select( $this->_queryString, array(
            'ConsistentRead' => $className::EnforceReadConsistency() )
        );

        if( !$this->_result->isOK() ) {
            throw new \ORM\Exceptions\ORMFetchIntoException(
                $this->_result->errorMessage()
            );
        } else if( count($this->_result) ) {
            $keys   = $this->_result->itemNames();
            $object = new $className;
            $object->id( $keys[0] );

            $object->setValues( $this->_result[$keys[0]] );
            $this->_fetchWith($object);

            return $object;
        }

        return false;
    }

    /**
     * Add requested foreign key objects to this object
     *
     * All fetch class options must be classes that extend ORM_Model
     *
     * @param ORMModelSDB $object
     */
    private function _fetchWith( ORMModelSDB &$object ) {
        $className = get_class($object);
        
        foreach( $this->_findWith as $fetchClassName ) {
            if( !is_subclass_of($fetchClassName, '\ORM\ORM_Model') ) {
                throw new \ORM\Exceptions\ORMFetchIntoException(
                    "Find with class '$fetchClassName' does not extend class ORM_Model"
                );
            }

            $key            = $className::ForeignKey( $fetchClassName );
            $baseClassName  = $fetchClassName::ClassName();

            $object->$baseClassName = $fetchClassName::Find($object->$key);
        }
    }

    /**
     * Alter the querystring so that it works with Amazon SDB
     *
     * Remove backticks and aliases. Removes offset so that the inbuilt limit
     * option works.
     *
     * @param string $className
     *      [optional] The ORM_Model class sometimes adds the model name to the
     *      SQL query (for joins). Since SDB can't do joins and they make altering
     *      the SQL more difficult, specify the classname to have them removed
     */
    private function _simplifyQuery( $className = '' ) {
        $baseClass  = basename( str_replace('\\', '//', $className) );
        $replace    = array("`$baseClass`.", "AS `$baseClass`", "`");
        
        if( preg_match('/LIMIT (\d+), (\d+) $/', $this->_queryString, $matches) ) {
            $replace[]      = $matches[0];
            $this->_offset  = (int)$matches[1];
            $this->_limit   = (int)$matches[2];
        }
        
        $this->_queryString = str_replace( $replace, '', $this->_queryString );
        
        if( !is_null($this->_limit) ) {
            $this->_queryString .= " LIMIT {$this->_limit}";
        }
    }

    /**
     * Fetch results into the specified class objects
     *
     * Actually peform the lookup and return the result as a collection of objects
     * of the specified class. Behaves like ORM_PDOStatement::fetchAllInto().
     *
     * \note This function is effected by the ORMModelSDB::EnforceReadConsistency()
     *      setting.
     *
     * @throws Exceptions\ORMFetchIntoClassNotFoundException for invalid classname
     * @throws Exceptions\ORMFetchIntoException for AmazonSDB errors
     *
     * @param string $className
     *      Object type to fetch into
     * @return ModelCollection
     *      Collection of objects of type $className
     */
    public function fetchAllInto( $className ) {
        if( !class_exists($className) ) {
            throw new Exceptions\ORMFetchIntoClassNotFoundException("Unknown class $className requested");
        }

        $this->_simplifyQuery($className);
        $this->_result = $this->_executeFetchQuery($className::EnforceReadConsistency());

        $collection = new \ORM\ModelCollection();
        
        if( !$this->_result->isOK() ) {
            throw new \ORM\Exceptions\ORMFetchIntoException(
                $this->_result->errorMessage()
            );
        } elseif( count($this->_result) ) {
            foreach( $this->_result as $key => $attributes ) {
                $object = new $className;
                $object->id( $key );
                
                $object->setValues( $attributes );
                $this->_fetchWith($object);

                $collection[] = $object;
            }
        }

        return $collection;
    }
    
    /**
     * Execute a fetchAll query and deal with offsets and limits
     * 
     * Called by FetchAllInto
     * 
     * @param string $consistentRead
     * @return SDBResponse
     */
    private function _executeFetchQuery( $consistentRead ) {
        $optionsArray = array(
            'ConsistentRead' => $consistentRead
        );
        
        // If we already know some tokens, don't start from the begining (for speed)
        $initialOffset = 0;
        if( $this->_offset > 0 ) {
            list( $initialOffset, $token ) = NextTokenCache::GetNearestToken(
                $this->_queryString, 
                $this->_limit, 
                $this->_offset
            );
            
            if( $token ) {
                $optionsArray['NextToken'] = $token;
            }
        }
        
        return self::$_sdb->select(
                $this->_queryString, $optionsArray 
        )->getAll($consistentRead == 'true', $this->_limit, $this->_offset, $initialOffset );
    }

    /**
     * Bind all the placeholders
     *
     * Should be called before executing the fetch* functions
     */
    private function _bind() {
        foreach( $this->_binds as $key => $value ) {
            $this->bindValue( $key, $value );
        }
    }

    /**
     * The queryString value is output when this class is cast to string
     *
     * @return string
     *      The SQL with bound placeholders if they have been replaced already
     */
    public function __toString() {
        return $this->_queryString;
    }

    /**
     * Get the autogenerated itemName value from the last insert operation
     *
     * @see SDBStatement::_emulateInsert()
     * @return string|null
     *      If there has been an \c INSERT statement executed, this will return
     *      the autogenerated id.
     */
    public static function LastInsertId() {
        return self::$_lastInsertID;
    }

    /**
     * Perform a SELECT statement directly on the SDB service
     * 
     * @param string $queryString
     *      Amazon SDB compatible SELECT querystring
     * @param boolean $consistent
     *      Should read consistency be enforced
     * @param string $nextToken
     *      Next token for continuing requests
     * @return SDBResponse
     */
    public static function Query( $queryString, $consistentRead = false, $nextToken = null ) {
        self::_InitSDBConnection();
        return self::$_sdb->select( $queryString, array(
            'ConsistentRead' => $consistentRead ? 'true' : 'false',
            'NextToken'      => $nextToken
        ));
    }

    /**
     * Get the active SDB connection
     * @return AmazonSDB
     */
    public static function GetSDBConnection() {
        self::_InitSDBConnection();
        return self::$_sdb;
    }
}
?>

