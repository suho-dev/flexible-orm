<?php
// Set the response class on a new AmazonSDB object
$sdb = new \AmazonSDB();
$sdb->set_response_class('\ORM\SDB\SDBResponse');

// ---------------------
// Using get_attributes() (retrieves one item's attributes)
$attributes = $sdb->get_attributes( 'myDomain', 'item1' );

// You can still use CFResponse features
if( !$attributes->isOK() ) {
    die( "Oh no! There's a problem");
}

// $attributes is now accessible as an array of key->value pairs associated with item1
echo "My item {$attributes['id']} has the name {$attributes['name']}";

// -------------
// Using select() (retrieves any number of items)
$items = $sdb->select( 'SELECT * FROM myDomain' );

// Count will work
echo "Returned ", count($items), " items.";

foreach( $items as $itemName => $itemAttributes ) {
     echo "Item: $itemName\n";

     // Print out all the attributes of this item
     print_r( $itemAttributes );
}