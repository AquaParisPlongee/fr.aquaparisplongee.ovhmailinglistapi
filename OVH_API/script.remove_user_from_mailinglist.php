<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/credential.php';
use \Ovh\Api;

// Information about your web hosting
$domain = 'aquaparisplongee.fr';
$list = 'test';
$email = 'author@aquaparisplongee.fr';

// Get servers list
$ovh = new Api($applicationKey,
                $applicationSecret,
                $endpoint,
                $DELETE_consumer_key);

$result = $ovh->delete('/email/domain/' . $domain . '/mailingList/' . $list . '/subscriber/' . $email);
print_r( $result );

?>
