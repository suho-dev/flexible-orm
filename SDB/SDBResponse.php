<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\SDB;
/**
 * Represent retrieved items and attributes from AmazonSDB as an array
 *
 * Works for both get_attributes and select.
 *
 * <b>Usage</b>
 * \include sdb.sdbresponse.example.php
 *
 * @todo test working with tagged values (multiple values for single attribute)
 *
 * @see SDBFactory, SDBStatement, ORMModelSDB
 */
class SDBResponse extends \CFResponse implements \Iterator, \ArrayAccess, \Countable {
    /**
     * The maximum number of consecutive "nextToken" queries run when getAll() is
     * called. Each query will usually return 100 items.
     */
    const MAX_QUERIES = 30;
    /**
     * Store the returned results as arrau
     * @var array $_items
     */
    private $_items = array();

    /**
     * The current key from the $_items array
     *
     * Maintains the internal pointer to the $_items array so this class can be
     * used with foreach loops.
     * 
     * @var string $_currentKey
     */
    private $_currentKey;

    /**
     * Array of keys from $_items
     * @var array $_itemNames
     */
    private $_itemNames;

    /**
     * The numerical position of the key within the $_itemNames array
     * @var int $_position
     */
    private $_position = 0;
    
    /**
     * Read the XML response into the $_items array if this is a supported
     * action.
     *
     * \note Currently this only supports AWS SDB actions: GetAttributesResult
     *      and SelectResult
     */
    public function __construct( $header, $body, $status = null ) {
        parent::__construct( $header, $body, $status );
        
        if( isset($this->body->GetAttributesResult) ) {
            $this->_getAttributesResult();
        } elseif( isset($this->body->SelectResult) ) {
            $this->_getSelectResult();
        }

        $this->rewind();
    }

    /**
     * Iterpret the result of a select operation into a 2-dimensional array
     * of items and their attributes.
     */
    private function _getSelectResult()  {
        $items = $this->body->SelectResult->Item();

        if( $items ) {
            foreach( $items as $item ) {
                $this->_items[(string)$item->Name] = $this->_getObject($item->Attribute);
            }
        }
    }

    /**
     * Convert an XML attribute tree to array of key-value pairs
     *
     * @see _getSelectResult(), _getAttributesResult()
     * @param SimpleXMLElement $attributes
     * @return array
     */
    private function _getObject( $attributes ) {
        $att_array = array();
        $toFlatten = array();

        foreach($attributes as $attribute ) {
            $name = (string)$attribute->Name;
            if( isset($att_array[$name]) ) {
                $att_array[$name]   = (array)$att_array[$name];
                $att_array[$name][] = (string)$attribute->Value;

            } elseif( preg_match('/(\w+)\[(\d+)\]/', $name, $matches) ) {
                // Large attributes are chunked into multiple items <fieldname>_<i>
                if( !isset($att_array[$matches[1]]) ) {
                    $att_array[$matches[1]] = array();
                    $toFlatten[] = $matches[1];
                }
                
                $att_array[$matches[1]][$matches[2]] = (string)$attribute->Value;
                
            } else {
                $att_array[$name] = (string)$attribute->Value;
            }
        }

        foreach($toFlatten as $key ) {
            ksort($att_array[$key]);
            $att_array[$key] = implode('', $att_array[$key]);
        }

        return $att_array;
    }

    /**
     * Interpret the result of a get_attributes() operation into an associative
     * array of attribute names and values
     */
    private function _getAttributesResult() {
        $attributes = $this->body->GetAttributesResult->Attribute();

        if( $attributes ) {
            $this->_items = $this->_getObject($attributes);
        }
    }

    /**
     * Get a list of itemName() values from SDB
     * @return array
     */
    public function itemNames() {
        return $this->_itemNames;
    }

    /**
     * Get the returned items as an array
     * @return array
     */
    public function items() {
        return $this->_items;
    }
    
