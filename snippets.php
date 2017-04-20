<?php
use EbayProductScrapper;

$url = 'http://www.ebay.co.uk/usr/theoldcountryfarmhouse';

$client = new CLient();
$crawler = $client->request('GET', $url);

$link = $crawler->filter('.see_all_items')->first()->extract(array('href'))[0];

echo $link;

$crawler->filter('.see_all_items')->each(function ($nodes) {
  print_r($nodes);
});

$links = [];

$crawler->filter('div.ttl a')->each(function ($node) use(&$links)
{
  $links[] = $node->extract(array('href'))[0];
});

function storeToListing($url)
{
  $pages = [];
  $client = new Client();
  $crawler = $client->request('GET', $url);

  $crawler->filter('a.pg')->each(function ($node)
  {
    $link = $node->extract(array('href'))[0];
    $pages[] = $link;
  });

  if(count($pages))
  {
    foreach ($pages as $page)
    {
      echo $page.'\n';
    }
  }

  return $pages;
}


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
