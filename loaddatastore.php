<?php

require 'vendor/autoload.php';
use Google\Auth\ApplicationDefaultCredentials;
use Google\Cloud\Datastore\DatastoreClient;

/*
  Load Cloud DataStore Kind from Cloud Storage object/file

  @param DataStoreClient $datastore
  @param Bucket $bucketName
  @param Object $objectName
*/
function datastore_gs_load(
  DataStoreClient $datastore,
  Bucket $bucketName,
  Object $objectName
) {
  // Imports the Google Cloud Storage client library
  use Google\Cloud\Storage\StorageClient;

  $config = [
      'projectId' => $projectId,
  ];

  $storage = new StorageClient($config);

  $bucket = $storage->bucket($bucketName);
  $object = $bucket->object($objectName);

  // Download products listing to local file
  $stream = $object->downloadToFile(__DIR__ . '/tmp/products.txt');
}

try
{
	$projectId = 'location360-poc';
	$datastore = new DatastoreClient([
	    'projectId' => $projectId
	]);

  // Treat each entity (UPSERT) as a transaction.
  // Ensures we only fail/ROLLBACK individual entities, but not as efficient as batch
  // which can go up to 25 entity groups or 500 entities in a commit limit
  // TODO: Use batch writes (upsertBatch in 25 entity group, or 500 entities in a commit limit)
	if (sizeof($argv) == 3) {
		$transaction = $datastore->transaction();
		$key = $datastore->key('SKU', $argv[1]);
		$product = $datastore->entity( $key, [
			'name' => strtolower($argv[2])
		]);
		$datastore->upsert($product);
		$transaction->commit();
	}
} catch (Exception $e) {
	echo 'Caught exception: ',  $e->getMessage(), "\n";
  $transaction->rollback();
}
?>
