<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Tests\Controller;
use ORM\Controller\Session;
use ORM\Tests\ORMTest;
use ORM\Tests\Mock\MockSessionWrapper;

require_once '../ORMTest.php';

/**
 * Test the Session class using the MockSessionWrapper
 */
class SessionTest extends ORMTest {
    /**
     * @var MockSessionWrapper $sessionWrapper
     */
    protected $sessionWrapper;
    
    public function setUp() {
        $this->sessionWrapper = new MockSessionWrapper(array(
            Session::FIELD_NAME => array(
                'user'          => 'jarrod',
                'loginCount'    => 10
            )
        ));
    }
    
    public function testGetSession() {
        $session = Session::GetSession( false, $this->sessionWrapper );
        
        $this->assertInstanceOf( 'ORM\Controller\Session', $session );
        $this->assertFalse( $session->isLocked() );
        
        $session2 = Session::GetSession( false, $this->sessionWrapper );
        $this->assertEquals( spl_object_hash($session), spl_object_hash($session2) );
        
        $session3 = Session::GetSession( true, $this->sessionWrapper );
        $this->assertTrue( $session3->isLocked() );
    }
    
    public function testLockAndUnlock() {
        $session = Session::GetSession( false, $this->sessionWrapper );
        
        $this->assertFalse( $session->isLocked() );
        $session->lock();
        $this->assertTrue( $session->isLocked() );
        $session->unlock();
        $this->assertFalse( $session->isLocked() );
    }
    
    public function testGet() {
        $session = Session::GetSession( false, $this->sessionWrapper );
        
        $this->assertEquals( 'jarrod', $session->user );
        
        $this->assertNull( $session->name );
    }
    
    public function testSet() {
        $session = Session::GetSession( true, $this->sessionWrapper );
        
        $session->loginCount++;
        $this->assertEquals( 11, $session->loginCount );
        $session->unlock();
        
        $this->assertFalse( $session->isLocked() );
        $this->assertEquals( 11, $session->loginCount );
    }
    
    /**
     * Should raise exception due to not being locked
     * 
     * @expectedException LogicException
     */
    public function testIllegalSet() {
        $session = Session::GetSession( false, $this->sessionWrapper );
        
        $session->loginCount++;
    }
    
    public function testDestroy() {
        $session = Session::GetSession( false, $this->sessionWrapper );
        $session->destroySession();
        $this->assertNull( $session->user );
    }
    
    public function testMockSessionWrapper() {
        $wrapper = new MockSessionWrapper(array('x' => 1, 'y' => 2));
        
        $wrapper->start();
        $this->assertEquals( 1, $wrapper['x'] );
        
        $wrapper['z'] = 3;
        
        $this->assertEquals( 3, $wrapper['z'] );
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testMockSessionWrapperIncorrectLocking() {
        $wrapper = new MockSessionWrapper(array('x' => 1, 'y' => 2));
        $this->assertEquals( 1, $wrapper['x'] );
    }
}