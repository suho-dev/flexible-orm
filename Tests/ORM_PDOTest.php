<?php
namespace ORM\Tests;
use \ORM\Tests\Mock, \ORM\PDOFactory;

require_once 'ORMTest.php';


class ORM_PDOTest extends ORMTest {
    private $_owner;
    
    protected function setUp() {
        
    }
    
    protected function tearDown() {
        if ( !is_null($this->_owner) ) {
            $this->_owner->delete();
        }

        $owners = Mock\Owner::FindAllByName('my:name')->delete();
    }

    /**
     * Test the bindObject without array option
     */
    public function testBindObject() {
        $this->_owner = new Mock\Owner();
        $this->_owner->name = 'Sam';
        $this->_owner->age  = 25;

        $query = PDOFactory::Get("INSERT INTO owners (name, age) VALUES (:name, :age)");
        $query->bindObject( $this->_owner );

        $query->execute();
        $this->_owner->id = PDOFactory::LastInsertId();

        $owner = Mock\Owner::Find( $this->_owner->id );

        $this->assertEquals( 'Sam', $owner->name );
    }

    public function testPlaceholders() {
        $query = PDOFactory::Get("INSERT INTO  `cars` (
            `id` ,
            `brand` ,
            `colour` ,
            `doors` ,
            `owner_id` ,
            `name` ,
            `age`
            ) VALUES (
            :id ,
            :brand ,
            :colour ,
            :doors ,
            :owner_id ,
            :name ,
            :age)");

        $expected = array('id', 'brand', 'colour', 'doors', 'owner_id', 'name', 'age');

        $this->assertEquals( $expected, $query->placeholders() );
    }

    /**
     * Test for the edge case that there is data that looks like a placeholder
     * but it is not
     */
    public function testPlaceholdersWithFixedData() {
        $expected   = array('age');

        $query      = PDOFactory::Get("INSERT INTO owners (name, age) VALUES (':name', :age)");
        $this->assertEquals( $expected, $query->placeholders(), "Failed on ':name'" );

        $query      = PDOFactory::Get("INSERT INTO owners (name, age) VALUES (':name is weird', :age)");
        $this->assertEquals( $expected, $query->placeholders(), "Failed on ':name is weird'" );

        $query      = PDOFactory::Get("INSERT INTO owners (name, age) VALUES (\"my:name\", :age)");
        $this->assertEquals( $expected, $query->placeholders(), "Failed on \"my:name\"" );

        $query      = PDOFactory::Get("INSERT INTO owners (name, age) VALUES ('This is my :name and not yours', :age)");
        $this->assertEquals( $expected, $query->placeholders(), "Failed on 'This is my :name and not yours'" );

        $query      = PDOFactory::Get("INSERT INTO owners (age, name) VALUES (:age, 'This is my :name and not yours')");
        $this->assertEquals( $expected, $query->placeholders(), "Failed on 'This is my :name and not yours' as last parameter" );

        $query      = PDOFactory::Get("INSERT INTO  `cars` (
            `id` ,
            `brand` ,
            `colour` ,
            `doors` ,
            `owner_id` ,
            `name` ,
            `age`
            ) VALUES (
            :id ,
            \"My fancy brand\" ,
            :colour ,
            :doors ,
            :owner_id ,
            'peter',
            :age)");
        $expected = array('id', 'colour', 'doors', 'owner_id', 'age' );
        $this->assertEquals( $expected, $query->placeholders(), "Failed on complex multi-line, multiple quotes query" );
    }

}