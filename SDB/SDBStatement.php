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
class SDBStatement extends SDBWrapper implements \ORM\Interfaces\DataStatement {
    /**
     * Fetch as both associative and indexed array
     * @see fetch(), fetchAll()
     */
    const FETCH_BOTH    = 1;
    
    /**
     * Fetch as associative array
     * @see fetch(), fetchAll()
     */
    const FETCH_ASSOC   = 2;
    
    /**
     * Fetch as indexed array
     * @see fetch(), fetchAll()
     */
    const FETCH_ARRAY   = 3;

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
     * A numerically index array of values to bind in order to anonymous placeholders
     * @var type $_anonymousBinds
     */
    private $_anonymousBinds = array();

    /**
     * @var SDBResponse $_result
     */
    private $_result;
    
    /**
     * Used to store the results.
     * @see fetch(), fetchAll()
     * @var array $_items
     */
    private $_items;

    /**
     * Used to reduce the number of regular expressions executed in queryType()
     * @var string $_queryType
     */
    private $_queryType;
    
    /**
     * Set to true to force consistent reads
     * @var boolean $_consistentRead
     */
    private $_consistentRead = false;

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
     * @var int $_limit
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
     * @param string $sql
     *      Simple SQL statement. See class description for detailed information
     *      about the types of SQL statements that are available for SDB.
     */
    public function __construct( $sql ) {
        $this->_queryString = $sql;
        $this->_findWith    = self::$findWith;
        self::$findWith     = array();
        
        parent::__construct();
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
        if ( $queryType == 'SELECT' || $queryType == 'DELETE' ) {
            $this->_bindToSQL($placeholder, $value);
        } else {
            $this->_bindToArray($placeholder, $value);
        }
    }
    
    /**
     * Bind a value to an anonymous placeholder
     * 
     * 
     * @param string $value 
     *      The value to bind to the first anonymous placeholder
     */
    private function _bindAnonymousValue( $value ) {
        $this->_anonymousBinds[] = $value;
        
        if ( $this->queryType() == 'SELECT' ) {
            list($unquoted, $quoted) = $this->_extractQuotedValues();
            $sanitizedValue = $this->_sanitizeValue($value);
            $replacedCount  = 0;
            $output         = '';

            foreach ( $unquoted as $string ) {
                if ( !$replacedCount ) {
                    $string = preg_replace( '/\?/', "'$sanitizedValue'", $string, 1, $replacedCount );
                }

                $quotedString = array_shift($quoted);
                if (strlen($quotedString) > 0 ) {
                    $output .= "$string'$quotedString'";
                } else {
                    $output .= $string;
                }
            }

            $this->_queryString = $output;
        }
    }
    
