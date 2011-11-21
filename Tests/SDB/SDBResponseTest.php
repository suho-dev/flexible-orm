<?php
namespace ORM\SDB;
use \ORM\Tests\Mock, \ORM\SDB\SDBResponse;

require_once '../ORMTest.php';


class SDBResponseTest extends \ORM\Tests\ORMTest {
    /**
     * @var AmazonSDB $object
     */
    protected $object;

    const DOMAIN = 'testORM';

    private $_testItems = array(
            'item1' => array('test' => 'value'),
            'item2' => array('test' => 'value2',    'another' => 'test'),
            'item3' => array('test' => 'value2',    'name'    => 'jarrod'),
        );

    protected function setUp(){
        $this->object = new \AmazonSDB();
        $this->object->set_response_class('\ORM\SDB\SDBResponse');
        $this->object->set_region(\AmazonSDB::REGION_APAC_SE1);
        
        $this->object->create_domain(self::DOMAIN);
        $this->object->batch_put_attributes(self::DOMAIN, $this->_testItems);
    }

    protected function tearDown() {
//        $this->object->delete_domain('bigDomain');
    }

    public function testReturnsSDBResponse() {
        $this->assertEquals( 'ORM\SDB\SDBResponse', get_class($this->object->list_domains() ) );
    }

    public function testSelect() {
        $result = $this->object->select('SELECT * FROM '.self::DOMAIN);

        $this->assertEquals( 3, count($result) );

        $this->assertEquals( array_keys($this->_testItems), $result->itemNames() );
        $this->assertEquals( $this->_testItems['item1'], $result['item1'] );
        $this->assertEquals( $this->_testItems['item2'], $result['item2'] );
        $this->assertEquals( $this->_testItems['item3'], $result['item3'] );
    }

    public function testGetAttributes() {
        $result = $this->object->get_attributes(self::DOMAIN, 'item2');
        $this->assertEquals($this->_testItems['item2']['test'], $result['test'] );
        $this->assertEquals($this->_testItems['item2']['another'], $result['another'] );
    }

    public function testGetAttributesNoResult() {
        $result = $this->object->get_attributes(self::DOMAIN, 'item100');
        $this->assertEquals( 0, count($result) );
    }

    public function testSelectNoResult() {
        $result = $this->object->select('SELECT * FROM '.self::DOMAIN .' WHERE x = "hello"');
        $this->assertEquals( 0, count($result) );
    }

    public function testGetAll() {
        // Make lots of items
//        $this->object->create_domain( 'bigDomain' );
//        $count = 0;
//        for( $j = 1; $j <= 8; $j++ ) {
//            $items = array();
//            for( $i = 1; $i <= 25; $i++ ) {
//                $items['item'.$i*$j] = array('test' => rand(1,100));
//                $count++;
//            }
//
//            $response = $this->object->batch_put_attributes( 'bigDomain', $items, true);
//
//            if ( !$response->isOK() ) {
//                echo "\nInsert failed:\n";
//                print_r($response);
//            }
//        }

        $result = $this->object->select('SELECT * FROM bigDomain', array('ConsistentRead' => 'true'))->getAll(true);

        $this->assertGreaterThan( 100, count($result) );

    }

    public function testGetQuery() {
        $results = $this->object->select('SELECT * FROM bigDomain');
        $this->assertEquals('SELECT * FROM bigDomain', $results->getQuery() );
    }
    
    public function testErrorMessage() {
        $results = $this->object->select('SELECT * FROM nonexistant');
        
        $this->assertFalse( $results->isOK(), "Response was OK, when it shouldn't be!" );
        
        $this->assertEquals( $results->errorMessage(), "The specified domain does not exist.");
    }
}
?>