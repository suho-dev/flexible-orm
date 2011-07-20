<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM;

use ORM\Exceptions;

/**
 * Base class for ordinary ORM classes
 *
 * It is very similar to the old base_model class but has been simplified to
 * have a more consistent interface and require less code to create model classes.
 *
 * \n\n
 * <b>Basic Usage</b>
 *
 * If the default configuration is correct for a model, then all is required
 * is to create a subclass of ORM_Model
 * 
 * \include orm_model.example.php
 *
 * The default configuration is simply:
 *  - Primary key is "id"
 *  - Table name is lowercase plural of model name (in the example above "cars")
 *
 * Alternatively, configuration can be set manually through constants:
 * - <b>PRIMARY_KEY</b>: sets the primary key for this table, only supports single
 * keys
 * - <b>TABLE</b>: sets the table name if it is not just the plural of the model name
 *
 * \include orm_model_custom.example.php
 * 
 * \n\n
 * <b>Foreign Keys</b>
 *
 * It is possible to fetch an object with related object(s) in a single query. This
 * only works for the model that has the foreign key in it. For more information,
 * on configuring foreign keys, see \ref intro_step3 "Define Foreign Keys".
 *
 * \include orm_model_foreign.example.php
 * 
 * \n\n
 * <b>Counting and Pagination</b>
 * 
 * For "paging" data (i.e. splitting into fixed sized sequential groups) you simply
 * need to use the CountFindAll() function and the \c limit and \c offset options.
 * 
 * \include orm_model.pagination.example.php
 *
 * \n\n
 * @todo Exceptions - if model is poorly coded, it should raise a more descriptive
 *      exception than a PDOException
 * 
 */
abstract class ORM_Model extends ORM_Core implements Interfaces\ORMInterface {
    /**
     * Map the value of the unique identifier to $_id for simplicity
     * @see id()
     * @var mixed $_id
     */
    protected $_id;
    
    /**
     * Only return the total number of matches, not the items
     * @see _BuildSQL()
     */
    const QUERY_COUNT_ONLY    = 1;
    
    /**
     * Default setting, just perform a query with joins and return the results
     * @see _BuildSQL()
     */
    const QUERY_REGULAR       = 2;
    
    /**
     * Placeholder constant for profiling a query
     * 
     * \note Not currently implemented, will behave like QUERY_REGULAR
     * @see _BuildSQL()
     */
    const QUERY_PROFILE_ONLY    = 3;
    
    /**
     * Create a new ORM_Model, optionally with some preset options
     *
     * @see ORM_Core::__construct()
     * @param array $values
     */
    public function __construct( $values = array() ) {
        // Get the primary key variable and assign $_id to be a pointer to it
        $this->_id  = &$this->{$this->PrimaryKeyName()};

        parent::__construct( $values );
    }

    /**
     * Find a single object
     *
     * May be used two ways: as a simple lookup by primary key, or as a more complex
     * lookup using an array of options.
     *
     * \n\n
     * <b>Usage: Primary key lookup</b>
     * @code
     * // Find car with id=2
     * @car = Car::Find( 2 );
     * @endcode
     *
     * <b>Usage: Array of options</b>
     * @code
     * $minDoors = 3;
     * $brand    = 'Alfa Romeo';
     *
     * // Find the first car that is not an Alfa Romeo where doors is greater than 3
     * $car      = Car::Find(array(
     *      'where' => 'doors > ? AND brand NOT LIKE ?',
     *      'order' => 'colour DESC',
     *      'values' => array( $minDoors, $brand ),
     * ));
     * @endcode
     *
     * For more information about the options array, see \ref intro_step4_options "Options Array"
     * in the \ref getting_started "Getting Started" tutorial.
     * 
     * \n\n
     * <b>Foreign Keys</b>
     *
     * It is possible to fetch an object with related object(s) in a single query. This
     * only works for the model that has the foreign key in it. For more information,
     * on configuring foreign keys, see \ref intro_step3 "Define Foreign Keys".
     *
     * @code
     * // Fetch the car with id 1 and include the Owner object
     * @car = Car::Find( 1, 'Owner' );
     * echo "This car is owned by ", $car->Owner->name;
     * @endcode
     * 
     * \n\n
     * <b>Other</b>\n
     * There is a hook for all \e get methods named \c afterGet(), to allow actions
     * to be performed when an object is fetched (as opposed to the constructor, 
     * which is called both when a new object is created or fetched).
     * 
     * @see FindByOptions(), FindAll()
     * @param mixed $idOrArray
     *      Either an array of search options or a primary key value. If nothing
     *      is supplied, the first database entry is returned
     * @param string|array $findWith
     *      [optional] A string or array of strings defining the related model
     *      names to also fetch (see \ref intro_step3 "Define Foreign Keys")
     * @return ORM_Model
     */
    public static function Find( $idOrArray = array(), $findWith = false ) {
        if( is_array($idOrArray) ) {
            return static::FindByOptions( $idOrArray, $findWith );
        } else {
            return static::FindBy( static::PrimaryKeyName(), $idOrArray, $findWith );
        }
    }

