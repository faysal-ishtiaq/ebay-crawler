<?php

require 'vendor/autoload.php';

use Goutte\Client;

class EbayListScrapper
{
  public $url;
  public $client;
  public $crawler;
  public $pages;

  public function __construct($url)
  {
    $this->url = $url;
    $this->client = new Client();
    $this->crawler = $this->client->request('GET', $url);
    $this->pages = array();
  }

  public function getAllLinks()
  {
    return $this->crawler->filter('h3 a.vip')->first()->extract(array('href'))[0];
  }

  public function getAllPageLinks()
  {
    $this->crawler->filter('a.pg')->each(function ($node)
    {
      $link = $node->extract(array('href'))[0];
      $this->pages[] = $link;
    });

    return $this->pages;
  }
}

// $url = 'http://www.ebay.co.uk/sch/theoldcountryfarmhouse/m.html';
// $url = 'http://www.ebay.com/sch/ss11211/m.html?_nkw=&_armrs=1&_ipg=&_from=';
$url = 'http://www.ebay.com/sch/shawneehonda/m.html?_nkw=&_armrs=1&_ipg=&_from=';

$linkCrawler = new EbayListScrapper($url);
$link = $linkCrawler->getAllLinks();
echo $link;
$pages = $linkCrawler->getAllPageLinks();
print_r($pages);

foreach ($pages as $page) {
  $parts = parse_url($page);
  parse_str($parts['query'], $query);
  echo $query['_ssn'];
}
