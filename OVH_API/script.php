#!/usr/bin/php
<?php

// This script is a skeleton of script or application using OVH APIs
// You can launch it with the command php script.php
// Or include it into a website be deleting the first line

require __DIR__ . '/vendor/autoload.php';
use \Ovh\Api;
use GuzzleHttp\Client;

////////////////////////////////////////////////////
//     Dont forget to update your credentials     //
////////////////////////////////////////////////////

// Please read the documentation to get your credentials
// https://github.com/ovh/php-ovh
$applicationKey = "your_app_key";
$applicationSecret = "your_app_secret";
$consumer_key = "your_consumer_key";

// Information about API and rights asked
$endpoint = 'ovh-eu';

// Get API connector instance
$conn = new Api(    $applicationKey,
                    $applicationSecret,
                    $endpoint,
                    $consumer_key);

////////////////////////////////////////////////////
//       Your logic will be inserted here         //
////////////////////////////////////////////////////

// This is an example
// Here, use the API connector as you want. Here, we are getting all hosting services
$webHosting = $conn->get('/hosting/web/');

// For each hosting, we create a new FTP user
foreach ($webHosting as $webHosting) {
	$webHostingData = $conn->get('/hosting/web/'. $webHosting );
	$primaryLogin = $webHostingData['primaryLogin'];

	$task = $conn->post('/hosting/web/'. $webHosting .'/user', array(
		"home" 		=> "www/",
		"login" 	=> $primaryLogin . "-dev",
		"password"	=> "mySecretPass42",
	) );

	echo "User will be add on hosting '$webHosting' when task ". $task['id'] . " will be ended" . PHP_EOL;
}
