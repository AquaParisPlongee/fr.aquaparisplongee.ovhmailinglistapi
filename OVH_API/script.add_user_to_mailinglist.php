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
                $POST_consumer_key);

$result = $ovh->post('/email/domain/' . $domain . '/mailingList/' . $list . '/subscriber',
                     array(
                        'email' => $email, // Required: Email of subscriber (type: string)
                    ));

print_r( $result );

?>
