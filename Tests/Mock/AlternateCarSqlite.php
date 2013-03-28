<?php
/**
 * @file
 * @author Pierre Dumuid <pierre.dumuid@sustainabilityhouse.com.au>
 */
/**
 * Mock object classes for testing
 */
namespace FlexibleORMTests\Mock;
use Suho\FlexibleOrm\ORM_Model;
/**
 * Description of AlternateCarSqlite
 *
 * A simple Model using a separate sqlite database
 */
class AlternateCarSqlite extends ORM_Model {
    const DATABASE  = 'sqliteDB';
    const TABLE     = 'cars';
}
?>
