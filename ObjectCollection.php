<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM;

/**
 * Description of ObjectCollection
 *
 * Provides a more Object-Oriented interface for standard PHP functions like
 * \c array_map() and \c array_filter()
 * \n\n
 * 
 * <b>Useful Methods:</b>
 * 
 * ObjectCollection::each()
 * \copydetails each
 * \n\n
 * ObjectCollection::select()
 * \copydetails select
 * \n\n
 * ObjectCollection::map()
 * \copydetails map
 */
class ObjectCollection implements \ArrayAccess, \Iterator, \Countable {
    /**
     * The array of items
     * @var array $_collection
     */
    protected $_collection;

    /**
     * Create a new ObjectCollection
     *
     * If arguments are supplied, they are added to the collection
     * in the order they are supplied. If only one paramater is given
     * and it is an array, add each element (convert array to ModelIterator)
     *
     * @param array $objects
     *      Array of objects to "convert" into an ObjectCollection. Array keys
     *      are removed (ie it will be zero indexed).
     * @return ModelIterator
     */
    public function __construct(array $objects = array()) {
        $this->_collection = array_values( $objects );
    }

    /**
     * Check to see whether an item exists matching offset
     *
     * Part of the ArrayAccess interface
     *
     * @param mixed $index
     * @return bool
     */
    public function offsetExists( $index ) {
        return array_key_exists( $index, $this->_collection );
    }

    /**
     * Return the object at a specific index
     *
     * Part of the ArrayAccess interface
     *
     * @return mixed
     * @param mixed $index
     */
    public function offsetGet( $index ) {
        return $this->_collection[$index];
    }

    /**
     * Set the value of a key pair
     *
     * Part of the ArrayAccess interface
     *
     * @return void
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet( $offset, $value ) {
        if( is_null($offset) ) {
            $this->_collection[] = $value;
        } else {
            $this->_collection[$offset] = $value;
        }
    }

    /**
     * Remove an item from the collection at specified index
     *
     * Part of the ArrayAccess interface
     * 
     * @return void
     * @param mixed $index
     */
    public function offsetUnset( $index ) {
        unset( $this->_collection[$index] );
    }

    /**
     * Reverse the collection
     *
     * @return void
     */
    public function reverse() {
        $this->_collection = array_reverse( $this->_collection );
    }

    /**
     * Get the element at the current pointer position
     *
     * Part of the Iterator interface
     *
     * @see next(), rewind()
     * @return mixed
     */
    public function current() {
        return current( $this->_collection );
    }

    /**
     * Return the key at the current position in the collection
     *
     * Part of the Iterator interface
     *
     * @see current()
     * @return mixed
     */
    public function key() {
        return key( $this->_collection );
    }

    /**
     * Get the next element and advance the current pointer position
     *
     * Part of the Iterator interface
     *
     * @see current(), rewind()
     * @return mixed
     */
    public function next() {
        next( $this->_collection );
    }

    /**
     * Rewind the pointer to the beginning of the collection
     *
     * Part of the Iterator interface
     *
     * @see current(), next()
     * @return mixed
     */
    public function rewind() {
        reset( $this->_collection );
    }

    /**
     * Check to see if the collection pointer is pointing at
     * a valid element.
     *
     * Part of the Iterator interface
     *
     * @see current()
     * @return bool
     */
    public function valid() {
        return (current($this->_collection) !== FALSE);
    }

    /**
     * Get the number of items in the collection
     *
     * Part of the Countable interface
     *
     * @return int
     */
    public function count() {
        return count( $this->_collection );
    }

    /**
     * Apply a function to each element in the collection
     *
     * Function is passed the current object
     *
     * <b>Usage:</b>
     * @code
     * // Assume $cars is an ObjectCollection and each object has a property "name"
     * $cars->each(function($car){
     *      echo $car->name;
     * });
     * @endcode
     *
     * @param string $function
     *      Function name or anonymous function to execute
     */
    public function each( $function ) {
        array_walk( $this->_collection, $function, $this );
    }

    /**
     * Use a function to map elements to an array
     *
     * Can either be supplied with a map function (like \c array_map()) or a string
     * representing a property name. Both of the following examples with have
     * the same output.
     *
     * <b>Example usage (with function)</b>:
     * @code
     * $ticket_names = $tickets->map(function( $ticket ){
     *      return $ticket->name;
     * });
     *
     * print_r( $ticket_names ); // Outputs array of names
     * @endcode
     * 
     * <b>Example usage (with property name)</b>:
     * @code
     * $ticket_names = $tickets->map( 'name' );
     *
     * print_r( $ticket_names ); // Outputs array of names
     * @endcode
     *
     *
     * @see each(), select(), auto_map()
     * @param sring $functionOrProperty
     *      Either an object property to map (see _autoMap()) or a function to 
     *      apply to each element. The function may either be an anonymous function
     *      or a function name (will behave exactly like calling array_map() on
     *      an array.
     * @return array
     */
    public function map( $functionOrProperty ) {
        if( is_callable($functionOrProperty) ) {
            return array_map( $functionOrProperty, $this->_collection );
        } else {
            return $this->_autoMap( $functionOrProperty );
        }
    }

