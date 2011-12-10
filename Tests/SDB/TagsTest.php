<?php
namespace ORM\SDB;
use \ORM\Tests\Mock;

require_once '../ORMTest.php';
$sdb = SDBStatement::GetSDBConnection();
$sdb->create_domain('sources');

/**
 * Description of TagsTest
 *
 * @author jarrodswift
 */
class TagsTest extends \ORM\Tests\ORMTest {
    /**
     * @var AmazonSDB $sdb 
     */
    protected $sdb;
    
    public function setUp() {
        $this->sdb = SDBStatement::GetSDBConnection();
    }
    
    public function testGetTags() {
        $sdb    = $this->sdb;
        $tags   = array('test', 'edmonton', 'get');
        $id     = time();
        $name   = "My Test ".rand();
        
        $sdb->put_attributes('sources', $id, array(
            'description'   => $name,
            'tags'          => $tags
        ));
        
        $statement  = SDBFactory::Get( "SELECT * FROM sources WHERE itemName() = \"$id\"" );
        $statement->setConsistentRead(true);
        $statement->execute();
        $source     = $statement->fetchInto('\ORM\Tests\Mock\Source');
        
        $this->assertEquals( $name, $source->description );
        $this->assertInstanceOf('\ORM\Tests\Mock\Source', $source);
        $this->assertEquals($tags, $source->tags );
    }
    
    public function testSaveTags() {
        $tags = array( 'test', 'save' );
        
        $source = new Mock\Source;
        $source->description = "Saved";
        $source->tags        = $tags;
        $this->assertTrue( $source->save() );
        $id     = $source->id();
         
        $this->assertEquals( $tags, $source->tags );
       
        $statement  = SDBFactory::Get( "SELECT * FROM sources WHERE itemName() = \"$id\"" );
        $statement->execute();
        $savedSource = $statement->fetchInto('\ORM\Tests\Mock\Source');
        
        $this->assertEquals( $tags, $savedSource->tags );
    }
    
    public function testAlterTags() {
        $this->markTestIncomplete();
    }
    
    public function __destruct() {
        $this->sdb->delete_domain('sources');
    }
}
