<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/credential.php';
use \Ovh\Api;


// Information about your web hosting
$domain = 'aquaparisplongee.fr';
$list = 'test';

// Get servers list
$ovh = new Api($applicationKey,
                $applicationSecret,
                $endpoint,
                $GET_consumer_key);

$result = $ovh->get('/email/domain/' . $domain . '/mailingList/' . $list . '/subscriber');
print_r( $result );

?>
