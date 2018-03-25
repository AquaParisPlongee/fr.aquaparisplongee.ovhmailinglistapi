<?php
// Informations about your application
$applicationKey = "Cjx5atTgE2kWERqi";
$applicationSecret = "vepSOUt4S8t2pJjkwgtrDbCMwAxT0hmZ";
// GET
$consumer_key = "N59ViO2vGW9Ue0WaO2dGhEZ74caH6XRX";
$GET_consumer_key = "DHXJTWuUXn9PqLI2p6tAzJR44kF1Rm1q";
// POST
$POST_consumer_key = "cUU2FaUvrKBj4isMSA4YzxQqcwlUnwXL";
// DELETE
$DELETE_consumer_key = "i3h1I0MogABcycg5iJziUKldUDFGey4Y";
//
// Information about API and rights asked
$endpoint = 'ovh-eu';

/*

 curl -XPOST -H"X-Ovh-Application: Cjx5atTgE2kWERqi" -H "Content-type: application/json" \
     https://eu.api.ovh.com/1.0/auth/credential  -d '{
         "accessRules": [
              {"method": "GET", "path": "/*"},
              {"method": "POST", "path": "/*"},
              {"method": "PUT", "path": "/*"},
              {"method": "DELETE", "path": "/*"}
        ]
    }'

curl --header "X-Ovh-Application: Cjx5atTgE2kWERqi" --data '{ "accessRules": [ {"method": "GET", "path": "/*"}, {"method": "POST", "path": "/*"}, {"method": "PUT", "path": "/*"}, {"method": "DELETE", "path": "/*"} ] }' https://eu.api.ovh.com/1.0/auth/credential

*/

?>
