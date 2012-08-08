<?php
/**
 * Tests for the Session class
 * 
 * This test needs to be run with the --stderr switch for PHPUnit
 * 
 * @file
 * @author jarrod.swift
 */
namespace ORM\Controller;
use FlexibleORMTests\ORMTest;
use FlexibleORMTests\Mock\MockSessionWrapper;
use FlexibleORMTests\Mock\SessionMock as Session;


session_start('ORM_DEFAULT');
session_write_close();

require_once '../ORMTest.php';

/**
 * Test the Session class using the MockSessionWrapper
 */
class SessionTest extends \FlexibleORMTests\ORMTest {
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
    
    public function tearDown() {
        Session::Clear();
    }
    
    public function testGetSession() {
        $session = Session::GetSession( $this->sessionWrapper );
        
        $this->assertInstanceOf( 'ORM\Controller\Session', $session );
        $this->assertFalse( $session->isLocked() );
        
        $session2 = Session::GetSession( $this->sessionWrapper );
        $this->assertEquals( spl_object_hash($session), spl_object_hash($session2) );
    }
    
    public function testLockAndUnlock() {
        $session = Session::GetSession( $this->sessionWrapper );
        
        $session->name;
        $this->assertFalse( $session->isLocked() );
        $session->lock();
        $this->assertTrue( $session->isLocked() );
        $session->unlock();
        $this->assertFalse( $session->isLocked() );
        
        $session->lock();
        $this->assertTrue( $session->isLocked() );
        $session->lock();
        $this->assertTrue( $session->isLocked() );
        $session->unlock();
        $this->assertTrue( $session->isLocked() );
        $session->unlock();
        $this->assertFalse( $session->isLocked() );
    }
    
    public function testLockAndUnlockData() {
        $session = Session::GetSession( $this->sessionWrapper );
        
        
        $session->lock();
        $session->name = 'Jarrod';
        $this->assertEquals( 'Jarrod', $session->name );
        $session->unlock();
        $this->assertEquals( 'Jarrod', $session->name );
        $session->lock();
        $this->assertEquals( 'Jarrod', $session->name );
        
        $session->unlock();
    }
    
    public function testGet() {
        $session = Session::GetSession( $this->sessionWrapper );
        
        $this->assertEquals( 'jarrod', $session->user );
        
        $this->assertNull( $session->name );
    }
    
    public function testSet() {
        $session = Session::GetSession( $this->sessionWrapper );
        $session->lock();
        
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
        $session = Session::GetSession( $this->sessionWrapper );
        
        $session->loginCount++;
    }
    
    public function testDestroy() {
        $session = Session::GetSession( $this->sessionWrapper );
        $session->destroySession();
        $this->assertNull( $session->user );
    }
    
    /**
     * @expectedException LogicException
     */
    public function testUnlockTwice() {
        $session = Session::GetSession( $this->sessionWrapper );
        
        $session->loginCount++;
        $this->assertEquals( 11, $session->loginCount );
        $session->unlock();
        
        $this->loginCount++;
        $session->unlock();  
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
    
    public function testUnlockWithStackIndex() {
        $session = Session::GetSession( $this->sessionWrapper );
        
        $i = $session->lock();
        
        $this->assertTrue( $session->unlock($i) ); 
        
        $i = $session->lock();
        $j = $session->lock();
        
        $this->assertFalse( $session->unlock($j) ); 
        $this->assertTrue( $session->unlock($i) ); 
    }
    
    public function testUnlockWithIncorrectStackIndex() {
        $session = Session::GetSession( $this->sessionWrapper );
        
        $i = $session->lock();
        $j = $session->lock();
        
        try {
            $session->unlock($i);
            $this->assertTrue( false );
        } catch( \ORM\Exceptions\IncorrectSessionLockIndexException $e ) {
            $this->assertTrue( true );
        }
        
        while( !$session->unlock() ) {};
    }
    
    /**
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testFailToUnlock() {
        $session = Session::GetSession( $this->sessionWrapper );
        
        $i = $session->lock();
        unset( $session );
        Session::Clear();
    }
    
    /**
     * @expectedException BadMethodCallException
     */
    public function testClone() {
        $session = Session::GetSession( $this->sessionWrapper );
        
        $clonedSession = clone $session;
    }
    
    /**
     * @expectedException BadMethodCallException
     */
    public function testGetSessionDifferentWrapper() {
        $session = Session::GetSession( $this->sessionWrapper );
        $session = Session::GetSession( new MockSessionWrapper() );
    }
    
    public function testWithActualSession() {
        Session::Clear();
        $session = Session::GetSession();
        $this->assertNull($session->name);
        
        $session->lock();
        $session->name = 'jarrod';
        $this->assertTrue( $session->unlock() );
        
        $this->assertEquals( 'jarrod', $session->name );
    }

    public function testWithActualSessionLockAndUnlockTwice() {
        Session::Clear();
        $session = Session::GetSession();
        $number = rand();

        // The set-up
        $session->lock();
        $session->number = $number;
        $this->assertTrue( $session->unlock() );

        // Ensure it variable is correct after first unlock
        $this->assertEquals( $number, $session->number, "number incorrect after first unlock" );

        // Ensure it variable is correct after subsequent lock
        $session->lock();
        $this->assertEquals( $number, $session->number, "number incorrect after re-locking"  );

        // Ensure it variable is correct after subsequent un-lock
        $this->assertTrue( $session->unlock() );
        $this->assertEquals( $number, $session->number, "number incorrect after second unlock"  );
    }

    /**
     * @todo implement test for regenerateSessionId
     */
    public function testRegenerateSessionId() {
        $this->markTestIncomplete();
    }
}