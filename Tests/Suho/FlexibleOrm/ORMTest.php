<?php
/**
 * @file
 * @author jarrod.swift
 */
/**
 * PHPUnit tests for flexible-orm
 */
namespace Tests\Suho\FlexibleOrm;

use Suho\FlexibleOrm\AutoLoader;
use Suho\FlexibleOrm\Utilities\Configuration;
use PHPUnit_Framework_TestCase;

error_reporting(E_ALL);

//require_once 'AWSSDKforPHP/sdk.class.php';

/**
 * Description of ORMTestClass
 *
 */
class ORMTest extends PHPUnit_Framework_TestCase {
    /**
     * Path to the top level of the test folder
     * @var string $pathToTestRoot
     */
    protected $pathToTestRoot;
    
    /**
     * Full path to the class files in the FlexibleOrm namespace
     * @var string $pathToFlexibleOrm
     */
    protected $pathToFlexibleOrm;
    
    public function __construct($name = NULL, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        
        $this->pathToTestRoot       = realpath(__DIR__.'/../..');
        $this->pathToFlexibleOrm    = realpath( "$this->pathToTestRoot/../suho/FlexibleOrm" );
        
        require_once "$this->pathToFlexibleOrm/AutoLoader.php";
        
        $loader = new AutoLoader();
        $loader->setPackageLocations(array('Tests' => $this->pathToTestRoot));
        $loader->register(AutoLoader::AUTOLOAD_STYLE_FORM);

        Configuration::Load("$this->pathToTestRoot/test.ini");
        Configuration::SetCacheClass('\ORM\Utilities\Cache\APCCache');
    }
}
