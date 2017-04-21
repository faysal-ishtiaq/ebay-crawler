<?php
require 'functions.php';

$urls = ['http://www.ebay.co.uk/itm/NEW-Farmhouse-Fruit-Basket-bowl-box-kitchen-storage-old-rustic-style/260943651348', 'http://www.ebay.co.uk/itm/Louis-XV-Style-Writing-Table-Circa-1900-/231928116653'];

$data = getFormattedData($urls);

print_r($data);