    /**
     * Ensure the query is complete
     *
     * Amazon SDB only returns a limited set of data (usually around 100 results).
     * Calling this method will ensure that SDB is queried until all results are
     * returned. It will stop retrieving results when MAX_QUERIES is reached.
     *
     * Additionally returned items are appended to this response object's items
     * array.
     *
     * \note Only works with Select statements. It should not be neccasary for
     *       others, except maybe getAttributes if you have a large number of
     *       attributes or listDomains if you have a lot.
     * 
     * \note The $consistentRead parameter must have the same value as the original
     *       command, if the original query was run with consistentRead==true, 
     *       then getAll() must be called as getAll(true)
     *
     * @param boolean $consistentRead
     *      [optional] defaults to false. Tell SDB whether or not to force consistency.
     *      This must be the same as the original query (ie if the original query
     *      enforced consistent read, this must be true)
     * @param int $limit
     *      [optional] Maximum number of records to return (including the original items)
     *      Defaults to no limit (other than those imposed by MAX_QUERIES).
     * @paran int $offset
     *      [optional] Number of rows to skip from beinging when used with limit.
     *      Defaults to 0 (start from beginning).
     * return SDBResponse
     *      The current SDBResponse item is returned for convenience
     */
    public function getAll($consistentRead = false, $limit = null, $offset = 0) {
        if( isset($this->body->SelectResult) ) {
            $result = $this;
            $query  = $this->getQuery();
            $count  = 0;
            
            if( $limit && $offset ) {
                NextTokenCache::Store($query, $limit, $offset, $result->nextToken());
            }

            while( (is_null($limit) || count($this->_items) < $limit) && $result->nextToken() ) {
                $limitRemaining = is_null($limit) ? 1000 : $limit - count($this->_items);
                $result = SDBStatement::Query( "$query LIMIT $limitRemaining", $consistentRead, $result->nextToken() );
                
                if( !$result->isOK() ) {
                    throw new \ORM\Exceptions\ORMPDOException($result->errorMessage() );
                }
                
                if( $limit && $offset ) {
                    NextTokenCache::Store($query, $limit, $offset, $result->nextToken());
                }
                
                foreach( $result as $key => $item ) {
                    $this->_items[$key] = $item;
                }
                
                if( ++$count > self::MAX_QUERIES ) break;
            }
        }
        
        return $this;
    }

    /**
     * Get the nextToken if it exists for completing this SDB request
     *
     * @return string|null
     */
    public function nextToken() {
        return empty($this->body->SelectResult->NextToken) ?
                    null : (string)$this->body->SelectResult->NextToken;
    }

    /**
     * Get the SELECT query used to generate this reponse
     *
     * Only works with select queries (obviously)
     * @return string
     */
    public function getQuery() {
        $marker         = '&SelectExpression=';
        $pos            = strpos( $this->header['x-aws-body'], $marker );
        $end            = strpos( $this->header['x-aws-body'], '&', $pos+10 );
        $queryStringLen = $end - $pos - strlen($marker);
        $encodedQuery   = substr($this->header['x-aws-body'], $pos+strlen($marker), $queryStringLen);
        
        return urldecode($encodedQuery);
    }
    
    /**
     * Get the error details (if there are errors)
     * 
     * @return string
     */
    public function errorMessage() {
        if( !$this->isOK() ) {
            $errors = $this->body->Message();
            return  (string)$errors[0];
        }
    }

    /**
     * Get the current item array
     *
     * @return array
     */
    public function current() {
        return $this->_items[$this->_currentKey];
    }

    /**
     * Get the current (in iteration) key
     *
     * This will also be the itemName for the current item
     * @return string
     */
    public function key() {
        return $this->_currentKey;
    }

    /**
     * Advance the internal pointer and return the new key
     * @return string
     */
    public function next() {
        $this->_currentKey = ++$this->_position >= count($this->_items) ? null : $this->_itemNames[$this->_position];
        return $this->_currentKey;
    }

    /**
     * Reset the internal pointer to the start
     *
     * Also ensure the _itemNames array correctly contains all the item names
     */
    public function rewind() {
        $this->_position    = 0;
        $this->_itemNames   = array_keys($this->_items);
        if( count($this->_items) ) {
            $this->_currentKey  = $this->_itemNames[$this->_position];
        }
    }

    /**
     * Ensure the the current key position is valid
     * @return boolean
     */
    public function valid() {
        return $this->offsetExists($this->_currentKey);
    }

    /**
     * Check if the requested key exists in the result set
     *
     * Because the key is the itemName, this tells you whether a particular
     * item was present in the results
     *
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->_items);
    }

    /**
     * Get an item's key pairs
     *
     * Allows the SDB result to be accessed like an array
     *
     * <b>Usage:</b>
     * <code>
     * $sdb     = new AmazonSDB();
     * $result  = AmazonSDBResult( $sdb->select("SELECT * FROM stuff") );
     *
     * print_r( $result['mykey'] );
     * </code>
     *
     * @param string $offset
     * @return array
     */
    public function offsetGet($offset) {
        return $this->_items[$offset];
    }

    /**
     * Add or modify an array item
     */
    public function offsetSet($offset, $value) {
        $this->_items[$offset]  = $value;
        $this->_itemNames       = array_keys($this->_items);
    }

    /**
     * Delete a value from a resultset - will throw error
     *
     * This has to exist for the ArrayAccess interface, but it does not make
     * sense to use it. As a result, it will through an Exception if it is called.
     *
     * @param string $offset
     * @throws Exception
     */
    public function offsetUnset($offset) {
        throw new Exception("Can't change values of SDB Result!");
    }

    /**
     * The total number of items returned
     *
     * Can also be called using the inbuilt count() method
     * @return int
     */
    public function count() {
        return count( $this->_items );
    }
}
?>