    /**
     * Find all matching objects
     *
     * Same syntax as Find() except will return a ModelCollection of all matched
     * items, rather than just the first matching object.
     *
     * \note There is a hook for all \e get methods named \c afterGet(), to allow actions
     *      to be performed when an object is fetched (as opposed to the constructor, 
     *      which is called both when a new object is created or fetched).
     * 
     * @see Find(), FindAllBy()
     * @param array $optionsArray
     *      [optional] An array of options for finding objects. If not supplied, this will
     *      return all objects of this type stored in the database. For more
     *      information about the options array, see \ref intro_step4_options "Options Array"
     *      in the \ref getting_started "Getting Started" tutorial
     * @param string|array $findWith
     *      [optional] A string or array of strings defining the related model
     *      names to also fetch (see \ref intro_step3 "Define Foreign Keys")
     * @return ModelCollection
     *      A collection of ORM_Model subclasses
     */
    public static function FindAll( $optionsArray = array(), $findWith = false ) {
        $df     = static::DataFactory();
        $sql    = static::_BuildSQL( $optionsArray, static::TableName(), $findWith );
        $query  = $df::Get( $sql, static::DatabaseConfigName(), get_called_class() );

        $query->execute( isset($optionsArray['values']) ? $optionsArray['values'] : null);

        $items = $query->fetchAllInto( get_called_class() );
        $items->each(function($item){
            $item->afterGet();
        });
        
        return $items;
    }
    
    /**
     * Find all objects where a single field meets the requirements
     *
     * This would not be called directly ordinarily, instead call the "magic" method
     * as shown below.
     *
     * <b>Usage</b>:
     * \include orm_model.findAllBy.example.php
     *
     * \note There is a hook for all \e get methods named \c afterGet(), to allow actions
     *      to be performed when an object is fetched (as opposed to the constructor, 
     *      which is called both when a new object is created or fetched).
     * 
     * 
     * @todo if operator is IN then allow $value to be an array
     * @todo verify operator type
     *
     * @param string $field
     *      The field name to search on
     * @param string $value
     *      The value to compare for finding objects
     * @param string $operator
     *      [optional] A valid SQL comparison operator (Default =)
     * @param string|array $findWith
     *      [optional] A string or array of strings defining the related model
     *      names to also fetch (see \ref intro_step3 "Define Foreign Keys")
     * @return ModelCollection
     *      Return the ModelCollection of objects
     */
    public static function FindAllBy( $field, $value, $operator = '=', $findWith = false ) {
        $df         = static::DataFactory();
        $className  = static::ClassName();
        $sql        = static::_BuildSQL(
            array( 'where' =>  "`$className`.`$field` $operator :$field" ),
            static::TableName(),
            $findWith
        );
        
        $query = $df::Get( $sql, static::DatabaseConfigName(), get_called_class() );

        $query->bindParam( ":$field", $value );
        $query->execute();

        $items = $query->fetchAllInto( get_called_class() );
        $items->each(function($item){
            $item->afterGet();
        });
        
        return $items;
    }
    
