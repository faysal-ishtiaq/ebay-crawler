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
		try {
			$title = $this->crawler->filter('#itemTitle')->first()->text();
		} catch (Exception $e) {
			$title = '';
		}

		try {
			$title = trim(str_replace('Details about','', $title));
		} catch (Exception $e) {
			$title = $title;
		}

		return $title;
	}

	public function getProductPrice()
	{
		try {
			$price = $this->crawler->filter('#prcIsum')->first()->text();
		} catch (Exception $e) {
			$price = '';
		}

		return $price;
	}

	public function getProductQuantity()
	{
		try {
			$quantity = $this->crawler->filter('#qtyTextBox')->first()->text();
		} catch (Exception $e) {
			$quantity = '';
		}

		return $quantity;
	}

	public function getProductDescription()
	{
		try {
			$_url = $this->crawler->filter('iframe#desc_ifr')->first()->extract(array('src'))[0];
		}
		catch (Exception $e)
		{
			return '';
		}
		catch (RuntimeException $e)
		{
			return '';
		}

		if (strpos($url, '__00004000__')) return '';
		
		$_client = new Client();
		$_crawler = $_client->request('GET', $_url);

		try
		{
			$description = $_crawler->filter('div#ds_div font')->first()->text();
		}
		catch (Exception $e)
		{
			try
			{
				$description = $_crawler->filter('div#ds_div')->first()->extract(array('_text'))[0];
			}
			catch (Exception $e)
			{
				$description = '';
			}
			catch (RuntimeException $e)
			{
				$description = '';
			}
		}
		catch (RuntimeException $e)
		{
			$description = '';
		}

		return $description;
	}



	public function getSellerName()
	{
		try {
			$sellerName = $this->crawler->filter('#mbgLink')->first()->text();
		} catch (Exception $e) {
			$sellerName = '';
		}

	}

	public function getSellerLink()
	{
		try {
			$sellerLink = $this->crawler->filter('#mbgLink')->first()->extract(array('href'))[0];
		} catch (Exception $e) {
			$sellerLink = '';
		}

		return $sellerLink;
	}

	public function getProductAttributes()
	{
		$attrs = [];

		try {

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
		} catch (Exception $e) {

		}

		return $attrs;
	}

	public function getSellerAddress()
	{
		$text = '';

		try {
			$addressContainer = $this->crawler->filter('.bsi-cic')->first();
			$addressContainer->filter('.bsi-c1 div')->each(function ($node) use(&$text)
			{
				$text = $text."\n".trim($node->text());
			});
		} catch (Exception $e) {

		}


		return $text;
	}

	public function getSellerContacts()
	{
		$contacts = [];
		try {
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
		} catch (Exception $e) {

		}
		return $contacts;
	}

	public function getImages()
	{
		$images = [];
		try {

			$unorderList = $this->crawler->filter('table.img td.tdThumb img')->each(function($img) use(&$images)
			{
				$src = $img->extract(array('src'))[0];
				$images[] = $src;
			});
		} catch (Exception $e) {

		}
		return $images;
	}
}
