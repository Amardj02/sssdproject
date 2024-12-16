<?php




$ch = curl_init("https://data.iana.org/TLD/tlds-alpha-by-domain.txt");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

curl_close($ch);
if ($response === false) {
    exit('Could not retrieve data from the API.');
}

$tld_array = explode("\n", $response);
$tld_array = array_slice($tld_array, 1); 
$tld_array = array_map('strtolower', $tld_array);
$tld_array = array_map('trim', $tld_array);