    /**
     * Get the number of records that exist matching the specified options
     * 
     * @see FindAll(), CountFindAllBy()
     * @param array $optionsArray 
     *      [optional] An array of options for finding objects. If not supplied, this will
     *      return all objects of this type stored in the database. For more
     *      information about the options array, see \ref intro_step4_options "Options Array"
     *      in the \ref getting_started "Getting Started" tutorial
     * @return int
     *      The total number of values that match this query
     */
    public static function CountFindAll( $optionsArray = array() ) {
        $df     = static::DataFactory();
        $sql    = static::_BuildSQL( $optionsArray, static::TableName(), false, self::QUERY_COUNT_ONLY );
        $query  = $df::Get( $sql, static::DatabaseConfigName(), get_called_class() );
        
        $query->execute( isset($optionsArray['values']) ? $optionsArray['values'] : null);

        $result = $query->fetch();
        return $result[0];
    }
    
    /**
     * Get the number of records that exist matching the specified options
     * 
     * @see FindAllBy(), CountFindAll()
     * @param string $field
     *      The field name to search on
     * @param string $value
     *      The value to compare for finding objects
     * @param string $operator
     *      [optional] A valid SQL comparison operator (Default =)
     * @return int
     *      The total number of values that match this query
     */
    public static function CountFindAllBy( $field, $value, $operator = '=' ) {
        $df         = static::DataFactory();
        $className  = static::ClassName();
        $sql        = static::_BuildSQL(
            array( 'where' =>  "`$field` $operator :$field" ),
            static::TableName(),
            false,
            self::QUERY_COUNT_ONLY
        );
        
        $query = $df::Get( $sql, static::DatabaseConfigName(), get_called_class() );

        $query->bindParam( ":$field", $value );

        $query->execute();

        $result = $query->fetch();
        return $result[0];
    }
    
    /**
     * Allow for specialty "FindBy" functions
     *
     * Objects can retrieved using (for example)
     *
     * @code
     * Car::FindByColour( 'red' );
     * Car::FindAllByColour( 'red' );
     * Car::FindAllByDoors( 3, '>=' );
     * @endcode
     *
     * @see FindBy(), FindAllBy(), CountFindAllBy()
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function  __callStatic($name, $arguments) {
        if( preg_match('/^FindBy(.+)$/', $name, $matches) ) {
            $findWith = isset($arguments[1]) ? $arguments[1] : false;

            return static::FindBy( self::_LowercaseFirst($matches[1]), $arguments[0], $findWith );

        } elseif( preg_match('/^FindAllBy(.+)$/', $name, $matches) ) {
            $findWith = isset( $arguments[2] ) ? $arguments[2] : false;
            $operator = isset( $arguments[1] ) ? $arguments[1] : '=';

            return static::FindAllBy( self::_LowercaseFirst($matches[1]), $arguments[0], $operator, $findWith );
            
        } elseif( preg_match('/^CountFindAllBy(.+)$/', $name, $matches) ) {
            $findWith = isset( $arguments[2] ) ? $arguments[2] : false;
            $operator = isset( $arguments[1] ) ? $arguments[1] : '=';
            
            return static::CountFindAllBy( self::_LowercaseFirst($matches[1]), $arguments[0], $operator );
        }
        
        throw new Exceptions\ORMException("Method $name does not exist");
    }

    /**
     * Convert the first character of a string to lowercase
     *
     * Used by __callStatic for FindByFieldName calls.
     * 
     * @param string $name
     *      The string to be changed
     * @return string
     *      With the first character changed to lowercase
     */
    private static function _LowercaseFirst( $name ) {
        $name{0} = strtolower( $name{0} );
        return $name;
    }

