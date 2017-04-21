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
		$title = $this->crawler->filter('#itemTitle')->first()->text();
		return trim(str_replace('Details about','', $title));
	}

	public function getProductPrice()
	{
		return $this->crawler->filter('#prcIsum')->first()->text();
	}

	public function getProductQuantity()
	{
		return $this->crawler->filter('#qtyTextBox')->first()->text();
	}

	public function getProductDescription()
	{
		$_url = $this->crawler->filter('iframe#desc_ifr')->first()->extract(array('src'))[0];

		$_client = new Client();
		$_crawler = $_client->request('GET', $_url);

		return $_crawler->filter('div#ds_div font')->first()->text();
	}



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
		$text = '';

		$addressContainer = $this->crawler->filter('.bsi-cic')->first();
		$addressContainer->filter('.bsi-c1 div')->each(function ($node) use(&$text)
		{
			$text = $text."\n".trim($node->text());
		});

		return $text;
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
		$images = [];
		$unorderList = $this->crawler->filter('table.img td.tdThumb img')->each(function($img) use(&$images)
		{
			$src = $img->extract(array('src'))[0];
			$images[] = $src;
		});

		return $images;
	}
}
