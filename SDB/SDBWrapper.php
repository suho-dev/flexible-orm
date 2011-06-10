<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\SDB;
use \ORM\Utilities\Configuration;

/**
 * Manage connection to and settings for an AmazonSDB connection
 * 
 * @see SDBStatement
 */
abstract class SDBWrapper {
    /**
     * The maximum size a single attribute is allowed to be (in bytes)
     * Currently the Amazon SimpleDB limit is 1K
     */
    const MAX_ATTRIBUTE_SIZE = 1024;
    
    /**
     * @var AmazonSDB $_sdb
     */
    protected static $_sdb;
    
    /**
     * Initialise the SDB connection when instantiating
     */
    public function __construct() {
        self::_InitSDBConnection();
    }
    
    /**
     * Setup the SDB connection
     *
     *   - Use the Configuration value AWS->region to set the region for the SDB
     *   - Use the Configuration value AWS->apc_enabled to enable/disable APC
     * 
     * Valid regions for AWS->region are:
     *   - us-east
     *   - us-west
     *   - ap-southeast
     *   - ap-northeast
     *   - eu-west
     * 
     * All other values will be ignored.
     * 
     * @see _InitSDBSettings() for environment specific SDB configuration
     */
    private static function _InitSDBConnection() {
        list( $key, $secret ) = self::_GetAWSKeys();

        self::$_sdb = new \AmazonSDB( $key, $secret );
        self::$_sdb->set_response_class( __NAMESPACE__.'\SDBResponse');
        self::_InitSDBSettings();
    }
    
    /**
     * Initialise the settings for the SDB object that may change from system
     * to system
     * 
     * @see _InitSDBConnection()
     */
    private static function _InitSDBSettings() {
        if( $region = self::SDBRegion() ) {
            self::$_sdb->set_region( $region );
        }

        if( Configuration::AWS()->apc_enabled ) {
            self::$_sdb->set_cache_config('apc');
        }
    }
    
    /**
     * Get the SDB region from the ini file
     * 
     * Converts from short form names (like 'us-west') to urls ('sdb.us-west-1.amazonaws.com')
     * 
     * @see _InitSDBConnection()
     * @return string 
     */
    public static function SDBRegion() {
        switch(Configuration::AWS()->region) {
            case 'us-east':
                return false;
            case 'us-west':
                return \AmazonSDB::REGION_US_W1;
            case 'ap-northeast':
                return \AmazonSDB::REGION_APAC_NE1;
            case 'eu-west':
                return \AmazonSDB::REGION_EU_W1;
            case 'ap-southeast':
            default:
                return \AmazonSDB::REGION_APAC_SE1;
        }
    }
    
    /**
     * Get array containing the public and private keys for accessing AmazonSDB
     * 
     * \note These can be retrieved through the Accounts panel of the AWS website.
     * 
     * Looks for ini settings below, or the constants AWS_KEY and AWS_SECRET_KEY
     * @code
     * [AWS]
     * key = "aaaa"
     * secret_key = "aaaaa"
     * @endcode
     * 
     * @return array
     *      2 elements to the array: [0] => public, [1] => private
     */
    private static function _GetAWSKeys() {
        $aws        = Configuration::AWS();
        $key        = $aws->key ?: AWS_KEY;
        $secret_key = $aws->secret_key ?: AWS_SECRET_KEY;
        
        return array( $key, $secret_key );
    }
    
    /**
     * Get the active SDB connection
     * @return AmazonSDB
     */
    public static function GetSDBConnection() {
        if( is_null(self::$_sdb) ) self::_InitSDBConnection();
        
        return self::$_sdb;
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
        return self::GetSDBConnection()->select( $queryString, array(
            'ConsistentRead' => $consistentRead ? 'true' : 'false',
            'NextToken'      => $nextToken
        ));
    }
}