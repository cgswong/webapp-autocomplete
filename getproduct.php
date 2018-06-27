<?php
# Include the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Import the Google Cloud client library
use Google\Auth\ApplicationDefaultCredentials;
use Google\Cloud\Datastore\DatastoreClient;

try
{
	$mem = new Memcache();
	$mem->addServer('127.0.0.1', 11211);

	$projectId = 'development-206303';
	$datastore = new DatastoreClient(['projectId' => $projectId]);

	echo "'";
	$matches_string = "";

	// Get the string typed in by user for autosuggestion...
	$queryval = strtolower($_GET['searchtext']);

	// If it is not blank...
	if (isset($queryval) && !empty($queryval)) {
		// Check local cache first for query results...
		$cache_hit = $mem->get($queryval);
		if ($cache_hit) {

			// Cache hit, no need to go back to Cloud Datastore...
			$matches_string = (string) $cache_hit;
		} else {

			// Cache miss, fetch result from Cloud Datastore...
			$upperlimit = $queryval . json_decode('"\ufffd"');
			$query = $datastore->query()
				->kind('SKU')
				->filter('name', '>=', $queryval)
				->filter('name', '<', $upperlimit)
				->order('name');
			$result = $datastore->runQuery($query);
			foreach ($result as $SKU) {
				$matches_string = $matches_string . $SKU['name'] . "',";
			}

			// Insert query result in local cache for next time to avoid Cloud Datastore round-trip
			$mem->set($queryval, $matches_string);
		}
		echo rtrim($matches_string,",");
	}
} catch (Exception $err) {
	echo 'Caught exception: ',  $err->getMessage(), "\n";
}
?>
