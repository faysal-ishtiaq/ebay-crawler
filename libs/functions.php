<?php
require 'vendor/autoload.php';
require 'EbayProductScrapper.php';

use Goutte\Client;

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

  if (!count($links))
  {
    $crawler->filter('a.vi-url')->each(function ($node) use(&$links)
    {
      $links[] = $node->extract(array('href'))[0];
    });
  }
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
  $urlType = 'unknown';
  if (count($pathAttr) == 3 && $hostAttr[0] != 'stores' && $pathAttr[1] == 'usr')
  {
      $urlType = 'user';
  }
  // if (count($pathAttr) == 4 && $hostAttr[0] != 'stores' && $pathAttr[1] != 'usr')
  // {
  //     $urlType = 'listing';
  // }
  if (count($pathAttr) >= 4 && $hostAttr[0] != 'stores' && $pathAttr[1] != 'usr')
  {
      $urlType = 'listing';
  }
  if (count($pathAttr) == 2 && $hostAttr[0] == 'stores' && $pathAttr[1] != 'usr')
  {
      $urlType = 'store';
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

  $storeUrl = $parts['scheme'].'://'.$parts['host'].'/'.$pathAttr[2];

  $client = new Client();
  $crawler = $client->request('GET', $url);

  $storeUrl = $crawler->filter('span.store_lk a')->first()->extract(array('href'))[0];

  return storeToListing($storeUrl);
}

/*
 * function : storeToListing(string $url);
 * returns  : array of listing page urls
 */
function storeToListing($url)
{
  $alternative = false;
  $pages = [];
  $listingUrls = [];
  $client = new Client();
  $crawler = $client->request('GET', $url);

  $crawler->filter('.pages a')->each(function ($node) use(&$pages)
  {
    $pages[] = $node->extract(array('href'))[0];
  });

  if (!count($pages))
  {
    $crawler->filter('.pgn a.no')->each(function ($node) use(&$pages)
    {
      $pages[] = $node->extract(array('href'))[0];
    });

    $alternative = true;
  }

  foreach ($pages as $page)
  {

    $parts = parse_url($page);

    if($page == '')
    {
      if($alternative)
      {
        if($parts['path'] == "")
        {
          $parts = parse_url($pages[1]);
        }

        parse_str($parts['query'], $query);
        $query['_pgn'] = 1;
        $pageUrl = $parts['scheme'].'://'.$parts['host'].$parts['path'].http_build_query($query);
      }
      else{
        $pageUrl = $parts['scheme'].'://'.$parts['host'].substr_replace($pages[1], '1', -1);
      }

    }
    else
    {
      if($alternative) $pageUrl = $page;
      else $pageUrl = $parts['scheme'].'://'.$parts['host'].$page;
    }

    $listingUrls[] = getItemUrl($pageUrl);
  }
  return $listingUrls;
}


function outputCsv($fileName, $assocDataArray)
{
    ob_clean();
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Cache-Control: private', false);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=' . $fileName);
    if(isset($assocDataArray['0'])){
        $fp = fopen('php://output', 'w');
        fputcsv($fp, array_keys($assocDataArray['0']));
        foreach($assocDataArray AS $values){
            fputcsv($fp, $values);
        }
        fclose($fp);
    }
    ob_flush();
}


/*
 * extract items
 */

 function extractItems($url)
 {
   $data = [];
   $ebayProductScrapper = new EbayProductScrapper($url);
   $data['item_url'] = $url;
   $data['product_title'] = $ebayProductScrapper->getProductTitle();
   $data['product_price'] = $ebayProductScrapper->getProductPrice();
   $data['product_description'] = $ebayProductScrapper->getProductDescription();
   $data['seller_name'] = $ebayProductScrapper->getSellerName();
   $data['seller_link'] = $ebayProductScrapper->getSellerLink();

   $productAttr = $ebayProductScrapper->getProductAttributes();
   foreach ($productAttr as $attr)
   {
   	if (is_array($attr))
   	{
   		$data[str_replace(':', '', $attr['key'])] = $attr['value'];
   	}
   }

   $data['seller_address'] = $ebayProductScrapper->getSellerAddress();

   $contacts = $ebayProductScrapper->getSellerContacts();
   foreach ($contacts as $contact)
   {
   	if (is_array($contact))
   	{
   		$data[$contact['type']] = $contact['value'];
   	}
   }

   $images = $ebayProductScrapper->getImages();
   foreach ($images as $key => $value)
   {
   	$data['url'.$key] = $value;
   }

   return $data;
 }

/**
 * start processing url
 */
function startProcessing($url)
{
  $urlType = getUrlType($url);
  $productData = [];
  echo $urlType;

  if ($urlType == 'user')
  {
    $listings = userToListing($url);
      print_r($listings);
      die();
    foreach ($listings as $listingChunk)
    {
      foreach($listingChunk as $listUrl)
      {
        $productData[] = extractItems($listUrl);
      }
    }
  }

  if ($urlType == 'store')
  {
    echo "handling store type link";
    die();
    $listings = storeToListing($url);

    foreach ($listings as $listingChunk)
    {
      foreach($listingChunk as $listUrl)
      {
        $productData[] = extractItems($listUrl);
      }
    }
  }

  if ($urlType == 'listing')
  {
    $productData[] = extractItems($url);
  }

  return $productData;
  // outputCsv('ebay-data.csv', $productData);
}

function getFormattedData($urls)
{
  $data = [];
  $superData = [];
  $superKeys = [];
  foreach ($urls as $_url)
  {
    $data[] = startProcessing(trim($_url));
  }

  foreach ($data as $_data)
  {
    foreach($_data as $d_)
      {
        $superKeys = array_unique(array_merge($superKeys, array_keys($d_)));
      }
  }

  foreach($data as $_data)
  {
    foreach ($_data as $_d)
    {
      $d = [];
      foreach($superKeys as $key)
      {
        if (array_key_exists($key, $_d))
        {
          $d[$key] = $_d[$key];
        }
        else $d[$key] = '';
      }
      $superData[] = $d;
    }
  }
  return $superData;
}
