<?php
namespace FlexibleORMTests;

use FlexibleORMTests\Mock\CachedCar;
use FlexibleORMTests\Mock\CachedElephant;
use FlexibleORMTests\Mock\CachedOwner;
use ORM\PDOFactory;
use ORM\Utilities\Cache\APCCache;
use Owner;

require_once 'ORMTest.php';
PDOFactory::GetFactory()->startProfiling();


/**
 * Test class for ORM_Model
 *
 * @note This tests that CachedORMModelTest behaves exactly as ORM_Model and that
 *      it caches objects correctly
 */
class CachedORMModelTest extends ORMTest {
    protected function tearDown() {
        $freds = Owner::FindAllByName('Fred');
        $freds->delete();
    }

    public function testTableName() {
        $this->assertEquals(
            'cars',
            CachedCar::TableName()
        );
    }

    public function testFind() {
        $car = CachedCar::Find(1);

        $this->assertEquals(
            'ORM\\Tests\\Mock\\CachedCar',
            get_class( $car )
        );

        $this->assertEquals(
            1,
            $car->id
        );

        $this->assertEquals(
            'Alfa Romeo',
            $car->brand
        );
    }

    public function testFindFalse() {
        $this->assertFalse( CachedCar::Find(1000) );
    }

    public function testFindWithOptions() {
        $car = CachedCar::Find(array(
            'where' => 'brand LIKE "Alfa Romeo"'
        ), 'ORM\Tests\Mock\CachedOwner');

        $this->assertEquals(
            'ORM\\Tests\\Mock\\CachedCar',
            get_class( $car )
        );

        $this->assertEquals(
            'ORM\\Tests\\Mock\\CachedOwner',
            get_class( $car->CachedOwner )
        );

        $this->assertEquals(
            'Jarrod',
            $car->CachedOwner->name
        );

        $this->assertEquals(
            '156Ti',
            $car->name
        );
    }

    public function testFindWith() {
        $car = CachedCar::Find( 1, 'ORM\Tests\Mock\CachedOwner');

        $this->assertEquals(
            'ORM\\Tests\Mock\\CachedCar',
            get_class( $car )
        );

        $this->assertEquals(
            'ORM\\Tests\Mock\\CachedOwner',
            get_class( $car->CachedOwner )
        );

        $this->assertEquals(
            'Jarrod',
            $car->CachedOwner->name
        );

        $this->assertEquals(
            '156Ti',
            $car->name
        );
    }

    public function testFindOptions() {
        $brand  = 'Alfa Romeo';
        $car    = CachedCar::Find(array(
            'where' => 'doors > ? AND brand NOT LIKE ?',
            'order' => 'colour DESC',
            'values' => array( 3, $brand )
        ));

        $this->assertEquals(
            'ORM\\Tests\Mock\\CachedCar',
            get_class( $car )
        );

        $this->assertNotEquals(
            'Alfa Romeo',
            $car->brand
        );

        $this->assertTrue(
            $car->doors > 3
        );
    }

    public function testFindNoOptions() {
        $car    = CachedCar::Find();

        $this->assertEquals(
            'ORM\\Tests\Mock\\CachedCar',
            get_class( $car )
        );

        $this->assertEquals(
            'Alfa Romeo',
            $car->brand
        );
    }

    public function testFindBy() {
        $car = CachedCar::FindByBrand('Alfa Romeo');

        $this->assertEquals(
            'ORM\\Tests\Mock\\CachedCar',
            get_class( $car )
        );

        $this->assertEquals(
            'Alfa Romeo',
            $car->brand
        );
    }

    public function testFindAllBy() {
        $cars = CachedCar::FindAllByBrand('Volkswagen');

        $this->assertEquals(
            'ORM\\ModelCollection',
            get_class( $cars )
        );

        $this->assertEquals(
            2,
            count( $cars )
        );

        foreach ( $cars as $car ) {
            $this->assertEquals(
                'ORM\\Tests\\Mock\\CachedCar',
                get_class( $car )
            );

            $this->assertEquals(
                'Volkswagen',
                $car->brand
            );
        }
    }

