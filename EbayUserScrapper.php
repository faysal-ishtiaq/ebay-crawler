<?php

require 'vendor/autoload.php';

use Goutte\Client;

/**
 * Get the items link
 */
class EbayUserScrapper
{
  public $client;
  public $crawler;

  public function __construct($url)
  {
    $this->client = new Client();
    $this->crawler = $this->client->request('GET', $url);
  }

  public function getAllItemsLink()
  {
    return $this->crawler->filter('a.see_all_items')->first();
  }
}

$url = 'http://www.ebay.com/usr/shawneehonda?_trksid=p2047675.l2559';
$profile = new EbayUserScrapper($url);
$link = $profile->getAllItemsLink();
print_r($link);
