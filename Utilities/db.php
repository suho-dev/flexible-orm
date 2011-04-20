<?php
/**
 * Define the database connection layer for ORM
 * 
 * @package Utilities
 * @author Jarrod Swift
 */
namespace ORM;
use PDO;

/**
 * Singleton class for connecting to the database
 * 
 * Ensures the same connection is always used throughout
 * the script.
 *
 * Example Usage:
 * <code>  $db_conn = DB::connect();</code>
 * 
 * Also includes the ability to use memcache if available
 * 
 * @package Utilities
 * @subpackage Database
 */
class DB {
	/**
	 * The database connection
	 * 
	 * Should be referenced using the DB::connect() method
	 * 
	 * @var DataObject $database
	 */
	public static $database;
	
	/**
	 * The name of the database currently connnected 
	 * @var string $db_name
	 */
	public static $db_name;
	
	/**
	 * Memcache object if available
	 * @var Memcache $memcache
	 */
	public static $memcache;
	
	/**
	 * Database user name, can be overridden by defining DATABASE_USER
	 */
	const DATABASE_USER 		= 'root';
	
	/**
	 * Database user password, can be overridden by defining DATABASE_PASSWORD
	 */
	const DATABASE_PASSWORD		= '123qwe';
	
	/**
	 * Database host, can be overridden by defining DATABASE_HOST
	 */
	const DATABASE_HOST			= 'localhost';
	
	/**
	 * Database name, can be overridden by defining DATABASE_NAME or when calling
	 * constructor
	 */
	const DATABASE_NAME			= 'test';
	
	/**
	 * The class name of the caching object.
	 * 
	 * Should implement the {@link Cache} class. Framework has 
	 * built-in support for Memcache and APCcache. Set to false
	 * if not used. Defaults to 'APCcache'
	 * 
	 * Eg, to use memcache, set value to "Memcache"
	 * 
	 * @see Cache, APCcache
	 */
	const CACHE_CLASS			= false;
	
	/**
	 * The size of the profile history
	 * @see profile()
	 */
	const PROFILING_HISTORY_SIZE = 50;

        const FETCH_CLASS = PDO::FETCH_CLASS;
	
	/**
	 * Connect to the database
	 *
         * For legacy reasons there are four ways to connect. The favoured method is
         * the Configuration class
         *
         * The methods are outlined below (in order of precedence). For security reasons, the database settings
         * are removed after loading (so that if the contents of the configuration
         * settings are accidently exposed, the chance of them containing the
         * database settings is reduced).
         * 
         * <b>Configuration loaded from ini</b>
         * <code>
         * [database]
         * database.host
         * database.name
         * database.user
         * database.pass
         * <code>
         *
         * <b>global $config</b>
         * As above, but loaded into a global array named $config
         * 
         * <b>CONSTANTS</b>
         * DATABASE_NAME, DATABASE_USER, DATABASE_HOST, DATABASE_PASSWORD
         *
         * <b>Defaults</b>
         *
	 * @param string $database_name 		Name of the database to connect to
	 * @return DB
         * @see Configuration
	 */
	private function __construct( $database_name = false ) {
		global $config;
		
		if( Configuration::Value('database') ) {
			self::$db_name = Configuration::Value('database.name', 'database');
		
			$db_host = Configuration::Value('database.host', 'database');
			$db_user = Configuration::Value('database.user', 'database');
			$db_pswd = Configuration::Value('database.pass', 'database');
			Configuration::Remove('database');
			
		} elseif( isset($config['database']) ) {
			self::$db_name = $config['database']['database.name'];
		
			$db_host = $config['database']['database.host'];
			$db_user = $config['database']['database.user'];
			$db_pswd = $config['database']['database.pass'];
			
			unset( $config['database'] );
		} else {
			self::$db_name = defined( 'DATABASE_NAME' ) ? DATABASE_NAME : DB::DATABASE_NAME;
		
			$db_host = defined( 'DATABASE_HOST' ) ? DATABASE_HOST : DB::DATABASE_HOST;
			$db_user = defined( 'DATABASE_USER' ) ? DATABASE_USER : DB::DATABASE_USER;
			$db_pswd = defined( 'DATABASE_PASSWORD' ) ? DATABASE_PASSWORD : DB::DATABASE_PASSWORD;
		}
		
		if( $database_name ) { self::$db_name = $database_name; }
		
		try
		{
			$dsn = "mysql:host={$db_host};dbname=".self::$db_name.";";//unix_socket=/var/run/mysqld/mysqld.sock
			self::$database = new PDO( $dsn, $db_user, $db_pswd );
			self::$database->setAttribute(PDO::ATTR_EMULATE_PREPARES, TRUE);
			self::$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e)
		{
			//Log message? 'Failed: ' . $e->getMessage();
			throw new Exception( "There has been a database connection error: ".$e->getMessage() );
		}


                if( $cache_class = Configuration::Value('cache.class', 'framework') ) {
                    self::$memcache = new $cache_class();
                } elseif( $cache_class = $config['framework']['cache.class'] ) {
                    self::$memcache = new $cache_class();
		}
	}
	
	/**
	 * Inititate the MySQL Profile
	 * 
	 * @link http://dev.mysql.com/tech-resources/articles/using-new-query-profiler.html
	 * @see get_profiles()
	 * @return void
	 */
	public static function profile() {
		$query = self::connect()->query("SET profiling=1, profiling_history_size = ". DB::PROFILING_HISTORY_SIZE);
		$query->execute();
	}
	
	/**
	 * Return the results of the MySQL profiler
	 * 
	 * Returns the basic summary of all queries executed since the profiler
	 * was activated
	 * 
	 * @return array
	 * @see profile()
	 */
	public static function get_profiles() {
		$query = self::$database->query( 'SHOW profiles' );
		$query->execute();
		$profiles = $query->fetchAll(PDO::FETCH_ASSOC);
		
		$query = self::$database->query( 'SELECT COUNT(DISTINCT(QUERY_ID)), SUM(Duration) as duration, \'\' FROM information_schema.profiling' );
		$query->execute();
		$profiles[] = $query->fetch(PDO::FETCH_ASSOC);
		
		return $profiles;
	}
	
	/**
	 * Return an active PDO database connection
	 * 
	 * Uses the current one if it exists
	 * @return PDO
	 * @param string $database_name[optional]
	 */
	public static function connect( $database_name = false ) {
		if( is_null(self::$database) ) { new DB($database_name); }
		
		return self::$database;
	}
	
	/**
	 * Get the row id of the last inserted row
	 * @return int
	 */
	public static function last_insert_id() {
		$query 	= self::$database->query( 'SELECT LAST_INSERT_ID()' );
		$result = $query->fetch( PDO::FETCH_ASSOC );
		
		return (int)$result['LAST_INSERT_ID()'];
	}
	
	/**
	 * Kill the current database connection
	 * @return void
	 */
	public static function disconnect() {
		self::$database = null;
	}
	
}