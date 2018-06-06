<?php

require 'vendor/autoload.php';
use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

try
{
// Path to application credentials
putenv('GOOGLE_APPLICATION_CREDENTIALS=/home/kbxkb/cp100-bbde4d919e35.json');

// Define the scopes for API call
$scopes = ['https://www.googleapis.com/auth/drive.readonly'];

// Create middleware
$middleware = ApplicationDefaultCredentials::getMiddleware($scopes);
$stack = HandlerStack::create();
$stack->push($middleware);

// Create HTTP client
$client = new Client([
  'handler' => $stack,
  'base_uri' => 'https://www.googleapis.com',
  'auth' => 'google_auth'  // authorize all requests
]);

// Make the request
$response = $client->get('drive/v2/files');

// Show the result!
print_r((string) $response->getBody());

} catch (Exception $e) {
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}
?>