    /**
     * Find the first object where field equals value
     *
     * This would not be called directly ordinarily, instead call the "magic" method
     * as shown below. It is also called by Find(), which is a shortcut to \c FindById()
     * (assuming that the primary key is "id").
     *
     * <b>Usage</b>:
     * @code
     * // Get the first car owned by owner_id 4
     * $car = Car::FindByOwner_id( 4 );
     * @endcode
     * 
     * \note There is a hook for all \e get methods named \c afterGet(), to allow actions
     *      to be performed when an object is fetched (as opposed to the constructor, 
     *      which is called both when a new object is created or fetched).
     *
     * @param string $field
     *      The field name to search on
     * @param string $value
     *      The value to that field must contain
     * @param string|array $findWith
     *      [optional] A string or array of strings defining the related model
     *      names to also fetch (see \ref intro_step3 "Define Foreign Keys")
     * @return ORM_Model
     */
    public static function FindBy( $field, $value, $findWith = false ) {
        $df         = static::DataFactory();
        $tableName  = static::ClassName();
        $sql        = static::_BuildSQL(
            array( 'where' =>  "`$tableName`.`$field` = :$field" ),
            static::TableName(),
            $findWith
        );

        $query      = $df::Get( "$sql LIMIT 1", static::DatabaseConfigName(), get_called_class() );
        
        $query->bindValue( ":$field", $value );
        $query->execute();

        $item = $query->fetchInto( get_called_class() );
        if( $item ) { $item->afterGet(); }
        
        return $item;
    }

    /**
     * Find a single object using the supplied options array
     *
     * See the instructions for ORM_Model::Find()
     *
     * @param array $optionsArray
     *      Array of options. For more information about the options array, see
     *      \ref intro_step4_options "Options Array"  in the
     *      \ref getting_started "Getting Started" tutorial.
     * @param string|array $findWith
     *      [optional] A string or array of strings defining the related model
     *      names to also fetch (see \ref intro_step3 "Define Foreign Keys")
     * @return ORM_Model
     */
    public static function FindByOptions( $optionsArray, $findWith = false ) {
        $df     = static::DataFactory();
        $sql    = static::_BuildSQL( $optionsArray, static::TableName(), $findWith );
        $sql    .= "LIMIT 1";
        $query  = $df::Get( $sql, static::DatabaseConfigName(), get_called_class() );

        if( isset($optionsArray['values']) ) {
            $query->execute($optionsArray['values']);
        } else {
            $query->execute();
        }

        $item = $query->fetchInto( get_called_class() );
        if( $item ) { $item->afterGet(); }
        
        return $item;
    }

    /**
     * Helper function for creating SQL statements based on the options array
     *
     * @see FindByOptions(), FindAll(), Find()
     * @param array $optionsArray
     *      Array of options. For more information about the options array, see
     *      \ref intro_step4_options "Options Array" in the
     *      \ref getting_started "Getting Started" tutorial
     * @param string $table
     *      Table name
     * @param string|array $findWith
     *      [optional] A string or array of strings defining the related model
     *      names to also fetch (see \ref intro_step3 "Define Foreign Keys")
     * @param int $queryOption
     *      [optional] Special setting for changing the purpose of this query.
     *      Can either be QUERY_REGULAR or QUERY_COUNT_ONLY
     * @return string
     *      SQL Query string
     */
    protected static function _BuildSQL( $optionsArray, $table, $findWith = false, $queryOption = self::QUERY_REGULAR ) {
        $className  = static::ClassName();

        if( $queryOption == self::QUERY_COUNT_ONLY ) {
            $sql = "SELECT COUNT(*) FROM `$table` ";
        } else {
            $sql = $findWith ? 
                    static::_BuildSQLFindWith( $table, $findWith )
                    : "SELECT `$className`.* FROM `$table` AS `$className` ";
        }

        if( isset($optionsArray['where']) ) {
            $sql .= "WHERE {$optionsArray['where']} ";
        }

        if( isset($optionsArray['order']) ) {
            $sql .= "ORDER BY {$optionsArray['order']} ";
        }
        
        if( isset($optionsArray['limit']) ) {
            $offset = isset($optionsArray['offset']) ? $optionsArray['offset'] : 0;
            $sql .= "LIMIT $offset, {$optionsArray['limit']} ";
        }

        return $sql;
    }

