<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM;
/**
 * Description of CachedORMModel
 *
 * An ORM Model class that implements caching on Find/FindAll actions and keeps
 * the cache updated on save() actions.
 *
 * This is mainly useful when the database storing the object is on a different
 * server from the application. If the database is on the same server then the
 * caching within the database should be sufficient.
 *
 * \note Use of this class is identical to ORM_Model.
 */
class CachedORMModel extends ORM_Model {
    /**
     * @var Cache $_cache
     */
    private static $_cache;

    /**
     * Length of time (in seconds) for cache objects to remain valid
     */
    const CACHE_TTL = 180;

    /**
     * Wrap the Find() method with caching
     *
     * Identical to ORM_Model::Find() except it caches results.
     *
     * @param string|array $idOrArray
     *      See ORM_Model::Find()
     * @param string|array $findWith
     *      [optional] Model name (or array of model names) to also fetch into this object
     * @return CachedORMModel|false
     */
    public static function Find( $idOrArray = array(), $findWith = false ) {
        if( is_array($idOrArray) ) {
            $object = static::FindByOptions( $idOrArray, $findWith );
            static::_AddToCache( $object, $findWith );

        } else {
            $object = static::RetrieveFromCache( $idOrArray, $findWith );

            if( !$object ) {
                $object = static::FindBy( static::PrimaryKeyName(), $idOrArray, $findWith );
                static::_AddToCache( $object, $findWith );
            }
        }

        return $object;
    }

    /**
     * Cache an object and any related models that were found with it
     *
     * @param CachedORMModel $fetchedObject
     *      An object that you wish to cache.
     * @param string|array $findWith
     *      [optional] Model name (or array of model names) that were fetched
     *      with this object. They will be cached independantly from the primary
     *      object.
     */
    protected static function _AddToCache( $fetchedObject, $findWith ) {
        if( $fetchedObject ) {
            $object = clone $fetchedObject;
            
            if( $findWith ) {
                $findWithArray = (array)$findWith;
                foreach( $findWithArray as $nsFetchClass ) {
                    $fetchClass = basename( $nsFetchClass );
                    self::_cache()->add( (string)$object->$fetchClass, $object->$fetchClass, self::CACHE_TTL );
                    unset($object->$fetchClass);
                }
            }

            self::_cache()->add( (string)$object, $object, self::CACHE_TTL );
        }
    }

    /**
     * Get an object from cache if it exists
     *
     * Optionally can \e findWith other classes (see ORM_Model::Find()). These
     * objects will be either fetched from cache or fetched from the database.
     *
     * @param string $id
     *      Primary key value to retrieve
     * @param string|array $findWith
     *      [optional] Model name (or array of model names) to also fetch into this object.
     *      They may not be cached, in which case the Find() action will be called.
     * @return CachedORMModel
     *      If the object exists in cache, return a CachedORMModel subclass. If
     *      it does not exist, return false.
     */
    public static function RetrieveFromCache( $id, $findWith = false ) {
        $object = self::_cache()->get( static::CacheId($id) );

        if( $object && $findWith ) {
            $findWithArray = (array)$findWith;
            foreach( $findWithArray as $nsFetchClass ) {
                $fetchClass     = basename( $nsFetchClass );
                $foreignKey     = static::ForeignKey( $nsFetchClass );
                $id             = $object->$foreignKey;

                // Either fetch from cache or get it from the database
                $relatedObject  = self::_cache()->get( $nsFetchClass::CacheId( $id ) );
                $object->$fetchClass = $relatedObject ?: $nsFetchClass::Find( $id );
            }
        }

        return $object;
    }

    /**
     * Cached object save
     *
     * See ORM_Model::save()
     *
     * @return boolean
     *      True if successful
     */
    public function save() {
        $result = parent::save();

        if( $result ) {
            $this->_cache()->set( (string)$this, $this, self::CACHE_TTL );
        }

        return $result;
    }

    /**
     * Delete object and remove it from the Cache
     *
     * @see ORM_Model::delete()
     */
    public function delete() {
        $this->_cache()->delete( (string)$this );
        parent::delete();
    }

    /**
     * Delete object and remove it from the Cache
     *
     * @see ORM_Model::Destroy()
     * @param string $id
     *      Model primary key value
     */
    public static function Destroy( $id ) {
        $this->_cache()->delete( static::CacheId($id) );
        parent::Destroy($id);
    }

    /**
     * Get the cache object
     * 
     * @return APCCache
     *      Can return any Cache class that implements Cache or a Memcache object
     */
    private function _cache() {
        if( is_null(self::$_cache) ) {
            self::$_cache = new Utilities\Cache\APCCache();
        }

        return self::$_cache;
    }

    /**
     * Get a string to identify an object in cache
     *
     * @param string $id
     *      Model primary key value
     * @return string
     */
    public static function CacheId( $id ) {
        return get_called_class()." [$id]";
    }
}
?>
