<?php
/**
 * @file
 * @author jarrod.swift
 * @version 2.0
 */
namespace ORM;

/**
 * Simple autoloading class for ORM
 * 
 * Rules followed:
 * - If there is no namespace for the class, then it will try to \c require
 *  the file with \c EXTENSION in both lower and uppercase
 * - If it cannot find a file, does nothing (meaning you just add to the autoload
 *  stack if you need a more complex loader)
 * - If the namespace begins with a package name that is known to the class
 *  (see below) then it will attempt to load it from the specified location
 * - Case must either match exactly or be all lowercase
 *
 * Also includes a Zend style loader (\c AUTOLOAD_STYLE_ZEND) which searches the include path
 * for the class, replacing underscores with "/".
 *
 * <b>Changes</b>
 * \note    Version 2: You now need to explicitly call register() to use this class automatically
 * \note    Version 2: To make it more flexible, the AutoLoader no longer automatically uses the Configuration class
 *          to load package locations.
 * 
 * 
 * \n
 * \n<b>Packages</b>
 * To autoload packages, supply the Autoloader object with an associative array, array keys are package names
 * and values are path names.
 * 
 * <b>Usage</b>
 * The following is the recommended method of boot-strapping the autoloader for flexible-orm
 * use.
 * 
 * In the application configuration, you can include a "packages" group which
 * identifies where the autoloader should look for classes. For example:
 *
 * @code
 * #INI File "my-application.ini"
 * [packages]
 * MyNamespace  = "/path/to/my/namespace"
 * APlugin      = "/path/to/plugins/aplugin"
 * @endcode
 *
 * So if you asked for MyNamespace\User, it would look in /path/to/my/namespace/User.
 * 
 * @code
 * // -- PHP Implementation
 * use \ORM;
 * use \ORM\Utilities\Configuration;
 * 
 * require 'flexible-orm/AutoLoader.php';
 * 
 * $loader = new AutoLoader();
 * $loader->register();
 * 
 * Configuration::Load('my-application.ini');
 * $loader->setPackageLocations( Configuration::packages()->toArray() );
 * @endcode
 *
 * For more information see the \ref autoloader_tut "Autoloader Tutorial".
 */
class AutoLoader {
    /**
     * The extensions to add to the class name for finding class files
     */
    const EXTENSION = '.php';
    
    /**
     * Load only using the package format of flexible-orm (namespaces are folders)
     * @see register()
     */
    const AUTOLOAD_STYLE_FORM = 1;
    
    /**
     * Load classes only using the Zend Framework 1 format of underscores are
     * folders.
     * 
     * \note Using this method will mean AutoLoader cannot load other flexible-orm
     *       classes
     * 
     * @see register()
     */
    const AUTOLOAD_STYLE_ZEND = 2;
    
    /**
     * Use both the Zend Framework and the flexible-orm formats.
     * @see register()
     */
    const AUTOLOAD_STYLE_BOTH = 3;
    
    /**
     * Array of namespaces and the directories to find these namespaces in
     * @var array $_packageLocations
     */
    protected $_packageLocations = array();

    /**
     * Set the known packages and create an AutoLoader
     * 
     * If no location is set for \c ORM, this will automatically assume the current
     * directory is the location for the \c ORM package. This can be overriden in
     * setPackageLocations().
     * 
     * @param array $packages 
     *      [optional] Array where keys are package names and values are locations to find
     *      the class files for these packages
     * 
     * @see setPackageLocations()
     */
    public function __construct( array $packages = array() ) {
        $this->setPackageLocations($packages);
    }
    
    /**
     * Get the list of known packages and their locations
     * @return array
     */
    public function getPackageLocations() {
        return $this->_packageLocations;
    }
    
    /**
     * Set the list of known packages and their locations
     * 
     * \note If no location is set for \c ORM, this will automatically assume the current
     *       directory is the location for the \c ORM package. This can be overriden by including
     *       an array key named ORM.
     * 
     * @param array $packages 
     */
    public function setPackageLocations( array $packages ) {
        $this->_packageLocations = $packages;
        
        if( !array_key_exists(__NAMESPACE__ ,$this->_packageLocations) ) {
            $this->_packageLocations[__NAMESPACE__] = __DIR__;
        }
    }

