<?php
namespace ORM\SDB;

use PHPUnit_Framework_TestCase;

use \FlexibleORMTests\Mock;

$sdb = SDBStatement::GetSDBConnection();
$sdb->create_domain('sources');

/**
 * Description of TagsTest
 *
 * @author jarrodswift
 */
class TagsTest extends PHPUnit_Framework_TestCase {
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
        $source     = $statement->fetchInto('\FlexibleORMTests\Mock\Source');
        
        $this->assertEquals( $name, $source->description );
        $this->assertInstanceOf('\FlexibleORMTests\Mock\Source', $source);
        
        $this->assertEquals( count($tags), count($source->tags), "Tags array wrong length");
        foreach( $tags as $tag ) {
            $this->assertContains($tag, $source->tags );
        }
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
        $savedSource = $statement->fetchInto('\FlexibleORMTests\Mock\Source');
        
        $this->assertEquals( $tags, $savedSource->tags );
    }
    
    public function testAlterTags() {
        $source = Mock\Source::Find();
        
        $source->tags[] = 'added tag';
        $source->save();
        
        $savedSource = Mock\Source::Find( $source->id() );
        $this->assertEquals( count($source->tags), count($savedSource->tags), 'Tag count wrong' );
        $this->assertContains( 'added tag', $source->tags );
        
        foreach( $source->tags as $tag ) {
            $this->assertContains($tag, $savedSource->tags, "Tag $tag missing from saved source" );
        }
    }
    
    public function testRemoveTag() {
        $source = Mock\Source::Find();
        array_pop($source->tags);
        
        $source->save();
        
        $savedSource = Mock\Source::Find( $source->id() );
        $this->assertEquals( count($source->tags), count($savedSource->tags), 'Tag count wrong' );
        
        foreach( $source->tags as $tag ) {
            $this->assertContains($tag, $savedSource->tags, "Tag $tag missing from saved source" );
        }
    }    
    public function __destruct() {
        $this->sdb->delete_domain('sources');
    }
}
