<?php
/**
 * @file
 * @author jarrod.swift
 */
/**
 * Contains all defined flexible-orm interfaces
 */
namespace ORM\Interfaces;
/**
 * Very simple cache interface, the main purpose of which is to allow simple switching
 * between alternative caching methods, such as APCCache and Memcached.
 *
 * To add custom caching abilities that will work with the ORM, simply implement
 * this interface in your new class.
 *
 * <b>Add</b>
 *
 * \copydoc add()
 *
 * <b>Set</b>
 *
 * \copydoc set()
 *
 * <b>Get</b>
 *
 * \copydoc get()
 *
 * <b>Delete</b>
 *
 * \copydoc delete()
 * 
 */
interface Cache {
    /**
     * Save an object, replacing the existing cache entry (if
     * it exists)
     *
     * This should be used when editing values and recaching (not when
     * retrieving items from the database to read)
     *
     * <b>Usage</b>
     * @code
     * function save_item( $item ) {
     *      $cache = new APCcache();
     *      $cache->set( $item->id, $item, 60 );
     *      $item->save();
     * }
     * @endcode
     * 
     * @see get(), add()
     * @return void
     * @param string $name		Cache id
     * @param object $object	Object to store
     * @param int $ttl	 		Seconds until cache expires (0 == never)
     */
    public function set( $name, $object, $ttl = 0 );

    /**
     * Retrieve an object from cache, returning false if it does not
     * exist
     *
     * <b>Usage</b>
     * @code
     * function get_item( $item_id ) {
     *      $cache = new Cache();
     *      if( $item = $cache->get( $item_id ) ) {
     *          return $item;
     *      } else {
     *          $item = get_item_from_database( $item_id );
     *          $cache->add( $item_id );
     *          return $item;
     *      }
     * }
     * @endcode
     *
     * @return mixed
     * @param string $name
     *      Unique identifier for this item in cache
     */
    public function get( $name );

    /**
     * Add a value to cache if the key does not exist
     *
     * This should be used when retrieving a value from the database to store.
     * When editing a value, set() should be used to prevent race condition problems.
     *
     * <b>Usage</b>
     * @code
     * function get_item( $item_id ) {
     *      $cache = new Cache();
     *      if( $item = $cache->get( $item_id ) ) {
     *          return $item;
     *      } else {
     *          $item = get_item_from_database( $item_id );
     *          $cache->add( $item_id );
     *          return $item;
     *      }
     * }
     * @endcode
     *
     * @see set(), get()
     * @return void
     * @param string $name
     *      Unique identifier for this item in cache
     * @param mixed $object
     *      The object to store in cache
     * @param int $ttl
     *      [optional] Seconds until cache expires (0 == never). Default = 0
     */
    public function add( $name, $object, $ttl = 0 );

    /**
     * Clear the cache
     *
     * @see delete()
     * @return void
     */
    public function flush();

    /**
     * Delete a particular cached item
     *
     * <b>Usage</b>:
     * @code
     * function delete_item( $id ) {
     *      delete_from_db( $id );
     *      $cache = new Cache();
     *      $cache->delete( $id );
     *      // $cache->get( $id ) will now return false
     * }
     * @endcode
     *
     * @see flush()
     * @return void
     * @param string $name
     *      Unique identifier for this item in cache
     */
    public function delete( $name );
}
