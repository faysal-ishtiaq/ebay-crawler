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
		$description = '';

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

		if (strpos($_url, '__00004000__')) return '';
		if (strpos(strtolower($_url), 'error')) return '';

		$_client = new Client();

		try
		{
			$_crawler = $_client->request('GET', $_url);
		}
		catch (Exception $e)
		{
			return '';
		}
		catch (RuntimeException $e)
		{
			return '';
		}

		try
		{
			$description = $_crawler->filter('div#ds_div')->first()->extract(array('_text'))[0];
			});
		}
		catch (Exception $e)
		{
			$description = '';
		}
		catch (RuntimeException $e)
		{
			$description = '';
		}

		if($description) return $description;

		try
		{
			$description = $_crawler->filter('div#ds_div font')->each(function($node) use($description){
				$description .= '\n'.$node->text();
			});
		}
		catch (Exception $e)
		{
			try
			{
				$description = $_crawler->filter('div#ds_div')->each(function($node) use($description){
					$description .= '\n'.$node->extract(array('_text'))[0];
				});
			}
			catch (Exception $e)
			{
				try
				{
					$description = $_crawler->filter('div#ds_div font p')->each(function($node) use($description){
						$description .= '\n'.$node->extract(array('_text'))[0];
					});
				}
				catch (Exception $e)
				{
					try
					{
						$description = $_crawler->filter('div#description-area')->each(function($node) use($description){
							$description .= '\n'.$node->extract(array('_text'))[0];
						});
					}
					catch (Exception $e)
					{
						try
						{
							$description = $_crawler->filter('div#ds_div font')->each(function($node) use($description){
								$description .= '\n'.$node->extract(array('_text'))[0];
							});
						}
						catch (Exception $e)
						{
							try
							{
								$description = $_crawler->filter('div#ds_div font li')->each(function ($node) use($description){
									$description .= $node->extract(array('_text'))[0];
								});
							}
							catch (Exception $e)
							{
								try
								{
									// $description = $_crawler->filter('div#ds_div font li')->each(function ($node) use($description){
									// 	$description .= $node->extract(array('_text'))[0];
									// });
									$description = $_crawler->filter('div#ds_div .x-main-desc .x-tins')->first()->extract(array('_text'))[0];
									});
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
		} catch (Exception $e) {}
		return $contacts;
	}

	public function getImages()
	{
		$images = [];
		try {
			if (count($this->crawler->filter('.img.img500.vi-hide-mImgThr')))
			{
				$unorderList = $this->crawler->filter('.img.img500.vi-hide-mImgThr')->each(function($img) use(&$images)
				{
					$src = $img->extract(array('src'))[0];
					$images[] = $src;
				});
			}

		} catch (Exception $e) {}

		return $images;
	}
}