    /***
     * Maps based on a property
     *
     * <b>Example usage</b>:
     * <code>
     *   $ticket_names = $tickets->map( 'name' );
     * </code>
     *
     * @see map()
     * 
     * @param string $property
     *      A property that exists in all items in the collection
     * @return array
     */
    private function _autoMap( $property ) {
        return $this->map(function($item) use ($property){
            return $item->$property;
        });
    }

    /**
     * Pop an element of the end of the collection
     *
     * Removes the last added element
     * @return mixed
     */
    public function pop() {
        return array_pop( $this->_collection );
    }

    /**
     * Return a subset of a collection based on a condition
     *
     * May be used in two ways:
     * - Selecting by property
     * - Selecting by function
     *
     * <b>Selecting by Property</b>
     *
     * This is the simplest method of selection. Provided a property name and value,
     * select() simply searches the collection for all cases the property is 
     * equal (\c ==) to the value.
     *
     * @code
     * $staff   = Staff::FindAll();
     *
     * $jons    = $staff->select( 'name', 'Jon' );
     * $steves  = $staff->select( 'name', 'Steve' );
     * @endcode
     *
     *
     * <b>Selecting by Function</b>
     *
     * Select when the function returns true. The function should take one paramater
     * which will be the item. This is the same as using PHP's \c array_filter() function
     *
     * @code
     * $staff   = Staff::FindAll();
     *
     * $youngStaff = $staff->select(function($staffMember){
     *      return $staffMember->age < 30;
     * });
     * @endcode
     *
     * <i>Note:</i>
     * - If you want to select all elements with a property set to null, you have
     * to use the function method.
     *
     * @param string $propertyOrFunction
     *      Either a property name that all object in the collection have to test
     *      against or a function to use for testing.
     * @param mixed $value
     *      [optional] When testing by property (rather than function) this is the
     *      value to test for. When the property equals this value the object will be
     *      included. If not supplied, tests against TRUE. This value is ignored
     *      if $propertyOrFunction is a function.
     * @return ModelIterator
     *      An ObjectCollection (or subclass of, depending on what this object
     *      is) containing the subset of selected items
     */
    public function select( $propertyOrFunction, $value = null ) {
        if( is_callable($propertyOrFunction) ) {
            $selected = $this->_selectByFunction( $propertyOrFunction );
        } else {
            $selected = $this->_selectByProperty( $propertyOrFunction, $value );
        }
        
        $class = get_class($this);

        return new $class( $selected );
    }

    /**
     * Select items from the collection when the supplied function is true
     * 
     * @see select()
     * @param string $function
     *      Callable function name or anonymous function. The function should take
     *      one parameter and return true when that parameter should be included
     *      in the returned array
     * @return array
     *      Array of selected items
     */
    private function _selectByFunction( $function ) {
        return array_filter( $this->_collection, $function );
    }

    /**
     * Shortcut method allowing selection of object when a single property equals
     * a specified value
     *
     * Easier than writing an anonymous function when making simple selects, such
     * as:
     * @code
     * $redCars = $cars->select( 'colour', 'red' );
     * @endcode
     *
     * @see select()
     * @param string $propertyName
     *      Name of the object property to test agains
     * @param mixed $value
     *      For each item in the collection, an item will be included in the output
     *      it the $propertyName property equals $value. If not supplied, item
     *      properties are tested against TRUE
     * @return array
     *      Array of selected items
     */
    private function _selectByProperty( $propertyName, $value ) {
        $value = is_null($value) ? true : $value;

        return array_filter(
            $this->_collection,
            function( $item ) use( $propertyName, $value ) {
                return $item->$propertyName == $value;
        });
    }

    /**
     * Detect whether a condition is true for any element in the collection
     *
     * Similar to select() except this exits as soon as the condition is met,
     * meaning it may be faster if you only want to know whether the condition
     * is met at all, not which items meet it.
     *
     * <b>Usage:</b>
     * @code
     * // Are there any staff older than 80 or younger than 12?
     * $labourProblems = $staff->detect(function($staff){
     *      return $staff->age < 12 || $staff->age > 80;
     * });
     * @endcode
     *
     * @see select()
     * @param string $function
     *      A function which takes one parameter (each element of the collection)
     *      and returns true if the condition has been detected
     * @return boolean
     *      True if at least one object returned true
     */
    public function detect( $function ) {
        foreach( $this->_collection as $item ) {
            if( call_user_func( $function, $item ) ) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Return the collection as an array
     * 
     * Needed for PHP functions that only work with arrays, not the array interfaces
     * 
     * @return array
     */
    public function toArray() {
        return $this->_collection;
    }
}
?>