    /**
     * Build the SELECT statement for a fetch request with specified foreign
     * keys.
     *
     * Called by _BuildSQL() if you specify $findWith
     *
     * @see _BuildSQL()
     * @param string $table
     *      Table name
     * @param string|array $findWith
     *      [optional] A string or array of strings defining the related model
     *      names to also fetch (see \ref intro_step3 "Define Foreign Keys")
     * @return string
     *      SQL Query
     */
    protected static function _BuildSQLFindWith( $table, $findWith ) {
        $className  = static::ClassName();
        $findWith   = (array)$findWith;
        $models     = array( "`$className`.*" );
        $joins      = '';

        foreach( $findWith as $nsFetchClass ) {
            $fetchTable = $nsFetchClass::TableName();
            $primaryKey = $nsFetchClass::PrimaryKeyName();
            $foreignKey = static::ForeignKey( $nsFetchClass );
            $fetchClass = basename( str_replace('\\', '//', $nsFetchClass) );

            $models[]   = "`$fetchClass`.*";
            $joins      .= "LEFT JOIN `$fetchTable` AS `$fetchClass` "
                        . "ON (`$className`.$foreignKey = `$fetchClass`.$primaryKey) ";
        }

        return 'SELECT '.implode(', ', $models)." FROM `$table` AS `$className` $joins";
    }

    /**
     * Delete a single item from the database without having to fetch it first
     *
     * \note This function is faster than ORM_Model::delete() because it does
     *       not fetch the object first, however as a result it does not call
     *       beforeDelete().
     *
     * @see delete()
     * @param string $id
     *      The primary key value specifying which item to delete
     */
    public static function Destroy( $id ) {
        $tableName  = static::TableName();
        $key        = static::PrimaryKeyName();
        $df         = static::DataFactory();
        $query      = $df::Get(
                "DELETE FROM `$tableName` WHERE `$key` = :id",
                static::DatabaseConfigName(), get_called_class()
        );

        $query->bindParam( ':id', $id );
        $query->execute();
    }

    /**
     * Delete a single item from the database
     *
     * The object will remain populated. Calls beforeDelete() before the object
     * is removed.
     *
     * \include orm_model.delete.example.php
     *
     *
     */
    public function delete() {
        $this->beforeDelete();

        $tableName  = static::TableName();
        $key        = static::PrimaryKeyName();
        $df         = static::DataFactory();
        $query      = $df::Get(
                "DELETE FROM `$tableName` WHERE `$key` = :id",
                static::DatabaseConfigName(), get_called_class()
        );

        $query->bindParam( ':id', $this->_id );
        $query->execute();
    }

    /**
     * A pre-delete hook
     *
     * This method is called before an object is deleted (only if it is called
     * using delete(), not Destroy()
     */
    public function beforeDelete() {}

    /**
     * Save an object to the database
     *
     * Either creates a new item or updates an existing one. First checks that
     * the object is valid (see valid()). Returns false if not valid and will
     * not attempt to save the object.
     *
     * <b>Usage:</b>
     * \include orm_model.save.example.php
     *
     * \note Changing the primary key on an existing object will create
     * a new record and leave the old one still in the database. If you want to
     * change the primary key, first call delete() then change the key and call
     * save()
     *
     * \n
     * <b>Pre- or Post-Processing Hooks</b>
     *
     * There are 6 pre and post processing hooks available for this method, to
     * allow for special processing to be done before or after saving. They are
     * called in the following order:
     *
     * - beforeSave()
     * \copydetails beforeSave()
     * - beforeUpdate()
     * \copydetails beforeUpdate()
     * - beforeCreate()
     * \copydetails beforeCreate()
     * - afterUpdate()
     * \copydetails afterUpdate()
     * - afterCreate()
     * \copydetails afterCreate()
     * - afterSave()
     * \copydetails afterSave()
     *
     * \n\n
     *
     * @see _update(), _create()
     *
     * @param boolean $forceCreate
     *      Skips the load test for speed, always resulting in a new object
     * @return boolean
     *      True if successful
     */
    public function save( $forceCreate = false) {
        $this->beforeSave();

        if( !$forceCreate && isset($this->_id) && $this->load() ) {
            $result = $this->_update();
        } else {
            $result = $this->_create();
        }

        if( $result ) $this->_originalValues = $this->values();

        return $result ? $this->afterSave() || true : false;
    }

