<?php
# Include the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Import the Google Cloud client library
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
            'name' => $prod
        ]);
    $datastore->upsert($product);
    return $product;
}

/*
  Batch load Cloud DataStore Kind from remote URL

  @param $projectId
  @param $url
*/
function batch_load_datastore($projectId, $url) {
  // Create Datastore client
  $datastore = new DatastoreClient(['projectId' => $projectId]);

  // Enable `allow_url_fopen` to allow reading file from URL
  ini_set("allow_url_fopen", 1);

  // Read the products listing and load to Cloud Datastore.
  // Use batches of 20 for a transaction
  $json = json_decode(file_get_contents($url), true);
  $productKeys = array();
  $products = array();
  $count = 0;
  foreach($json as $key => $value) {
    $productKeys[] = $datastore->key('SKU-BATCH', $key);
    $products[] = $datastore->entity(
        $productKey[$count],
        [
            'created' => new DateTime(),
            'name' => $value
        ]);
    $count++;
  
  $datastore->upsertBatch($products);
  }
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
  foreach($json as $key => $value) {
    add_product($datastore, $value["sku"], $value["name"]);
  }
}

try
{
	$projectId = 'development-206303';
	$url = 'https://raw.githubusercontent.com/BestBuyAPIs/open-data-set/master/products.json';
	batch_load_datastore($projectId, $url);
	return 200;
} catch (Exception $err) {
	echo 'Caught exception: ',  $err->getMessage(), "\n";
}
?>
