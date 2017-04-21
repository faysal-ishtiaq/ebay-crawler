<?php
require 'libs/functions.php';
$urls = ['http://www.ebay.co.uk/itm/NEW-Farmhouse-Fruit-Basket-bowl-box-kitchen-storage-old-rustic-style/260943651348', 'http://www.ebay.co.uk/itm/Louis-XV-Style-Writing-Table-Circa-1900-/231928116653'];
// $urls = ['http://www.ebay.co.uk/usr/bronk.uk2015?_trksid=p2047675.l2559', 'http://www.ebay.co.uk/itm/Louis-XV-Style-Writing-Table-Circa-1900-/231928116653'];
// $urls = ['http://www.ebay.co.uk/itm/Woods-Ware-Woods-Sons-England-Rouen-Pattern-Dinner-Plate-c-1917-/232282696661'];
$data = getFormattedData($urls);

print_r($data);