    /**
     * Update an existing object
     *
     * <b>How this works:</b>
     * - call load() Compare to existing
     * - Generate query
     * - Bind params
     * - Execute
     *
     * @note If the key value is set but not record exists, this method will exit
     * and invoke _create() instead
     *
     * @see save()
     * @return boolean
     *      true on successful update
     */
    private function _update() {
        
        $this->beforeUpdate();

         if( !$this->valid() ) {
            return false;
        }
        
        $changedFields = $this->changedFields();

        if( count($changedFields) ) {
            $table  = static::TableName();
            $key    = static::PrimaryKeyName();
            $set    = array_map(function($field) {
                return "`$field` = :$field";
            }, $changedFields );
            
            $sql = "UPDATE `$table` SET ".implode( ', ', $set );
            $sql .= " WHERE `$key` = :$key";

            $changedFields[] = $key;
            $df              = static::DataFactory();
            $query = $df::Get( $sql, static::DatabaseConfigName(), get_called_class() )->bindObject($this, $changedFields);

            if( $query->execute() ) {
                $this->afterUpdate();

                return true;
            }
            
        } else {
            // No changes made
            return true;
        }
        
        return false;
    }

    /**
     * Create a new record in the database
     *
     * If the primary key is an \c autoincrement key and it was not specified
     * in the object, the assigned primary key will be retrieved and added to
     * the object.
     *
     * @code
     * $car = new Car();
     * // ... setup properties here
     *
     * $car->save();
     *
     * echo "Car saved with id: ", $car->id();
     * @endcode
     *
     * <b>How this works:</b>
     *
     * - Get field names from database
     * - Generate SQL
     * - Bind params
     * - Execute query
     *
     * @return boolean
     *      True on succesful update
     */
    private function _create() {
        $this->beforeCreate();

         if( !$this->valid() ) {
            return false;
        }

        $table  = static::TableName();
        $fields = $this->_includedFields( $this->DescribeTable() );
        $df     = static::DataFactory();

        $sql    = "INSERT INTO `$table` (`".implode('`, `',$fields)."`)";
        $sql    .= " VALUES ( :".implode(', :',$fields)." )";
        $query  = $df::Get( $sql, static::DatabaseConfigName(), get_called_class() )->bindObject($this, $fields);

        $result = $query->execute();
        
        if( $result ) {
            if( is_null($this->_id) ) {
                $this->_id = $df::LastInsertId();
            }

            $this->afterCreate();
            
            return true;
            
        } else {
            return false;
        }
    }

    /**
     * Filter field names to only include fields that are in this object
     * 
     * This allows for database defaults to work.
     *
     * @see _create(), DescribeTable()
     *
     * @param array $allFields
     *      Array of all field names (from ORM_Model::DescribeTable())
     * @return array
     *      Array of fields names representing each database field that is actually
     *      present in this object
     */
    private function _includedFields( $allFields ) {
        $me = $this;
        return array_filter($allFields, function( $field ) use( $me ){
            return isset($me->$field);
        });
    }

