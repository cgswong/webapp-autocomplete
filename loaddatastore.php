<?php

require 'vendor/autoload.php';
use Google\Auth\ApplicationDefaultCredentials;
use Google\Cloud\Datastore\DatastoreClient;

/**
 * Create a new product with a given SKU.
 *
 * @param DatastoreClient $datastore
 * @param $sku
 * @param $product
 * @return Google\Cloud\Datastore\Entity
 */
function add_product(DatastoreClient $datastore, $sku, $prod)
{
    $productKey = $datastore->key('SKU', $sku);
    $product = $datastore->entity(
        $productKey,
        [
            'created' => new DateTime(),
            'name' => strtolower($prod)
        ]);
    $datastore->upsert($product);
    return $product;
}

/*
  Load Cloud DataStore Kind from remote URL

  @param $projectId
  @param $url
*/
function load_datastore($projectId, $url) {
  // Create Datastore client
  $datastore = new DatastoreClient(['projectId' => $projectId]);

  // Enable `allow_url_fopen` to allow reading file from URL
  ini_set("allow_url_fopen", 1);

  // Read the products listing and load to Cloud Datastore.
  // Use batches of 20 for a transaction
  $json = json_decode(file_get_contents($url), true);
  $count = 1;
  foreach($json as $key => $value) {
    if ($count == 1) {
		  $transaction = $datastore->transaction();
    }
    add_product($datastore, $value["sku"], $value["name"]);
		if ($count == 20) {
		  $transaction->commit();
		  $count = 0;
    }
    $count++;
  }
}

try
{
	$projectId = 'development-206303';
	$url = 'https://raw.githubusercontent.com/BestBuyAPIs/open-data-set/master/products.json';
	load_datastore($projectId, $url);
} catch (Exception $err) {
	echo 'Caught exception: ',  $err->getMessage(), "\n";
  //$transaction->rollback();
}
?>
