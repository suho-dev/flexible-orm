<?php
namespace ORM\Tests;
use \ORM\Tests\Mock,    \ORM\Utilities\Debug;

require_once 'ORMTest.php';

/**
 * Description of DebugTest
 *
 * @author jarrod.swift
 */
class DebugTest extends ORMTest {
    public function testSetDebugStore() {
        Debug::SetLogStore(__NAMESPACE__.'\Mock\DebugCorrectLog');
        
        $this->assertTrue(true, "Exception not raised");
    }
    
    /**
     * @expectedException Exception
     */
    public function testInvalidDebugStore() {
        Debug::SetLogStore(__NAMESPACE__.'\Mock\DebugTestLog');
    }
}

?>
