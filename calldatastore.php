<?php
require 'vendor/autoload.php';
use Google\Auth\ApplicationDefaultCredentials;
use Google\Cloud\Datastore\DatastoreClient;

try
{
	$mem = new Memcached();
	$mem->addServer("127.0.0.1", 11211);

	web-serving-auto1 = 'location360-poc';
	$datastore = new DatastoreClient([
	    'projectId' => $projectId
	]);

	echo 'Matches:<br/>';
	$matches_string = "";

	// Get the string typed in by user for autocompletion...
	$queryval = strtolower($_GET['searchtext']);

	// If it is not blank...
	if ($queryval[0]) {

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
				$matches_string = $matches_string . $SKU['name'] . "<br/>";
			}

			// Insert query result in local cache for next time to avoid Cloud Datastore round-trip
			// Caching for 7 days (demo)
			$mem->set($queryval, $matches_string, 604800);
		}
		echo $matches_string;
	}
} catch (Exception $e) {
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}
?>
