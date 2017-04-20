<?php

require 'vendor/autoload.php';

use Goutte\Client;

class EbayProductScrapper
{
	public $client;
	public $crawler;

	public function __construct($url)
	{
		$this->client = new Client();
		$this->crawler = $this->client->request('GET', $url);
	}

	public function getProductTitle()
	{
		return $this->crawler->filter('#itemTitle')->first()->text();
	}

	public function getProductPrice()
	{
		return $this->crawler->filter('#prcIsum')->first()->text();
	}

	public function getProductQuantity()
	{
		return $this->crawler->filter('#qtyTextBox')->first()->text();
	}

	// public function getProductDescription()
	// {
	// 	$descriptionArr = [];
	//
	// 	$this->crawler->filter('#itemDescription>div>span>font')->each(function ($node) {
	// 		$descriptionArr[] = $node->text();
	// 	});
	//
	// 	return $descriptionArr;
	// }



	public function getSellerName()
	{
		return $this->crawler->filter('#mbgLink')->first()->text();
	}

	public function getSellerLink()
	{
		return $this->crawler->filter('#mbgLink')->first()->extract(array('href'))[0];
	}

	public function getProductAttributes()
	{
		$attrs = [];
		$attrContainer = $this->crawler->filter('.itemAttr')->first();
		$attrTable = $attrContainer->filter('div.section > table');
		$labels = $attrTable->filter('td.attrLabels');
		$labels->each(function($node) use(&$attrs)
		{
			$valueNode = $node->nextAll()->first();
			$key = trim($node->text());
			$value = trim($valueNode->text());
			$attr = [
					'key' => $key,
					'value' => $value
			];
			$attrs[] = $attr;
		});
		return $attrs;
	}

	public function getSellerAddress()
	{
		$addressContainer = $this->crawler->filter('.bsi-cic')->first();
		return $address = $addressContainer->filter('.bsi-c1')->first()->text();

	}

	public function getSellerContacts()
	{
		$contacts = [];
		$addressContainer = $this->crawler->filter('.bsi-cic')->first();
		$numberContainer = $addressContainer->filter('.bsi-c2')->first();
		$count = 0;
		$numberContainer->filter('div')->each(function($div) use(&$count, &$contacts)
		{
			$span = $div->filter('span:not(.bsi-lbl)')->first();
			if($count == 0)
			{
				$contacts[] = [
					'type' => 'phone',
					'value' => $span->text()
				];
			}
			elseif($count == 1)
			{
				$contacts[] = [
					'type' => 'email',
					'value' => $span->text()
				];
			}
			$count++;
		});
		return $contacts;
	}

	public function getImages()
	{
		$imageContainer = $this->crawler->filter('.bsi-c2')->first();
		$unorderList = $imageContainer->filter('.lst.icon')->first();
		$unorderList->filter('li')->each(function($li)
		{
			$img = $li->filter('a > table > tbody > tr > td > div > img')->first();
			$src = $img->extract(array('src'))[0];
			echo $src;
		});
	}
}

// $url = 'http://www.ebay.co.uk/itm/Louis-XV-Style-Writing-Table-Circa-1900-/231928116653';
// //$url = 'http://www.ebay.co.uk/itm/NEW-Farmhouse-Fruit-Basket-bowl-box-kitchen-storage-old-rustic-style/260943651348';
//
// $ebayProductScrapper = new EbayProductScrapper($url);
// echo $ebayProductScrapper->getProductTitle()."\n";
// echo $ebayProductScrapper->getProductPrice()."\n";
// //echo $ebayProductScrapper->getProductQuantity()."\n";
// echo $ebayProductScrapper->getSellerName()."\n";
// echo $ebayProductScrapper->getSellerLink()."\n";
// print_r($ebayProductScrapper->getProductAttributes())."\n";
// echo $ebayProductScrapper->getSellerAddress()."\n";
// print_r($ebayProductScrapper->getSellerContacts())."\n";
// echo $ebayProductScrapper->getImages()."\n";
