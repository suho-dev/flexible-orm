<?php
namespace ORM;
use \ORM\Tests\Mock, \ORM\PDOFactory;

require_once 'ORMTest.php';


class PDOFactoryTest extends Tests\ORMTest {
    public function testGet() {
        $cars = PDOFactory::Get('SELECT * FROM cars');
        $this->assertEquals( 'ORM\ORM_PDOStatement', get_class($cars), 'PDO Factory did not return a ORM_PDOStatement object' );
        $this->assertTrue( $cars === PDOFactory::Get('SELECT * FROM cars'), 'PDO Factory did not return the same ORM_PDOStatement object' );

        $elephants = PDOFactory::Get('SELECT * FROM elephants');
        $this->assertEquals( 'ORM\ORM_PDOStatement', get_class($cars), 'PDO Factory did not return a ORM_PDOStatement object' );
        $this->assertFalse( $cars === $elephants, 'PDO Factory did not create a new statement' );
    }

    public function testGetMultiple() {
        $carQuery       = PDOFactory::Get('SELECT * FROM cars WHERE id = :id');
        $carQuery->bindValue(':id', 2);
        $carQuery->execute();
        
        $secondQuery    = PDOFactory::Get('SELECT * FROM cars WHERE id = :id');
        $carQuery->bindValue(':id', 1);
        $carQuery->execute();

        $thirdQuery    = PDOFactory::Get('SELECT colour FROM cars WHERE id = :id');
        $thirdQuery->bindValue(':id', 1);
        $thirdQuery->execute();

        $this->assertEquals( $carQuery, $secondQuery );
        $this->assertNotEquals( $thirdQuery, $carQuery );
    }

    /**
     * FetchInto is extensively tested through ORM, but the exception is not
     *
     * @expectedException ORM\Exceptions\ORMFetchIntoClassNotFoundException
     */
    public function testFindIntoNoClass() {
        $query = PDOFactory::Get('SELECT * FROM cars WHERE id = :id');
        $query->execute();
        $query->fetchInto('noclass');
    }

    /**
     * @expectedException ORM\Exceptions\ORMFetchIntoRelatedClassNotFoundException
     */
    public function testFindWithInvalidForeignClass() {
        $query = PDOFactory::Get(
            "SELECT Owner.*, NoClass.* FROM owners AS Owner, cars AS NoClass WHERE Owner.name = :name"
        );

        $query->bindValue(':name', 'Jarrod');
        $query->execute();
        
        $query->fetchInto('\ORM\Tests\Mock\Owner');
    }
    
    /**
     * @expectedException ORM\Exceptions\ORMPDOInvalidDatabaseConfigurationException
     */
    public function testInvalidDetails() {
        $factory = PDOFactory::GetFactory('invalidDatabase');
    }
    
    /**
     * @expectedException ORM\Exceptions\ORMPDOInvalidDatabaseConfigurationException
     */
    public function testNoDetails() {
        $factory = PDOFactory::GetFactory('noDetails');
    }
    
    public function testGetType() {
        $factory = PDOFactory::GetFactory();
        
        $this->assertEquals( 'mysql', $factory->databaseType() );
    }
    
    /**
     * @todo implement test for DescribeField (valid)
     */
    public function testDescribeField() {
        $factory = PDOFactory::GetFactory();
        $this->assertEquals(
                'int(1)',
                $factory->describeField('cars', 'doors')
        );
    }
    
    /**
     * @expectedException \ORM\Exceptions\FieldDoesNotExistException
     */
    public function testDescribeUnknownField() {
        $factory = PDOFactory::GetFactory();
        $factory->describeField('cars', 'idontexist');
    }
}