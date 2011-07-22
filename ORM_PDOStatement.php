<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM;

/**
 * Custom PDOStatement class allowing better integration with ORM_Model
 *
 * Allows a single row to be mapped to multiple objects (using fetchInto())
 * or a group of rows to be mapped to multiple objects in a ModelCollection
 * (using fetchAllInto()).
 *
 * Also contains a convenience method for mapping a series of Object properties
 * to a prepared statement (using bindObject()).
 *
 * This class can be return instead of PDOStatement by setting the attribute
 * PDO::ATTR_STATEMENT_CLASS to this class name (as it is in PDOFactory).
 */
class ORM_PDOStatement extends \PDOStatement implements Interfaces\DataStatement {
    /**
     * Constructor for ORM_PDOStatement
     *
     * Currently this does not do anything, but it is required for using this
     * as a PDO::ATTR_STATEMENT_CLASS
     */
    protected function __construct() {
    }
    
    /**
     * For simplicity, the fetch method has fewer options when using the DataStatement
     * interface
     *
     * @param int $fetch_style
     * @return mixed
     */
    public function fetch( $fetch_style = \PDO::FETCH_BOTH ) {
        return parent::fetch( $fetch_style );
    }

    /**
     * Return an object representing the result of the database query
     * 
     * Note: Will only fetch the first result. For returning a group of objects
     * use fetchAllInto().
     *
     * This will only work when the SQL query is formatted correctly. Each table
     * must be aliased (or have the same name as) the model class. For example,
     * the following SQL would attempt to put the result into an object called Car.
     * @code
     * SELECT Car.* FROM cars AS Car LIMIT 1;
     * @endcode
     *
     * The more complex query below would populate a class "Car" and give it a
     * property "Owner" which would be an Owner object:
     * @code
     * $sql   = "SELECT Car.*, Owner.* FROM cars AS Car LEFT JOIN owners AS Owner ON Car.owner_id = Owner.id";
     * $query = $db->prepare( $sql );
     *
     * $car = $query->fetchInto( 'Car' );
     * echo $car->Owner->name; // The name of this car's owner
     * @endcode
     *
     * @see fetchAllInto(), ORM_Model::_buildSQL();
     * @throws ORMFetchIntoClassNotFoundException
     *      if $className does not exist
     *
     * @throws ORMFetchIntoRelatedClassNotFoundException
     *      when a reultset includes a table alias that cannot be converted to
     *      an existing class name
     * @param string $className
     *      The object class name that will form the base object
     * @return
     *      An object of type $className
     */
    public function fetchInto( $className ) {
        return $this->_getObject($className, $this->_getQualifiedColumnNames());
    }
    
    /**
     * Get a collection of objects representing a number of rows in the database
     * 
     * @see fetchInto()
     * @throws ORMFetchIntoClassNotFoundException
     *      if $className does not exist
     *
     * @throws ORMFetchIntoRelatedClassNotFoundException
     *      when a reultset includes a table alias that cannot be converted to
     *      an existing class name
     * @param string $className
     *      The object class name that will form the base object
     * @return ModelCollection
     *      A ModelCollection of objects of type $className
     */
    public function fetchAllInto( $className ) {
        $qualifiedColumnNames = $this->_getQualifiedColumnNames();
        $objects    = new ModelCollection();
        $class      = basename(str_replace('\\','/', $className));

        while( $object = $this->_getObject($className, $qualifiedColumnNames) ) {
            $objects[] = $object;
        }
        
        return $objects;
    }

    /**
     * Get an array of strings detailing the return column names
     * 
     * This is neccasary when fetching from multiple tables with duplicated field 
     * names (e.g. User.id and Computer.id).
     *
     * \note Not all PDO drivers support PDOStatement::getColumnMeta()
     *
     * \note <b>From PHP Documentation:</b> This function is EXPERIMENTAL. The behaviour of this function,
     * its name, and surrounding documentation may change without notice in a future
     * release of PHP. This function should be used at your own risk.
     *
     * @todo there needs to be an alternative method for databases that do not support this
     *      functionality
     * 
     * @return array
     *      Array of field names, each qualified by table name (aliased table
     *      name if there is one).
     */
    private function _getQualifiedColumnNames() {
        $qualifiedNames = array();

        for ($i = 0; $i < $this->columnCount(); $i++) {
            $meta               = $this->getColumnMeta($i);
            $qualifiedNames[]   = isset($meta['table']) ? "{$meta['table']}.{$meta['name']}" : ".{$meta['name']}";
        }

        return $qualifiedNames;
    }
    
    /**
     * Get a single object of class '$className' from the database
     *
     * @see fetchInto()
     *
     * @throws Exceptions\ORMFetchIntoClassNotFoundException
     *      If the requested $className cannot be loaded
     * @throws Exceptions\ORMFetchIntoRelatedClassNotFoundException
     *      If a class within the query could not be resolved to a class name
     *
     * @param string $className
     *      Object class to fetch
     * @param array $qualifiedColumnNames
     *      Array of column names qualified by the classname they are to be mapped
     *      to.
     * @return
     *      Object of type $className. Returns false if no row to be fetched.
     */
    private function _getObject( $className, $qualifiedColumnNames ) {
        if ( !class_exists($className) ) {
            throw new Exceptions\ORMFetchIntoClassNotFoundException("Unknown class $className requested");
        }

        $row = $this->fetch( \PDO::FETCH_NUM );

        if ( $row ) {
            $classPath  = str_replace( '\\', '/', $className );// This is so basename/dirname work on *nix
            $results    = array_combine($qualifiedColumnNames, $row );
            $class      = basename($classPath);
            $object     = new $className;
            $namespace  = str_replace( '/', '\\', dirname( $classPath ) );

            foreach ( $results as $field => $value ) {
                list( $classDestination, $property ) = explode( '.', $field, 2 );

                if ( $classDestination == '' || $class == $classDestination ) {
                    // This is the requested (base) class
                    $object->$property = $value;
                    $object->setOriginalValue($property, $value);
                    
                } else {
                    // This is not the requested class, rather it's a subclass
                    // belonging to the $classDestination class
                    
                    if ( !isset($object->$classDestination) ) {
                        // Create new object if there isn't one for this $class
                        $fullClassName = "$namespace\\$classDestination";
                        if ( !class_exists($fullClassName) ) {
                            throw new Exceptions\ORMFetchIntoRelatedClassNotFoundException("Unknown class $fullClassName in '{$this}'");
                        }

                        $object->$classDestination = new $fullClassName;
                    }

                    // Set the value $class property
                    $object->$classDestination->$property = $value;
                }
            }

            return $object;
        } else {
            return false;
        }
    }

