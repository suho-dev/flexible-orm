<?php
// How may items per page
$pageSize = 10;

// How many pages will there be?
$pageCount = ceil( Owner::CountFindAll() / $pageSize );

// Get all the pages (this is a trivial example, you wouldn't fetch all the pages
// at once
$pages = array();
for( $i = 1; $i <= $pageCount; $i++ ) {
    // Get the next 10 items
    $pages[$i] = Owner::FindAll(array(
        'limit'  => $pageSize,
        'offset' => ($i-1) * $pageSize
    ));
}