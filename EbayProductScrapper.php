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

	// public function getProductDescription()
	// {
	// 	$description = [];
	// 	// $this->crawler->filter('div#ds_div font font')->each(function ($node) use(&$descriptionArr) {
	// 	// 	$descriptionArr[] = $node->text();
	// 	// });
	//
	// 	// $this->crawler->filter('font')->each(function ($node) use(&$description)
	// 	// {
	// 	// 	if($node->extract(array('_text'))) $description[] = $node->extract(array('_text'));
	// 	// });
	//
	// 	// $this->crawler->filter('font')->each(function ($node) use(&$description)
	// 	// {
	// 	// 	// if($node->html()) $description[] = $node->html();
	// 	// 	$description[] = $node->ownerDocument->saveHTML($node);
	// 	// });
	// 	$crw = $this->crawler->filter('div font');
	//
	// 	foreach ($crw as $domElement)
	// 	{
	// 		$description[] = $domElement->ownerDocument->saveHTML($domElement);
	// 	}
	//
	// 	return $description;
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

// $url = 'http://www.ebay.co.uk/itm/Louis-XV-Style-Writing-Table-Circa-1900-/231928116653';
$url = 'http://www.ebay.co.uk/itm/NEW-Farmhouse-Fruit-Basket-bowl-box-kitchen-storage-old-rustic-style/260943651348';

// $csvFile = 'ebay-data.csv';
//
// if (file_exists($csvFile)) {
//   $fh = fopen($myFile, 'a');
//   fwrite($fh, $message."\n");
// } else {
//   $fh = fopen($myFile, 'w');
//   fwrite($fh, $message."\n");
// }
// fclose($fh);

$data = [];
$ebayProductScrapper = new EbayProductScrapper($url);

$csv_headers = ['item_url', 'product_title', 'product_price', 'style', 'material', 'age', 'product type', 'brand', 'country/region of manufacture', 'seller_name', 'seller_link', 'seller_address', 'email', 'phone', 'url0', 'url1', 'url2', 'url3', 'url4', 'url5', 'url6', 'url7', 'url8', 'url9', 'url10', 'url11', 'url12', 'url13', 'url14'];

$data['item_url'] = $url;
$data['product_title'] = $ebayProductScrapper->getProductTitle();
$data['product_price'] = $ebayProductScrapper->getProductPrice();
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

// print_r($productAttr);

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
// print_r($images);
print_r($data);
// echo "\n".$ebayProductScrapper->getProductDescription();
// print_r($ebayProductScrapper->getProductDescription());
