<?php
namespace ORM;

require 'Utilities/Configuration.php';
require 'Utilities/ConfigurationGroup.php';

/**
 * Simple autoloading class for ORM
 *
 * Rules followed:
 * - If there is no namespace for the class, then it will try to \c require
 *  the file with \c EXTENSION in both lower and uppercase
 * - If it cannot find a file, does nothing (meaning you just add to the autoload
 *  stack if you need a more complex loader)
 * - If the namespace begins with a package name that is recorded in the Configuration
 *  (see below) then it will attempt to load it from the specified location
 * - Case must either match exactly or be all lowercase
 *
 * Also includes a Zend style loader (loadZend) which searches the include path
 * for the class, replacing underscores with "/".
 *
 * \n<b>Packages</b>
 *
 * In the application configuration, you can include a "packages" group which
 * identifies where the autoloader should look for classes. For example:
 *
 * @code
 * #INI File
 * [packages]
 * ORM          = "/path/to/orm"
 * MyNamespace  = "/path/to/my/namespace"
 * @endcode
 *
 * This INI file defines that if the root namespace of a class is \c ORM, then
 * look in \c /path/to/orm. It will then use further namespaces as folders within
 * this path. For example it would look for <code>ORM\\Mock\\Car</code> in
 * <code>/path/to/orm/Mock/Car.php</code> (or <code>/path/to/orm/mock/car.php</code>
 * if the former does not exist).
 *
 * For more information see the \ref autoloader_tut "Autoloader Tutorial".
 */
class AutoLoader {
    /**
     * The extensions to add to the class name for finding class files
     */
    const EXTENSION = '.php';

    /**
     * @var AutoLoader $_autoLoader
     */
    private static $_autoLoader;

    /**
     * AutoLoader is a singleton class
     *
     * @see Get()
     */
    private function __construct() {}

    /**
     * Get the AutoLoader instance
     * 
     * @return AutoLoader
     */
    public static function Get() {
        if( is_null(self::$_autoLoader) ) {
            self::$_autoLoader = new Autoloader();
        }

        return self::$_autoLoader;
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
        
        if( $pathName && file_exists($pathName) ) {
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
        $pathName = str_replace( array('_', '\\'),'/', $class );

        if( $this->_locateInIncludePath($pathName) ) {
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
        if( $this->_rootNamespace( $class ) == '.' ) {
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
     * @todo test this
     * @param string $class
     *      Class name to use as package name locate
     * @return string|false
     *      Returns path or false if unknown package
     */
    public function locatePackage( $class ) {
        $packages = Utilities\Configuration::packages();

        foreach( $packages as $package => $path ) {
            if( preg_match("/^\\\?$package\\\(.*)$/", $class, $matches ) ) {
                return $path.'/'.str_replace('\\','/', $matches[1]);
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

        foreach( $paths as $path ) {
            $fullPath = "$path/$classPath.php";
            if( file_exists($fullPath) ) {
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
        if( file_exists($pathName) ) {
            return $pathName;
        } else {
            return strtolower($pathName);
        }
    }

    /**
     * Register the autoloaders
     */
    public static function RegisterAutoloaders() {
        spl_autoload_register(function($class){
            AutoLoader::Get()->load($class);
        });

        spl_autoload_register(function($class){
            AutoLoader::Get()->loadZend($class);
        });
    }
}

AutoLoader::RegisterAutoloaders();

?>