    /**
     * Bind an entire object to a prepared statement
     *
     * A convenient shortcut to looping through an array and binding a series
     * of properties of an object to a prepared statement. The placeholders
     * and strings of the $params array must be the same and all the names must
     * be available public properties of the supplied object.
     *
     * Alternatively you can omit the placeholders array and the method will
     * attempt to bind \e all placeholders to corresponding properties.
     * 
     * <b>Usage</b> (using the PDOFactory class)
     * @code
     * $car         = Car::FindByColour( 'red' );
     * $car->colour = 'blue';
     * $car->doors  = 8;
     *
     * $query = PDOFactory::Get(
     *      "UPDATE cars SET doors = :doors, colour = :colour WHERE id = :id"
     * );
     *
     * // This is only for example, as if you were actually using a ORM_Model
     * // subclass, you would only need to call $car->save();
     * $query->bindObject( $car, array('colour', 'doors', 'id') );
     * $query->execute();
     * @endcode
     *
     * @see ORM_Model::_update(), placeholders()
     * @param Object $object
     *      An object which must have public properties matching exactly all
     *      the strings in the $params array
     * @param array $params
     *      [optional] An indexed array of strings that define which object properties
     *      will be bound to the prepared statement. They will all be bound to
     *      named placeholders that match their property name (see description).
     *      The order is not important. If it is not provided, the binds are
     *      determined from the stored placeholders.
     * @return ORM_PDOStatement
     *      Returns this instance of ORM_PDOStatement for convenience
     */
    public function bindObject( $object, array $params = null ) {
        $me     = $this; //!<- This is needed for the anonymous function, which cannot use $this
        $params = is_null($params) ? $this->placeholders() : $params;

        array_walk( $params, function($param) use( $object, $me ){
            $me->bindParam( ":$param", $object->$param );
        });

        return $this;
    }

    /**
     * Get the placeholder names from this prepared statement
     *
     * <b>Example:</b>
     * @code
     * $query = PDOFactory::Get( 'UPDATE cars SET doors = :doors, colour = :colour WHERE id = :id' );
     *
     * print_r( $query->placeholders() );
     *
     * // Would output:
     * // Array
     * // (
     * //   [0] => doors
     * //   [1] => colour
     * //   [2] => id
     * // )
     * @endcode
     *
     * @see This function is used by bindObject() if not param array is supplied
     * @return array
     *      Indexed array of placeholder names (without the preceding colon)
     */
    public function placeholders() {
        $pattern = '/(?<=:)([a-zA-Z0-9_]+(?![^\'"]*["\']))/';

        if ( strstr($this->queryString, "'") !== false || strstr($this->queryString, '"') !== false ) {
            $query = $this->_removeQuotedValues();
        } else {
            $query = $this->queryString;
        }

        if ( preg_match_all( $pattern, $query, $placeholders ) ) {
            return $placeholders[1];
        }

        return array();
    }

    /**
     * Helper method to remove values in quotes from the query string
     *
     * This allows simple regex search for placeholders.
     *
     * <b>Example</b>
     * @code
     * // This
     * "INSERT INTO owners (name, age) VALUES ('This is my :name and not yours', :age)"
     *
     *
     * // Would become
     * INSERT INTO owners (name, age) VALUES (, :age)
     *
     * // Meaning that it could be parsed to find placeholders (:age)
     * @endcode
     *
     * @return string
     */
    private function _removeQuotedValues() {
        $quotedToken = strtok( $this->queryString, '"\'' );
        $output      = '';
        $count       = 1;
        
        while( $quotedToken !== false ) {
            if ( $count++ % 2 ) {
                $output .= $quotedToken;
            }
            $quotedToken = strtok( '"\'' );
        }

        return $output;
    }

    /**
     * Output the prepared SQL when casting to string
     * 
     * @return string
     */
    public function __toString() {
        return $this->queryString;
    }

    /**
     * Override the execute function to add some custom error handling
     *
     * @throws Exceptions\ORMFindByInvalidFieldException if there is an invalid
     *      field in the SQL
     *
     * @throws Exceptions\ORMPDOException for any other exceptions
     *
     * @param array $input
     *      [optional] An array of values with as many elements as there are bound
     *      parameters in the SQL statement being executed. See PDOStatement->execute()
     * @return bool
     */
    public function execute( array $input = null) {
        try{
            return parent::execute($input);

        } catch( \PDOException $e ) {
            if ( strpos( $e->getMessage(), 'Column not found') !== false ) {
                throw new Exceptions\ORMFindByInvalidFieldException( $e->getMessage() );
            } else {
                throw new Exceptions\ORMPDOException( $e->getMessage() );
            }
        }

        return false;
    }
}
