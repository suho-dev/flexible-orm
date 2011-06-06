<?php
/**
 *
 * @file
 * @author jarrod.swift
 */
namespace ORM;
use ORM\Utilities\Configuration;

/**
 * A singleton database managing class that acts as a factory for prepared
 * statements.
 *
 * To use, simply call PDOFactory::Get( $yourSQLstring );
 *
 * \copydoc PDOFactory::Get()
 *
 * @see Configuration, ORM_PDOStatement
 */
class PDOFactory implements Interfaces\DataFactory {
    /**
     * The name of the configuration group if database configuration group is
     * not specified
     */
    const DEFAULT_DATABASE = 'database';

    /**
     * Array of PDOFactory instances, only one for each database connection
     *
     * Keys are the database configuration name
     * 
     * @var array $_factories
     */
    private static $_factories = array();

    /**
     * An associative array of prepared PDO statements
     *
     * The keys are the SQL query strings. This is used to ensure that if the
     * same SQL is requested multiple times the statement will only be prepared
     * once. Each element of the array will be a different ORM_PDOStatement.
     * 
     * @var array $_statements
     */
    private $_statements;

    /**
     * Database Connection
     * @var PDO $_db
     */
    private $_db;

    /**
     * Connect to the database when creating the PDOFactory instance
     *
     * Sets the PDO connection to return ORM_PDOStatement objects when preparing
     * SQL.
     *
     * Uses configuration settings from the Configuration class. An example setup:
     *
     * <i>Ini File (test.ini):</i>
     * @code
     * [database]
     * name = "test_db"
     * host = "localhost"  #This is optional [default='localhost']
     * user = "test"
     * pass = "password"
     * @endcode
     *
     * <i>PHP:</i>
     * @code
     * Configuration::Load('test.ini');
     *
     * // Note database is not specified as this uses the default "database"
     * $query = PDOFactory::Get("SELECT * FROM rabbits");
     * @endcode
     *
     * @throws \ORM\Exceptions\ORMPDOInvalidDatabaseConfigurationException if
     *      configuration details are not present or invalid
     * 
     * @param string $databaseConfig
     *      Name of the group to load from the Configuration (normally this would
     *      be "database").
     * @return PDOFactory
     */
    private function __construct( $databaseConfig ) {
        if( !Configuration::GroupExists($databaseConfig) ) {
            throw new \ORM\Exceptions\ORMPDOInvalidDatabaseConfigurationException(
                "No database configuration details for $databaseConfig"
            );
        }
        
        $db_name = Configuration::$databaseConfig()->name;
        $db_host = Configuration::$databaseConfig()->host ?: 'localhost';
        $db_user = Configuration::$databaseConfig()->user;
        $db_pswd = Configuration::$databaseConfig()->pass;
        $dsn     = "mysql:host={$db_host};dbname={$db_name};";
        
        try {
            $this->_db = new \PDO( $dsn, $db_user, $db_pswd );
            $this->_db->setAttribute( \PDO::ATTR_EMULATE_PREPARES, TRUE);
            $this->_db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->_db->setAttribute( \PDO::ATTR_STATEMENT_CLASS, array(__NAMESPACE__.'\ORM_PDOStatement'));
        } catch( \PDOException $e ) {
            throw new \ORM\Exceptions\ORMPDOInvalidDatabaseConfigurationException( $e->getMessage() );
        }
    }

    /**
     * Get a prepared PDOStatement matching the supplied string
     *
     * If the SQL matches an already prepared statement, return that statement
     * instead of preparing a new one. This is useful when a query is run
     * multiple times with different bound parameters.
     *
     * <i>For Example:</i>
     * @code
     * $query = PDOFactory::Get("SELECT * FROM cars WHERE id = :id");
     * $query->bindValue( ':id', 1 );
     * $query->execute();
     *
     * $anotherQuery = PDOFactory::Get("SELECT * FROM cars WHERE id = :id");
     * $query->bindValue( ':id', 2 );
     * $query->execute();
     * // at this point $query === $anotherQuery
     * @endcode
     * 
     * @param string $sql
     *      The SQL to prepare
     * @param string $database
     *      [optional] The name of the database Configuration group. If not supplied
     *      uses the group DEFAULT_DATABASE = "database". Allows one application
     *      to connect to multiple databases.
     * @return ORM_PDOStatement
     */
    public static function Get( $sql, $database = self::DEFAULT_DATABASE, $callingClass = null ) {
        $factory = self::GetFactory( $database );

        return $factory->statementPrepared($sql) ?
                    $factory->getStatement($sql) :
                    $factory->setStatement($sql);
    }

    /**
     * Check to see whether a matching prepared statement has already been prepared
     * for the supplied SQL
     * 
     * @param string $sql
     *      The SQL to prepare
     * @return boolean
     */
    public function statementPrepared( $sql ) {
        return isset($this->_statements[$sql]);
    }

    /**
     * Prepare and store a SQL statement
     *
     * @param string $sql
     *      The SQL to prepare
     * @return ORM_PDOStatement
     */
    public function setStatement( $sql ) {
        $this->_statements[$sql] = $this->_db->prepare($sql);

        return $this->getStatement($sql);
    }

    /**
     * Retrieve a prepared statement
     * 
     * @param string $sql
     *      The SQL statement required
     * @return ORM_PDOStatement
     */
    public function getStatement( $sql ) {
        return $this->_statements[$sql];
    }

    /**
     * Returns the ID of the last inserted row, or the last value from a sequence
     * object, depending on the underlying driver.
     *
     * A shortcut to the PDO::lastInsertId()
     *
     * return mixed
     *      Key value
     */
    public static function LastInsertId() {
        return self::GetFactory()->_db->lastInsertId();
    }

    /**
     * Get the instance of PDOFactory
     *
     * @param string $database
     *      [optional] The name of the database Configuration group. If not supplied
     *      uses the group DEFAULT_DATABASE = "database". Allows one application
     *      to connect to multiple databases.
     * @return PDOFactory
     */
    public static function GetFactory( $database = self::DEFAULT_DATABASE ) {
        if( !isset(self::$_factories[$database]) ) {
            self::$_factories[$database] = new PDOFactory($database);
        }
        
        return self::$_factories[$database];
    }

    /**
     * Turn on DB Profiling
     *
     * @see getProfile()
     */
    public function startProfiling() {
        $query = $this->_db->query("SET profiling=1, profiling_history_size = 50");
        $query->execute();
    }

    /**
     * Get an array detailing database activity
     *
     * Profiling must have first been enabled using startProfiling()
     *
     * @see startProfiling()
     * @return array
     */
    public function getProfile() {
        $query = $this->_db->query( 'SHOW profiles' );
        $query->execute();
        $profiles = $query->fetchAll( \PDO::FETCH_ASSOC);

        $query = $this->_db->query( 'SELECT COUNT(DISTINCT(QUERY_ID)), SUM(Duration) as duration, \'\' FROM information_schema.profiling' );
        $query->execute();
        $profiles[] = $query->fetch( \PDO::FETCH_ASSOC);

        return $profiles;
    }
}
?>
