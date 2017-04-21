<?php
require 'libs/functions.php';
$urls = [];
if (isset($_POST['scrapper'])) {
  $urls = explode(',', $_POST['inputLinks']);
}

$data = getFormattedData($urls);

outputCsv('ebay-data.csv', $data);
