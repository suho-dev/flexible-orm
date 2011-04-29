<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Tests;
require_once 'ORMTest.php';

use \ORM\Utilities\Configuration;

set_include_path(get_include_path().PATH_SEPARATOR.'../plugins/ZendGdata-1.11.5/library');

/**
 * Description of GdataTest
 *
 */
class GdataTest extends ORMTest {
    public function testConnection() {
        $user    = Configuration::GoogleData('user');
        $pass    = Configuration::GoogleData('password');
        $service = \Zend_Gdata_Docs::AUTH_SERVICE_NAME;
        
        $httpClient = \Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $service);
        $gdClient   = new \Zend_Gdata_Docs($httpClient);

        $this->assertEquals(
                'Zend_Gdata_HttpClient',
                get_class( $httpClient )
        );

        $this->assertEquals(
                'Zend_Gdata_Docs',
                get_class( $gdClient )
        );
    }

//    public function testRows() {
//        $user       = Configuration::GoogleData('user');
//        $pass       = Configuration::GoogleData('password');
//        $service    = \Zend_Gdata_Spreadsheets::AUTH_SERVICE_NAME;;
//        $httpClient = \Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $service);
//        $gdClient   = new \Zend_Gdata_Spreadsheets($httpClient);
//
//        $key        = 'tUp-3zdla2ly7gqJEreNZ2g';
//        $query = new \Zend_Gdata_Spreadsheets_ListQuery();
//        $query->setSpreadsheetKey($key);
////        $query->setWorksheetId(0);
//        $listFeed = $gdClient->getListFeed($query);
//
//        $this->_printFeed( $listFeed );
//
//        $this->assertTrue(false);
//    }

    private function _printFeed($feed)
    {
        $i = 0;
        foreach($feed->entries as $entry) {
            if ($entry instanceof Zend_Gdata_Spreadsheets_CellEntry) {
                print $entry->title->text .' '. $entry->content->text . "\n";
            } else if ($entry instanceof Zend_Gdata_Spreadsheets_ListEntry) {
                print $i .' '. $entry->title->text .' | '. $entry->content->text . "\n";
            } else {
                print $i .' '. $entry->title->text . "\n";
            }
            $i++;
        }
    }
}
?>
