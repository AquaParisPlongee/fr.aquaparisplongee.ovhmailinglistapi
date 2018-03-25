<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/credential.php';
use \Ovh\Api;

// Information about your web hosting
$web_hosting = 'aquaparisplongee.fr';

// Get servers list
$conn = new Api($applicationKey,
                $applicationSecret,
                $endpoint,
                $GET_consumer_key);
$hosting = $conn->get('/hosting/web/' . $web_hosting );
print_r( $conn->get('/hosting/web/offerCapabilities', array( 'offer' => $hosting['offer']) ) );

?>
