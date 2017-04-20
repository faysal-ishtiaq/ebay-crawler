<?php

require 'vendor/autoload.php';
require 'EbayProductScrapper.php';

use Goutte\Client;


$url = 'http://www.ebay.co.uk/usr/theoldcountryfarmhouse';
$url = 'http://stores.ebay.co.uk/retro-fun-stuff';
$url = 'http://stores.ebay.com/asc365usa/';
$url = 'http://stores.ebay.com/themaytagshed';
$url = 'http://stores.ebay.com/theoldcountryfarmhouse';
// $url = 'http://www.ebay.co.uk/theoldcountryfarmhouse';
// $url = 'http://www.ebay.co.uk/itm/NEW-Farmhouse-Fruit-Basket-bowl-box-kitchen-storage-old-rustic-style/260943651348';
// $url = 'http://www.ebay.co.uk/usr/theoldcountryfarmhouse';

/*
 * function : getItemUrl(string $url);
 * returns  : array of listing page links
 */
function getItemUrl($url)
{
  $client = new Client();
  $crawler = $client->request('GET', $url);
  $links = [];

  $crawler->filter('div.ttl a')->each(function ($node) use(&$links)
  {
    $links[] = $node->extract(array('href'))[0];
  });

  return $links;
}

/*
 * function : getUrlType(string $url);
 * returns  : type of ebay page
 */
function getUrlType($url)
{
  $parts = parse_url($url);
  $hostAttr = explode('.', $parts['host']);
  $pathAttr = explode('/', $parts['path']);

  if (count($pathAttr) == 3 && $hostAttr[0] != 'stores' && $pathAttr[1] == 'usr')
  {
      $urlType = 'user';
  }
  else if (count($pathAttr) == 4 && $hostAttr[0] != 'stores' && $pathAttr[1] != 'usr')
  {
      $urlType = 'listing';
  }
  else if (count($pathAttr) == 2 && $hostAttr[0] == 'stores' && $pathAttr[1] != 'usr')
  {
      $urlType = 'store';
  }
  else
  {
    $urlType = 'unknown';
  }

  return $urlType;
}

/*
 * function : userToListing(string $url);
 * returns  : array of listing page urls
 */
function userToListing($url)
{
  $parts = parse_url($url);
  $hostAttr = explode('.', $parts['host']);
  $pathAttr = explode('/', $parts['path']);

  $hostAttr[0] = 'stores';
  $parts['host'] = join('.', $hostAttr);

  $storeUrl = $parts['scheme'].'://'.$parts['host'].'/'.$pathAttr[1];

  return storeToListing($storeUrl);
}

/*
 * function : storeToListing(string $url);
 * returns  : array of listing page urls
 */
function storeToListing($url)
{
  $pages = [];
  $listingUrls = [];
  $client = new Client();
  $crawler = $client->request('GET', $url);

  $crawler->filter('.pages a')->each(function ($node) use(&$pages)
  {
    $pages[] = $node->extract(array('href'))[0];
  });

  foreach ($pages as $page)
  {
    $parts = parse_url($url);

    if($page == '')
    {
      $pageUrl = $parts['scheme'].'://'.$parts['host'].substr_replace($pages[1], '1', -1);
    }
    else
    {
      $pageUrl = $parts['scheme'].'://'.$parts['host'].$page;
    }

    $listingUrls[] = getItemUrl($pageUrl);
  }
  return $listingUrls;
}


/*
 * extract items
 */

 function extractItems($url)
 {
   $ebayProductScrapper = new EbayProductScrapper($url);
   echo $ebayProductScrapper->getProductTitle()."\n";
   echo $ebayProductScrapper->getProductPrice()."\n";
   //echo $ebayProductScrapper->getProductQuantity()."\n";
   echo $ebayProductScrapper->getSellerName()."\n";
   echo $ebayProductScrapper->getSellerLink()."\n";
   print_r($ebayProductScrapper->getProductAttributes())."\n";
   echo $ebayProductScrapper->getSellerAddress()."\n";
   print_r($ebayProductScrapper->getSellerContacts())."\n";
   echo $ebayProductScrapper->getImages()."\n";
 }

$urlType = getUrlType($url);
echo $urlType;

if ($urlType == 'user')
{
  $listings = userToListing($url);
  foreach ($listings as $listingChunk)
  {
    foreach($listingChunk as $listUrl)
    {
      extractItems($listUrl);
    }
  }
}
else if ($urlType == 'store')
{
  $listings = userToListing($url);
  foreach ($listings as $listingChunk)
  {
    foreach($listingChunk as $listUrl)
    {
      extractItems($listUrl);
    }
  }
}
else if ($urlType == 'listing')
{
  extractItems($url);
}
else
{
  //do nothing
}
