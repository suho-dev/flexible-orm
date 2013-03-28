<?php
/**
 * @file
 * @author jarrod.swift
 */
/**
 * Amazon Simple DB resources
 */
namespace Suho\FlexibleOrm\SDB;
use \ORM\Utilities\Configuration;
use \AmazonSDB;

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
     *   - Use the Configuration value \c AWS->region to set the region for the SDB
     *   - Use the Configuration value \c AWS->apc_enabled to enable/disable APC
     *   - Use the Configuration value \c AWS->key and \c AWS->secret_key to determine
     *   - Use the Configuration value \c AWS->sdb_alternate_hostname and AWS->sdb_credentials to specify alternate endpoint host/port, and CFCredential name
     *
     * the account settings for Amazon SimpleDB
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
        self::$_sdb = new AmazonSDB( self::_BuildSDBConstructorOptions() );
        
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
        if ( $region = self::SDBRegion() ) {
            self::$_sdb->set_region( $region );
        }

        if ( Configuration::AWS()->apc_enabled ) {
            self::$_sdb->set_cache_config('apc');
        }
        
        if ( Configuration::AWS()->sdb_alternate_hostname ) {
            self::$_sdb->ssl_verification = false;
            self::$_sdb->use_ssl = false;
            if (strstr(Configuration::AWS()->sdb_alternate_hostname, ":")) {
                list($hostname, $port_number) = explode(":", Configuration::AWS()->sdb_alternate_hostname);
                self::$_sdb->set_hostname($hostname, $port_number);
            } else {
                self::$_sdb->set_hostname(Configuration::AWS()->sdb_alternate_hostname);
            }
        }
    }
    
    private static function _BuildSDBConstructorOptions() {
        list( $key, $secret ) = self::_GetAWSKeys();
        
        $options = array();
        
        if( $key ) {
            $options['key']     = $key;
        }
        
        if( $secret ) {
            $options['secret'] = $secret;
        }
        if (Configuration::AWS()->sdb_credentials) {
            $options['credentials'] = Configuration::AWS()->sdb_credentials;
        }
        return $options;
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
        $key        = $aws->key         ?: self::_GetAWSKeyID();
        $secret_key = $aws->secret_key  ?: self::_GetAWSSecretKey();
        
        return array( $key, $secret_key );
    }
    
    /**
     * Attempt to determine the AWS key from the constant AWS_KEY
     * @return string
     *      The AWS key if defined, otherwise a blank string.
     */
    private static function _GetAWSKeyID() {
        return defined('AWS_KEY') ? AWS_KEY : '';
    }
    
    /**
     * Attempt to determine the AWS secret key from the constant AWS_SECRET_KEY
     * @return string
     *      The secret key if defined, otherwise a blank string
     */
    private static function _GetAWSSecretKey() {
        return defined('AWS_SECRET_KEY') ? AWS_SECRET_KEY : '';
    }
    
    /**
     * Get the active SDB connection
     * 
     * Connect and set settings if required. See _InitSDBConnection() for all 
     * options that are set and configurable.
     * 
     * @return AmazonSDB
     */
    public static function GetSDBConnection() {
        if ( is_null(self::$_sdb) ) {
            self::_InitSDBConnection();
        }
        
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
     * @param boolean $consistentRead
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