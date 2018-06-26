<?php

# Include the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Import the Google Cloud client library
use google\appengine\api\taskqueue\PushTask;

/**
 * Create a task to load Cloud Datastore product entities with a given SKU Kind.
 *
 */
function schedule_load()
{
  $task_options = [
    $delay_seconds => 600,
  ];
  
  $task = new PushTask(
    '/loaddatastore.php');
  $task_name = $task->add('dataload');
}

?>
