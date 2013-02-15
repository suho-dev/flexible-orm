<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM;
use \PDO;
use ORM\Utilities\Configuration;
use ORM\Exceptions\ORMPDOInvalidDatabaseConfigurationException;
use ORM\Exceptions\FieldDoesNotExistException;

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
     * Description of the database type
     * @var string $_databaseType
     */
    private $_databaseType;
    
    /**
     * Store the datatypes and field names after being looked up
     * @var array $_knownTables
     */
    private $_knownTables = array();

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
     * prefix = "pgsql" # optional DSN prefix (default='mysql')
     * @endcode
     * 
     * <i>Alternative Method:</i>
     * @code
     * [database]
     * dsn  = "sqlite:/opt/databases/mydb.sq3"
     * user = "test"
     * pass = "password"
     * @endcode
     *
     * \n\n
     * <i>PHP:</i>
     * @code
     * Configuration::Load('test.ini');
     *
     * // Note database is not specified as this uses the default "database"
     * $query = PDOFactory::Get("SELECT * FROM rabbits");
     * @endcode
     *
     * @throws ORMPDOInvalidDatabaseConfigurationException if
     *      configuration details are not present or invalid
     * 
     * @param string $databaseConfig
     *      Name of the group to load from the Configuration (normally this would
     *      be "database").
     * @return PDOFactory
     */
    private function __construct( $databaseConfig ) {
        if ( !Configuration::GroupExists($databaseConfig) ) {
            throw new ORMPDOInvalidDatabaseConfigurationException(
                "No database configuration details for $databaseConfig"
            );
        }

        $db_name    = Configuration::$databaseConfig()->name;
        $db_host    = Configuration::$databaseConfig()->host ?: 'localhost';
        $db_user    = Configuration::$databaseConfig()->user;
        $db_pswd    = Configuration::$databaseConfig()->pass;
        $db_prefix  = Configuration::$databaseConfig()->prefix ?: 'mysql';
        $dsn        = Configuration::$databaseConfig()->dsn;
        $dsn        = $dsn ?: "$db_prefix:host={$db_host};dbname={$db_name};";

        $this->_setDatabaseType( $dsn );

        try {
            $this->_db = new \PDO( $dsn, $db_user, $db_pswd );
            $this->_db->setAttribute( \PDO::ATTR_EMULATE_PREPARES, true);
            $this->_db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->_db->setAttribute( \PDO::ATTR_STATEMENT_CLASS, array(__NAMESPACE__.'\ORM_PDOStatement'));
        } catch( \PDOException $e ) {
            throw new \ORM\Exceptions\ORMPDOInvalidDatabaseConfigurationException( "[$databaseConfig] ".$e->getMessage() );
        }
    }
    
    /**
     * Use the database prefix as the database "type"
     * 
     * Sets the $_databaseType property
     * 
     * @param string $dsn
     *      The database connection string
     */
    private function _setDatabaseType( &$dsn ) {
        list( $this->_databaseType, ) = explode( ':', $dsn, 2 );
        
        if ( $this->_databaseType == 'sqlsrv' ) {
            $dsn = preg_replace( array('/host=/','/dbname=/'), array('Server=', 'Database='), $dsn );
        }
    }
    
    /**
     * Get the type of database this Factory is connected to
     * 
     * \note Rough- just uses the DSN prefix
     * 
     * @return string
     *      Database type as a string
     */
    public function databaseType() {
        return $this->_databaseType;
    }
    
    /**
     * Get the PDO object behind this object
     * 
     * @return PDO
     */
    public function PDO() {
        return $this->_db;
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
     * @param string $callingClass
     *      [optional] Not used for PDOFactory, required for DataFactory interface
     * @return ORM_PDOStatement
     */
    public static function Get( $sql, $database = self::DEFAULT_DATABASE, $callingClass = null ) {
        $factory = self::GetFactory( $database );
        
        return $factory->statement( $sql );
    }
    
    private function _convertToCompatibleSQL( $sql ) {
        switch( $this->databaseType() ) {
        case 'sqlsrv':
            $compatibleSql = preg_replace( "/LIMIT \d+/", '', $sql );
            $compatibleSql = str_replace( '`', '', $compatibleSql );
            break;

        case 'pgsql':
            $compatibleSql = str_replace( '`', '', $sql );
            break;

        default:
            $compatibleSql = $sql;
        }
        
        return $compatibleSql;
    }

    /**
     * Get (and set if required) the prepared statement
     * 
     * @param string $sql 
     *      The SQL to prepare
     * @return ORM_PDOStatement
     */
    public function statement( $sql ) {
        $sql = $this->_convertToCompatibleSQL( $sql );
        return $this->statementPrepared($sql) ?
                $this->getStatement($sql) :
                $this->setStatement($sql);
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
        return !preg_match('/^PRAGMA /i', $sql) && isset($this->_statements[$sql]);
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
     * @param string $database
     *      [optional] The database name. Defaults to DEFAULT_DATABASE
     * @param string $name
     *      [optional] The serial name required for Postgres
     * @return mixed
     *      Key value
     */
    public static function LastInsertId( $database = self::DEFAULT_DATABASE, $name = null ) {
        return self::GetFactory( $database )->_db->lastInsertId( $name );
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
        if ( !isset(self::$_factories[$database]) ) {
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
    
    /**
     * Get the names of each field from the database table structure
     * 
     * @param string $table
     *      The table name 
     * @return array
     *      Field names in a numerically indexed array
     */
    public function fieldNames( $table ) {
        return array_keys( $this->describeTable($table) );
    }
    
    /**
     * Get a description of a field in the table
     * 
     * @throws FieldDoesNotExistException if the field requested does not exist
     * @param string $table
     *      The table name
     * @param string $field
     *      The field name
     * @return string
     */
    public function describeField( $table, $field ) {
        $fields = $this->describeTable($table);
        
        if(array_key_exists($field, $fields) ) {
            return $fields[$field];
        } else {
            throw new FieldDoesNotExistException("Field $field does not exist in $table");
        }
    }
    
    /**
     * Get a desciption of all the fields and their field types from the given table
     * 
     * @todo Improve this so that it is easier to add to and maintain
     * 
     * @param string $table
     *      Table name 
     * @return array
     *      Keys will be field names and values will be field descriptions
     */
    public function describeTable( $table ) {
        if( !isset($this->_knownTables[$table]) ) {
            switch( $this->databaseType() ) {
            case 'sqlite':
                $this->_knownTables[$table] = $this->_describeTableSQLite( $table );
                break;
            case 'pgsql':
                $this->_knownTables[$table] = $this->_describeTablePostgres( $table );
                break;
            case 'sqlsrv':
                $this->_knownTables[$table] = $this->_describeTableMSSql( $table );
                break;
            case 'mysql':
            default:
                $this->_knownTables[$table] = $this->_describeTableMysql( $table );
            }
        }
        
        return $this->_knownTables[$table];
    }
    
    /**
     * Get the column names and datatypes from a MySQL database
     * 
     * @param string $table
     *      Table name
     * @return array
     *      Array of field names => data types
     */
    private function _describeTableMysql( $table ) {
        $query  = $this->statement( "DESCRIBE `$table`" );
        $query->execute();
        $result = $query->fetchAll( PDO::FETCH_ASSOC );

        $fields = array();
        foreach( $result as $fieldPropreties ) {
            $fields[$fieldPropreties['Field']] = $fieldPropreties['Type'];
        }
        
        return $fields;
    }
    
    /**
     * Get the column names and datatypes from a Postgres database
     * 
     * @todo implement fetching datatypes
     * @param string $table
     *      Table name
     * @return array
     *      Array of field names => data types
     */
    private function _describeTablePostgres( $table ) {
        $query  = $this->statement( "SELECT column_name FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table'" );
        $query->execute();
        $result = $query->fetchAll( PDO::FETCH_ASSOC );

        return array_map(function($row){
            return $row['column_name'];
        }, $result );
    }
    
    /**
     * Get the column names and datatypes from a MS SQL database
     * 
     * @todo implement fetching datatypes
     * @param string $table
     *      Table name
     * @return array
     *      Array of field names => data types
     */
    private function _describeTableMSSql( $table ) {
        $query  = $this->statement( "EXEC sp_columns @table_name= N'$table'" );
        $query->execute();
        $result = $query->fetchAll( PDO::FETCH_ASSOC );

        return array_map(function($row){
            return $row['COLUMN_NAME'];
        }, $result );
    }
    
    /**
     * Get the column names and datatypes from a SQLite database
     * 
     * @todo implement fetching datatypes
     * @param string $table
     *      Table name
     * @return array
     *      Array of field names => data types
     */
    private function _describeTableSQLite( $table ) {
        $query  = $this->statement( "PRAGMA table_info($table)" );
        $query->execute();
        $result = $query->fetchAll( PDO::FETCH_ASSOC );

        return array_map(function($row){
            return $row['name'];
        }, $result );
    }
    
}