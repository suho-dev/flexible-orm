<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Tests;
use \ORM\SDB;
require_once 'ORMTest.php';

/**
 * Description of SDBSessionHandlerTest
 *
 */
class SDBSessionHandlerTest extends ORMTest{
    public $session_id;
    private $_savedData = array(
        'some' => array('saved', 'data')
    );

    public function setUp() {
        SDB\SDBStatement::GetSDBConnection()->create_domain(SDB\SDBSession::TableName());

        // Setup a fake session
        $this->session_id   = "My-test-".rand();
        $data               = serialize($this->_savedData);
        $session            = new SDB\SDBSession();
        $session->data      = $data;
        $session->id( $this->session_id );
        $session->save();
    }

    public function tearDown() {
        SDB\SDBStatement::GetSDBConnection()->delete_domain(SDB\SDBSession::TableName());
    }

    public function testRegister() {
        $this->assertTrue( SDB\SDBSessionHandler::Register() );
    }

    public function testWrite() {
        $data = array('my' => 'data', 'for' => 'testing' );
        $id   = "testWrite-".rand();
        SDB\SDBSessionHandler::Get()->write($id, serialize($data) );

        $session = SDB\SDBSession::Find($id);

        $this->assertEquals( serialize($data), $session->data );
    }

    public function testRead() {
        $serializedData = SDB\SDBSessionHandler::Get()->read($this->session_id);
        $this->assertEquals($this->_savedData, unserialize($serializedData));
    }

    public function testDestroy() {
        SDB\SDBSessionHandler::Get()->destroy($this->session_id);

        $this->assertFalse( SDB\SDBSession::Find($this->session_id), 'Found session that should be deleted' );
    }

    public function testRepeatWrites() {
        $data = array('my' => 'data', 'for' => 'testing' );
        $id   = "testRepeatWrites-".rand();
        SDB\SDBSessionHandler::Get()->write($id, serialize($data));

        $data[] = array('more', 'stuff');
        SDB\SDBSessionHandler::Get()->write($id, serialize($data) );

        $serializedData = SDB\SDBSessionHandler::Get()->read($id);
        $this->assertEquals($data, unserialize($serializedData));

        $data = 'none';
        SDB\SDBSessionHandler::Get()->write($id, serialize($data));
        $serializedData = SDB\SDBSessionHandler::Get()->read($id);
        $this->assertEquals($data, unserialize($serializedData));
    }
}
?>
