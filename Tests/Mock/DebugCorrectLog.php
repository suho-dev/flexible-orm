<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace FlexibleORMTests\Mock;

/**
 * A mock debug log class that uses SDB to store logs
 *
 * @author jarrod.swift
 */
class DebugCorrectLog extends \ORM\SDB\ORMModelSDB implements \ORM\Interfaces\DebugLog {
    public $timestamp;
    public $serializedObject;
    
    /**
     * Store object for later analysis
     * @param mixed $object 
     */
    public function store( $object ) {
        $this->object($object);
        $this->timestamp = time();
        $this->save();
    }
    
    /**
     * Get or set the object for this log
     * 
     * Serializes (and unserializes) the object, so this can be any serializable
     * object.
     * 
     * @param mixed $object
     *      [optional]any serializable object.
     * @return mixed
     *      The object 
     */
    public function object( $object = null ) {
        if ( !is_null($object) ) {
            $this->serializedObject = serialize($object);
        }
        
        return unserialize($this->serializedObject);
    }
}
DebugCorrectLog::CreateDomain();
?>