    /**
     * Load the class files if they can be found
     *
     * \note This is the function that should be registered with spl_autoload_register()
     *
     * \copydoc AutoLoader
     *
     * @param string $class
     */
    public function load( $class ) {
        $pathName = $this->locate($class);
        
        if ( $pathName && file_exists($pathName) ) {
            require $pathName;
        }
    }

    /**
     * An alternative loader that loads classes using the Zend method
     * where underscores are folder names
     *
     * @param string $class
     */
    public function loadZend( $class ) {
        $pathName = str_replace( array('_', '\\'), '/', $class );

        if ( $this->_locateInIncludePath($pathName) ) {
            require "$pathName.php";
        }
    }

    /**
     * Locate where the files would be if this autoloader could locate them
     *
     * @see load()
     * @param string $class
     * @return string
     */
    public function locate( $class ) {
        if ( $this->_rootNamespace( $class ) == '.' ) {
            return $this->_setToLowerCaseIfRequired($class.self::EXTENSION);
        }

        $packageLocation = $this->locatePackage($class);

        return $packageLocation ? 
                $this->_setToLowerCaseIfRequired($packageLocation.self::EXTENSION)
                : $this->_locateInIncludePath( $class );
    }

    /**
     * Get the path of a package
     *
     * @param string $class
     *      Class name to use as package name locate
     * @return string|false
     *      Returns path or false if unknown package
     */
    public function locatePackage( $class ) {
        foreach ( $this->_packageLocations as $package => $path ) {
            if ( preg_match("/^\\\?$package\\\(.*)$/", $class, $matches ) ) {
                return $path.'/'.str_replace('\\', '/', $matches[1]);
            }
        }

        return false;
    }

    /**
     * Get the root namespace of a class name
     *
     * If the class is not namespaced, it will be '.'
     * 
     * @param string $class
     * @return string
     */
    private function _rootNamespace( $class ) {
        return dirname(str_replace( '\\', '/', $class));
    }

    /**
     * Locate a file using the include path as it was not in the known package
     * locations
     *
     * @param string $class
     * @return string|false
     */
    private function _locateInIncludePath( $class ) {
        $paths      = explode( PATH_SEPARATOR, get_include_path() );
        $classPath  = str_replace('\\', '/', $class );

        foreach ( $paths as $path ) {
            $fullPath = "$path/$classPath.php";
            if ( file_exists($fullPath) ) {
                return $fullPath;
            }
        }

        return false;
    }

    /**
     * Checks if the file name with match case exists, if it does not then
     * return a lowercase version.
     *
     * \note This mainly exists for backwards compatibility. File and pathnames
     *      should match the case of the namespaces and classes. This function
     *      has no effect on windows machines.
     * 
     * @param string $pathName
     * @return string
     */
    private function _setToLowerCaseIfRequired( $pathName ) {
        if ( file_exists($pathName) ) {
            return $pathName;
        } else {
            return strtolower($pathName);
        }
    }
    
    /**
     * Register this autoloader
     * 
     * @param int $loaderType 
     *      [optional] Which autoloader styles to register. May be either AUTOLOAD_STYLE_FORM,
     *      AUTOLOAD_STYLE_ZEND or AUTOLOAD_STYLE_BOTH. Default is both.
     */
    public function register( $loaderType = self::AUTOLOAD_STYLE_BOTH ) {
        $autoloader = $this;
        if( $loaderType == self::AUTOLOAD_STYLE_BOTH || $loaderType == self::AUTOLOAD_STYLE_FORM ) {
            spl_autoload_register(function($class) use($autoloader) {
                $autoloader->load($class);
            });
        }
        
        if( $loaderType == self::AUTOLOAD_STYLE_BOTH || $loaderType == self::AUTOLOAD_STYLE_ZEND ) {
            spl_autoload_register(function($class) use($autoloader) {
                $autoloader->loadZend($class);
            });
        }
    }
}
