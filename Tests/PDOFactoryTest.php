<?php
namespace FlexibleORMTests;

use ORM\PDOFactory;

require_once 'ORMTest.php';

class PDOFactoryTest extends ORMTest {
    public function setUp() {
        PDOFactory::Get("INSERT INTO `cars` (`id`, `brand`, `colour`, `doors`, `owner_id`, `name`, `age`, `type`) VALUES
            (1, 'Alfa Romeo', 'red', 4, 1, '156Ti', 4, 'Sedan'),
            (2, 'Volkswagen', 'black', 5, 1, NULL, 0, NULL),
            (3, 'Volkswagen', 'black', 2, 2, NULL, 0, NULL),
            (4, 'Toyota', 'White', 4, 2, NULL, 62, NULL)")->execute();
    }
    
    public function tearDown() {
        PDOFactory::Get("TRUNCATE TABLE `cars`")->execute();
    }
    
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
            "SELECT Owner.*, NoClass.* FROM owners AS Owner, cars AS NoClass WHERE NoClass.owner_id = Owner.id"
        );

        $query->execute();
        
        $owner = $query->fetchInto('\FlexibleORMTests\Mock\Owner');
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