    public function testDescribeTable() {
        $describe = CachedElephant::DescribeTable();

        $this->assertEquals( 2, count( $describe ) );
        $this->assertTrue( in_array('name', $describe) );
        $this->assertTrue( in_array('weight', $describe) );
    }

    public function testCreate() {
        $elephant = new CachedElephant();

        $elephant->name     = "Roger";
        $elephant->weight   = 1234.5;

        $this->assertTrue( $elephant->save() );
    }

    public function testCreateWithAutoIncrement() {
        $owner = new CachedOwner();
        $owner->name = 'Fred';
        $owner->age  = rand(0,120);
        $owner->save();

        $retrieved = CachedOwner::Find( $owner->id() );

        $this->assertEquals( $owner->age, $retrieved->age );

    }

    public function testUpdate() {
        $elephant = new CachedElephant();
        $elephant->name     = "Tim";
        $elephant->weight   = 1000;

        $this->assertTrue( $elephant->save(), 'Unable to save elephant: ', $elephant->errorMessagesString() );

        $elephant = CachedElephant::Find( 'Tim' );
        $this->assertEquals( 1000, $elephant->weight );
        $elephant->weight = 1400;

        $this->assertTrue( $elephant->save() );

        $elephant = CachedElephant::Find( 'Tim' );
        $this->assertEquals( 1400, $elephant->weight );

    }

    public function testDelete() {
        $ford = new CachedCar(array(
            'brand'     => 'Ford',
            'colour'    => 'Black',
            'owner_id'  => 3,
            'doors'     => 2,
            'age'       => 100
        ));

        $this->assertTrue($ford->valid());
        $ford->save();

        // Ensure it has been created for this to make sense as a test
        $this->assertEquals( $ford->id(), CachedCar::Find($ford->id())->id() );

        $ford->delete();
        $this->assertFalse( CachedCar::Find($ford->id()) );
    }

    public function testLoad() {
        $car = new CachedCar(array('id' => 2, 'name' => 'leo') );
        $car->load();

        $this->assertEquals( 2, $car->id );
        $this->assertEquals( "leo", $car->name );
        $this->assertEquals( "Volkswagen", $car->brand );
        $this->assertEquals( 5, $car->doors );
    }

    public function testLoadNewObject() {
        $car = new CachedCar(array('id' => 100000, 'name' => 'leo') );
        $car->load();

        $this->assertEquals( 100000, $car->id );
        $this->assertEquals( "leo", $car->name );
        $this->assertFalse( isset($car->brand) );
    }

    public function testCacheOnFind() {
        $cache              = new APCCache();
        $cache->flush();
        
        $car                = CachedCar::Find(3);
        $cachedCarObject    = $cache->get( (string)$car );

        $this->assertTrue( $cachedCarObject !== false, "Unable to find $car in the cache. " );
        $this->assertEquals( $car, $cachedCarObject );
    }

    public function testCacheOnFindWith() {
        $cache              = new APCCache();
        $cache->flush();

        $car                = CachedCar::Find(3, 'ORM\Tests\Mock\CachedOwner');
        $cachedCarObject    = $cache->get( (string)$car );
        $cachedOwnerObject  = $cache->get( (string)$car->CachedOwner );

        $this->assertTrue( $cachedOwnerObject !== false, "Unable to find {$car->CachedOwner} in the cache. " );
        $this->assertEquals( $car->CachedOwner, $cachedOwnerObject, "Found incorrect owner for {$car->CachedOwner}" );

        unset($car->CachedOwner);

        $this->assertTrue( $cachedCarObject !== false, "Unable to find $car in the cache. " );
        $this->assertEquals( $car, $cachedCarObject, "Found incorrect car for {$car}" );
    }

    public function testRetrieveFromCache() {
        $cache              = new APCCache();
        $cache->flush();

        $car                = CachedCar::Find(3, 'ORM\Tests\Mock\CachedOwner');
        $retrievedCar       = CachedCar::RetrieveFromCache(3, 'ORM\Tests\Mock\CachedOwner');

//        $apc = new \APCIterator('user');
//        echo "\nCached:\n";
//        foreach ( $apc as $cached ) {
//            var_dump($cached);
//        }

        $this->assertEquals( $car, $retrievedCar );
    }
}
?>