    /**
     * Get an array listing all the fields stored in the database for this object
     *
     * @return array
     *      An array of field names representing each database field
     */
    public static function DescribeTable() {
        $df         = static::DataFactory();
        $database   = $df::GetFactory( static::DatabaseConfigName() );
        
        return $database->fieldNames( static::TableName() );
        
        /*
         * try {
            return $database->fieldNames( static::TableName() );
            
        } catch( Exception $e ) {
            // just use the public properties
            $publicPropertiesFunction = (function() use( $item ) {
                $vars = get_object_vars($item);

                return array_keys($vars);
            });

            return $publicPropertiesFunction();
        }
         */
    }

    /**
     * Get the value of the primary key for this object
     *
     * @param mixed $value
     *      [optional] If specified, set the primary key value for this object
     * @return mixed
     */
    public function id( $value = null ) {
        if( !is_null($value) ) {
            $this->_id = $value;
        }
        
        return $this->_id;
    }

    /**
     * Validity check to be called before saving.
     *
     * Override this for individual models to check for validity. The default
     * implementation looks for error messages that have been set by validationError()
     * 
     * See \ref validation "Model Validation" for more information.
     *
     * @return boolean
     *      If this method returns false for a particular object, it is considered
     *      not in a "valid" state and so it cannot be saved using save()
     */
    public function valid() {
        return count($this->_errorMessages) == 0;
    }

    /**
     * Get the database configuration group name for this model
     *
     * See \ref intro_step2_advanced "Multiple Databases" for more information
     * 
     * @return string
     */
    public static function DatabaseConfigName() {
        return defined("static::DATABASE") ? static::DATABASE : PDOFactory::DEFAULT_DATABASE;
    }

    /**
     * Get the datafactory classname for this model
     *
     * Advanced feature that allows you to use an alternative to the PDOFactory
     * class. The class should implement the DataFactory interface.
     *
     * @return string
     *      Normally returns "PDOFactory"
     */
    public static function DataFactory() {
        return defined("static::DATAFACTORY") ?  __NAMESPACE__."\\".static::DATAFACTORY : __NAMESPACE__.'\PDOFactory';
    }

    /**
     * Get the table name for a given model
     * 
     * The table name is either guessed from the model name or explicitly defined
     * by creating a constant \c TABLE in the class.
     *
     * See \ref intro_step2 "Getting Started: 2. Define Model Classes" for more information
     *
     * @return string
     * @see _makeTableName()
     */
    public static function TableName() {
        return defined("static::TABLE") ? static::TABLE : self::_makeTableName(get_called_class());
    }

    /**
     * Get the primary key for a given table
     *
     * This is assumed to be <i>"id"</i> except for models where the constant \c PRIMARY_KEY
     * has been set.
     *
     * See \ref intro_step2 "Getting Started: 2. Define Model Classes" for more information
     * 
     * @return string
     */
    public static function PrimaryKeyName() {
        return defined("static::PRIMARY_KEY") ? static::PRIMARY_KEY : 'id';
    }

    /**
     * Get the name of the foreign key that relates another model to this one
     *
     * Defaults to <i>modelName</i>_<i>modelPrimaryKey</i> (lowercase). Can be
     * overriden by defining constant \c FOREIGN_KEY_<i>{modelName}</i> to the
     * foreign key name.
     *
     * See \ref intro_step2 "Getting Started: 2. Define Model Classes" for more information
     * 
     * @param string $modelName
     *      The name of the model to join to this one
     * @return string
     */
    public static function ForeignKey( $modelName ) {
        $model = basename( str_replace('\\', '//', $modelName) );

        $constName = "FOREIGN_KEY_".strtoupper( $model );
        $defaultFK = strtolower($model).'_'.$modelName::PrimaryKeyName();
        
        return defined("static::$constName") ? constant("static::$constName") : $defaultFK;
    }

    /**
     * Assume the name of the table from a given model name
     * 
     * Basically just a plural lowercase version of the model name
     *
     * @note Currently this only allows for some simple plurals ('ies' or 's')
     *
     * @see TableName()
     * @param string $model_name
     *      The name of the model.
     * @return string
     */
    private static function _makeTableName( $model_name ) {
        $class = strtolower( basename(str_replace( '\\', '/', $model_name)) );

        if( substr($class, -1) == 'y' ) {
            return substr($class, 0, -1)."ies";
        } else {
            return "{$class}s";
        }
    }

