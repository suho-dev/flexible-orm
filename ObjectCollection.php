<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM;

/**
 * Represents an array of anything
 *
 * Provides a more Object-Oriented interface for standard PHP functions like
 * \c array_map() and \c array_filter()
 * 
 * Also allows you to access items from the object as an array (ArrayAccess).
 * \n\n
 * 
 * <b>Useful Methods:</b>
 * 
 * Array access:
 * @code
 * $collection = new ObjectCollection(array(
 *      'my'    => 'collection',
 *      'size'  => 'small'
 * ));
 * 
 * foreach( $collection as $key => $value ) {
 *      echo "Key: $key, Value: $value \n";
 * }
 * 
 * echo "Size: ".$collection['size'];
 * @endcode
 * \n\n
 * ObjectCollection::each()
 * \copydetails each
 * \n\n
 * ObjectCollection::select()
 * \copydetails select
 * \n\n
 * ObjectCollection::map()
 * \copydetails map
 */
class ObjectCollection extends \ArrayObject {
    /**
     * Reverse the collection
     *
     * @return void
     */
    public function reverse() {
        $this->exchangeArray( array_reverse( $this->getArrayCopy() ) );
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
        array_walk( $this, $function, $this );
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
     * @see each(), select(), _autoMap()
     * @param sring $functionOrProperty
     *      Either an object property to map (see _autoMap()) or a function to 
     *      apply to each element. The function may either be an anonymous function
     *      or a function name (will behave exactly like calling array_map() on
     *      an array.
     * @return array
     */
    public function map( $functionOrProperty ) {
        if ( is_callable($functionOrProperty) ) {
            return array_map( $functionOrProperty, $this->getArrayCopy() );
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
     * Removes the last added element. Not very efficient, since it has to make
     * a copy of the array (since there's no access to the ArrayObject internal
     * array).
     * 
     * @return mixed
     */
    public function pop() {
        $newValues  = $this->getArrayCopy();
        $output     = array_pop( $newValues );
        $this->exchangeArray( $newValues );
        
        return $output;
    }

    /**
     * Return a subset of a collection based on a condition
     *
     * May be used in two ways:
     * - Selecting by property
     * - Selecting by function
     * 
     * Array keys are lost on the selection.
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
        if ( is_callable($propertyOrFunction) ) {
            $selected = $this->_selectByFunction( $propertyOrFunction );
        } else {
            $selected = $this->_selectByProperty( $propertyOrFunction, $value );
        }
        
        $class = get_class($this);

        return new $class( array_values($selected) );
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
        return array_filter( $this->toArray(), $function );
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
            $this->toArray(),
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
        foreach( $this as $item ) {
            if ( call_user_func( $function, $item ) ) {
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
        return $this->getArrayCopy();
    }
    
    /**
     * Reduce this collection to a single value
     * 
     * Works exactly the same as array_reduce().
     * 
     * <b>Usage Example:</b>
     * @code
     * // Get the Result object with the highest total from a collection of
     * // Result objects
     * $highest = $results->reduce(function($best, $current){
     *      return $current->total > $best->total ? $current : $best;
     * }, new Result() );
     * 
     * // Get the sum of all results
     * $sum = $results->reduce(function($sum, $current){
     *      return $sum + $current->total;
     * });
     * @endcode
     * 
     * @param string $function
     *      Either an anonymous function (closure) or a function name. The function
     *      should accept two parameters, the first being the current reduced value
     *      (which starts at $initial) and the second is the current element in
     *      the collection.
     * @param mixed $initial
     *      [Optional] Initial value. Defaults to null
     * @return mixed
     */
    public function reduce( $function, $initial = null ) {
        return array_reduce( $this->toArray(), $function, $initial );
    }
}