    /**
     * Bind a series of values at once
     * 
     * @param array $values 
     *      Array of values to bind. Associative arrays are bound to named keys,
     *      indexed arrays are bound to anonymous placeholders ('?'). Mixed arrays
     *      attempts to do both
     */
    public function bindValues( $values ) {
        foreach ($values as $key => $value ) {
            if ( is_numeric($key) ){
                $this->_bindAnonymousValue( $value );
            } else {
                $this->bindValue( $key, $value );
            }
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
        
        foreach ( $attributes as $field => $value ) {
            if ( strlen($field) && strlen($value) > self::MAX_ATTRIBUTE_SIZE ) {
                $chunks = str_split( $value, self::MAX_ATTRIBUTE_SIZE );
                foreach ($chunks as $i => $chunk ) {
                    $chunkedAttributes["{$field}[$i]"] = $chunk;
                }
                
            } elseif ( strlen($field) ) {
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
        if ( $this->queryType() == 'SELECT' ) {
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
     * 
     * That is \c "SELECT * FROM cars WHERE doors > :doors AND brand = :brand"
     * would return <code>array( 'doors', 'brand' )</code>
     * 
     * 
     * @return array
     *      of placeholder names or an empty array if there are no named placeholders
     */
    public function placeholders() {
        $pattern = '/(?<=:)([a-zA-Z0-9_]+(?![^\'"]*["\']))/';

        if ( preg_match_all( $pattern, $this->_removeQuotedValues(), $placeholders ) ) {
            return $placeholders[1];
        } else {
            return array();
        }
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
            if ( $count++ % 2 ) {
                $output .= $quotedToken;
            }
            $quotedToken = strtok( '"\'' );
        }

        unset($quotedToken); //!< ensure that the tokeniser is destroyed

        return $output;
    }
    
    /**
     * Deconstruct a query to determine which bits are inside single quotes
     * 
     * @return array
     *      First key is the parts of the query not inside of quotes, the second
     *      is those parts that are.
     */
    private function _extractQuotedValues() {
        $output      = array(array(), array());
        $insideQuotes = false;
        $offset     = 0;
        $length     = strlen($this->_queryString);
        
        while( ($i = strpos($this->_queryString, "'", $offset)) !== false && $offset < $length ) {
            if ( $insideQuotes ) {
                // Currently inside a quoted block
                if ( substr($this->_queryString, $i+1, 1) != "'" ) {
                    $output[1][] = substr($this->_queryString, $insideQuotes, $i - $insideQuotes );
                    $insideQuotes = false;
                }
            } else {
                // This is the start of a new quoted block
                $insideQuotes   = $i+1;
                $output[0][]    = substr($this->_queryString, $offset, $i - $offset  );
            }
            
            $offset = $i+1;
        } 
        
        // Add remaining outside quote
        $output[0][] = substr($this->_queryString, $offset );
        
        return $output;
    }

    /**
     * Execute the query
     *
     * Designed so that this class mimicks ORM_PDOStatement::execute(). For
     * retrieving data, you will need to call one of the fetch*() functions to
     * actually get the data.
     *
     * @todo throw an exception if all placeholders aren't bound yet
     * @todo Define exception for this action
     * 
     * @param array $values
     *      [optional] Array of values to bind. May be a mixture of named placeholders
     *      or numerically indexed anonymous values which will be bound in statement
     *      order to anonymous placeholders (marked as '?')
     * @return boolean
     *      True on success.
     */
    public function execute( array $values = null ) {
        if ( !is_null($values) ) {
            $this->bindValues( $values );
        }

        $this->bindValues( $this->_binds );
        $queryType = $this->queryType();

        switch( $queryType ) {
            case 'INSERT':
                return $this->_emulateInsert();
                
            case 'UPDATE':
                return $this->_emulateUpdate();
                
            case 'DELETE':
                return $this->_emulateDelete();
                
            case 'SELECT':
                return $this->_executeFetchQuery();
                
            default:
                throw new \ORM\Exceptions\ORMPDOException("Unsupported query type $queryType");
        }
    }
    
    /**
     * Force read (in)consistency.
     * 
     * Defaults to inconsistent reads.
     * 
     * @param boolean $consistentRead 
     *      Set to true to force read consistency. Inconsistent reads will be
     *      faster, but the information may be out of date.
     */
    public function setConsistentRead( $consistentRead ) {
        $this->_consistentRead = $consistentRead;
    }
    
    /**
     * Get the type of SQL statement
     * 
     * @return string
     *      Either 'DELETE', 'INSERT', 'UPDATE' or 'SELECT'
     */
    public function queryType() {
        if ( is_null($this->_queryType) ) {
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
        if ( isset($attributes['itemName()']) ) {
            $itemName = $attributes['itemName()'];
            unset($attributes['itemName()']);
        } else {
            $itemName = $this->_generateItemName( $domain );
        }

        self::$_lastInsertID = $itemName;

        $response = self::$_sdb->put_attributes( $domain, $itemName, $attributes );

        if ( !$response->isOK() ) {
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

        if ( $itemNamePresent ) {
            $i = array_search('itemName[]', $fields);
            $fields[$i] = 'itemName()';
        }
        
        if ( $itemNamePresent > 1 ) {
            $i = array_search(':itemName[]', $values);
            if ( $i !== false ) $values[$i] = ':itemName()';
        }

        $attributes = array();
        foreach ($values as $i => $value ) {
            $trimmedValue = trim( $value );
            if ( array_key_exists( $trimmedValue, $this->_binds) ) {
                $attributes[$fields[$i]] = $this->_binds[$trimmedValue];
            } elseif ( $trimmedValue == '?' ) {
                $attributes[$fields[$i]] = array_shift($this->_anonymousBinds);
            } else {
                $attributes[$fields[$i]] = substr($trimmedValue, 1, -1);
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

        $regex      = '/[a-z_]+ = ((\'.*?(?<!\\\)\'|:[^, )]+)|\\?)/i';
        preg_match_all($regex, $setString, $matches);
        $set        = $matches[0];
        $attributes = array();
        
        foreach ($set as $pair) {
            list( $field, $value ) = explode( ' = ', trim($pair), 2 );
            $trimmedValue = trim( $value );
            
            if ( array_key_exists($trimmedValue, $this->_binds) ) {
                $attributes[$field] = $this->_binds[$trimmedValue];
            } elseif ( $trimmedValue == '?' ) {
                $attributes[$field] = array_shift($this->_anonymousBinds);
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
        if ( !preg_match('/^UPDATE ([a-z_0-9A-Z]+) /i', $this->_queryString, $matches ) ) {
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
        if ( preg_match('/itemName\(\) = \'(.+)\'/i', $this->_queryString, $matches ) ) {
            return $matches[1];
        } elseif (isset($this->_binds[':itemName()'])) { 
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

        if ( !$result->isOK() ) {
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
        
        if ( preg_match( '/DELETE FROM ([a-z_0-9A-Z]+) WHERE itemName\(\) = \'([^\']*)/i', $this->_queryString, $matches ) ){
            // There is only going to be one item to delete
            return self::$_sdb->delete_attributes( $matches[1], $matches[2] )->isOK();
        } else {
            // May be many returned items, we will find them then delete each one
            preg_match( '/^DELETE FROM ([a-z_0-9A-Z]+) WHERE (.*)$/', $this->_queryString, $matches );
            $sql = "SELECT * FROM {$matches[1]} WHERE {$matches[2]} LIMIT 25";

            $items = self::$_sdb->select( $sql );
            return self::$_sdb->batch_delete_attributes( $matches[1], $items->itemNames() )->isOK();
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
        if ( !class_exists($className) ) {
            throw new Exceptions\ORMFetchIntoClassNotFoundException("Unknown class $className requested");
        }

        if ( !$this->_result->isOK() ) {
            throw new \ORM\Exceptions\ORMFetchIntoException(
                $this->_result->errorMessage()
            );
        } else if ( count($this->_result) ) {
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
        
        foreach ( $this->_findWith as $fetchClassName ) {
            if ( !is_subclass_of($fetchClassName, '\ORM\ORM_Model') ) {
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
     * @return string
     *      Returns the possible classname found in the SQL. This comes from
     *      ORM_Model SELECT statements that alias the table name with the model
     *      name, eg "SELECT * FROM cars AS Car" would return 'Car'. Returns empty
     *      string if no class;
     */
    private function _simplifyQuery() {
        $replace    = array();
        
        if ( preg_match('/LIMIT (\d+), (\d+) $/', $this->_queryString, $matches) ) {
            $replace[]      = $matches[0];
            $this->_offset  = (int)$matches[1];
            $this->_limit   = (int)$matches[2];
        } elseif ( preg_match('/LIMIT (\d+)$/', $this->_queryString, $matches) ) {
            $replace[]      = $matches[0];
            $this->_limit   = (int)$matches[1];
        }
        
        if ( preg_match('/AS `([^`]+)`/i', $this->_queryString, $matches ) ) {
            $className = $matches[1];
            $replace[] = "`$className`.";
            $replace[] = "AS `$className`";
        } else {
            $className = '';
        }
        
        $replace[] = "`";
        $this->_queryString = str_replace( $replace, '', $this->_queryString );
        
        if ( !is_null($this->_limit) ) {
            $this->_queryString .= " LIMIT {$this->_limit}";
        }
        
        return $className;
    }
    
    /**
     * Fetch a row from simpleDB
     *
     * Does not return itemName() properties
     *  
     * @throws \ORM\Exceptions\ORMFetchException
     * @see fetchAll()
     * @param int $fetch_style 
     *      [optional] What type of array to return. Defaults to \c FETCH_BOTH
     *      \c FETCH_ASSOC
     *          An associative array where keys are attributes.  
     *      \c FETCH_BOTH  
     *          Both associative keys and zero-indexed keys returned
     *      \c FETCH_ARRAY 
     *          Return a zero-indexed array of attributes. Since the attribute
     *          order is unpredictable on a "*" request, only use when you know
     *          the return order.
     * @return array|false
     */
    public function fetch( $fetch_style = self::FETCH_BOTH ) {
        if ( count($this->_items) === 0 ) return false;
        
        $result = array_shift($this->_items);

        switch( $fetch_style) {
            case self::FETCH_ARRAY:
                return array_values($result);

            case self::FETCH_ASSOC:
                return $result;

            case self::FETCH_BOTH:
                return array_merge(array_values($result), $result);

            default:
                throw new \ORM\Exceptions\ORMFetchException(
                        "Unknown fetch style $fetch_style"
                );
        }
        
    }
    
    /**
     * Fetch all returned results in an array
     * 
     * @see fetch()
     * @param int $fetch_style 
     *      [optional] What type of array to return. Defaults to \c FETCH_BOTH
     *      \c FETCH_ASSOC
     *          An associative array where keys are attributes. Each row will
     *          have it's itemName as key 
     *      \c FETCH_BOTH  
     *          Both associative keys and zero-indexed keys returned
     *      \c FETCH_ARRAY 
     *          Return a zero-indexed array of attributes. Since the attribute
     *          order is unpredictable on a "*" request, only use when you know
     *          the return order.
     * @return array
     *      An array of results, each one the same as calling fetch(). If there 
     *      are no results, an empty array is returned. The array will be associative
     *      with keys being itemNames if the fetch type is \c FETCH_ASSOC, otherwise
     *      it will be zero-indexed
     */
    public function fetchAll( $fetch_style = self::FETCH_BOTH ) {
        $results = array();
        
        if ( $fetch_style == self::FETCH_ASSOC ) {
            $results = $this->_items;
        } else {
            while( $results[] = $this->fetch($fetch_style) ) {}
            array_pop($results);
        }
        
        return $results;
    }

    /**
     * Fetch results into the specified class objects
     *
     * Behaves like ORM_PDOStatement::fetchAllInto().
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
        if ( !class_exists($className) ) {
            throw new Exceptions\ORMFetchIntoClassNotFoundException("Unknown class $className requested");
        }

        $collection = new \ORM\ModelCollection();
        
        if ( !$this->_result->isOK() ) {
            throw new \ORM\Exceptions\ORMFetchIntoException(
                $this->_result->errorMessage()
            );
        } elseif ( count($this->_result) ) {
            foreach ( $this->_result as $key => $attributes ) {
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
     * Populates the _result variable. Uses the $_consistentRead property to
     * determine whether consistency needs to be enforced for the SDB request
     * 
     * @return boolean
     *      \c true on success
     */
    private function _executeFetchQuery() {
        $this->_simplifyQuery();
        $optionsArray = array(
            'ConsistentRead' => $this->_consistentRead ? 'true' : 'false'
        );
        
        // If we already know some tokens, don't start from the begining (for speed)
        $initialOffset = 0;
        if ( $this->_offset > 0 ) {
            list( $initialOffset, $token ) = NextTokenCache::GetNearestToken(
                $this->_queryString, 
                $this->_limit, 
                $this->_offset
            );
            
            if ( $token ) {
                $optionsArray['NextToken'] = $token;
            }
        }
        
        $this->_result = self::$_sdb->select(
                $this->_queryString, $optionsArray 
        )->getAll( $this->_consistentRead, $this->_limit, $this->_offset, $initialOffset );
        
        $this->_items = $this->_result->items();
        return $this->_result->isOK();
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
     * Get a description of the attributes being modified in an update or insert
     * statement.
     * 
     * Returns an empty array for other query types.
     * 
     * @return array
     */
    public function attributes() {
        switch($this->queryType()) {
            case 'INSERT':
                return $this->_getAttributesFromInsertQuery($this->_queryString);
            case 'UPDATE':
                return $this->_getAttributesFromUpdateQuery();
            default:
                return array();
        }
    }
}