    /**
     * Get the base classname
     * 
     * @return string
     *      The class name that is calling this function, stripped of namespacing
     */
    public static function ClassName() {
        return basename(str_replace('\\', '//', get_called_class()));
    }

    /**
     * Get the possible values of an ENUM field
     *
     * \note Only works for MySQL ENUM datatypes
     *
     * This is useful for rapid development as it allows you to only change the
     * ENUM values at the SQL side, but it is going to be slightly slower and
     * cause higer database load. If using this in production cache the result.
     *
     * @param string $fieldName
     *      The name of the database field you wish to check. If it is not an
     *      ENUM field, an empty array will be returned.
     * @return array
     *      Array of strings containing the valid field values. Is indexed from 1
     *      to match the MYSQL index. Will be empty if it cannot determine values
     *      (ie it's not a MySQL table or it's not an ENUM field).
     */
    public static function PossibleValues( $fieldName ) {
        $query = PDOFactory::Get('SHOW COLUMNS FROM `' . static::TableName() . '` LIKE "' . $fieldName . '"');
        $query->execute();

        $results = $query->fetch();
        $fields  = array();

        if( preg_match_all('/\'(.*?)\'/', $results[1], $fieldsZeroIndexed) ) {
            // Convert to 1-indexed array
            $fields = array_combine(
                    range( 1 ,count($fieldsZeroIndexed[1])),
                    $fieldsZeroIndexed[1]
            );
        }

        return $fields;
    }

    /**
     * Load the rest of the details into a partial model
     *
     * The _originalValues array will be updated to reflect the values retrieved
     * from the database.
     *
     * <b>Usage</b>
     * \include orm_model.load.example.php
     *
     * @note If no there is no pre-existing object, nothing is loaded
     * 
     * @todo Throw an exception if this is called on an object with no _id
     *
     * @return boolean
     *      true if an existing object existed
     */
    public function load() {
        if( $stored_object = static::Find( $this->_id ) ) {
            $attributes = $stored_object->attributes();

            foreach( $attributes as $attribute ) {
                if( !isset($this->$attribute) ) {
                    $this->$attribute = $stored_object->$attribute;
                }

                $this->setOriginalValue($attribute, $stored_object->$attribute);
            }

            return true;
        }

        return false;
    }

    /**
     * For debugging, return the model name and primary key value when casting
     * an ORM_Model object to string
     * 
     * @return string
     */
    public function __toString() {
        return get_called_class()." [{$this->id()}]";
    }

    /**
     * Override this method to perform an action \e before an object is saved to
     * the database (creating and updating).
     *
     * @note This is called \e before valid(), meaning it is always called when
     * saving
     */
    public function beforeSave() {}

    /**
     * Override this method to perform an action \e before an existing object is
     * updated in the database.
     *
     * @note This is called \e before valid(), meaning it is always called when
     * updating
     */
    public function beforeUpdate() {}

    /**
     * Override this method to perform an action \e before a new object is
     * created in the database.
     *
     * @note This is called \e before valid(), meaning it is always called when
     * creating
     */
    public function beforeCreate() {}

    /**
     * Override this method to perform an \e after before a new object is
     * created in the database. Only called on success. The object will have its
     * $_id field populated.
     */
    public function afterCreate() {}

    /**
     * Override this method to perform an action \e after an existing object is
     * updated in the database. Only called on success.
     */
    public function afterUpdate() {}

    /**
     * Override this method to perform an action \e after an object is saved to
     * the database (creating and updating). Only called on success.
     */
    public function afterSave() {}
    
    /**
     * Override this method to perform an action \e after an object is retrieved
     * from the database (Find(), FindAll(), etc).
     * 
     * Called after the constructor and with all the data populated.
     * 
     * <b>Example</b>
     * 
     * \include orm_model.serialised.example.php
     */
    public function afterGet() {}
}
