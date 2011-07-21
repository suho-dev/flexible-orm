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
    protected $testData; 
    
    public function setUp() {
        $this->testData = array(
            'me'    => rand(),
            'array' => array(
                'more' => 'jkljkljkljkljkljkljkljkljkljkljkljkljkljklbhsdfgkjsbhdfg'
            )
        );
    }
        
    
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
    
    public function testLogStore() {
        Debug::SetLogStore(__NAMESPACE__.'\Mock\DebugCorrectLog');
        Debug::SetDisplayOutput(false);
        
        Debug::Dump($this->testData);
        
        $log    = Debug::Get()->lastLogObject();
        $stored = Mock\DebugCorrectLog::Find($log->id());
        
        $this->assertEquals( $log->timestamp, $stored->timestamp );
        $this->assertEquals( $this->testData,     $stored->object() );
    }
    
    public function testOutputSuppression() {
        Debug::SetLogStore(null);
        Debug::SetDisplayOutput(false);
        
        ob_start();
        $output   = Debug::Dump($this->testData);
        $actual   = ob_get_flush();
        
        $this->assertEquals( '', $actual );
    }
    
    public function testOutput() {
        Debug::SetLogStore(null);
        Debug::SetDisplayOutput(true);
        
        ob_start();
        $expected = Debug::Dump($this->testData);
        $actual   = ob_get_clean();
        
        $this->assertEquals( $expected, $actual );
        $this->assertGreaterThan( 10, strlen($expected), "Output is too short!" );
    }
}

?>
