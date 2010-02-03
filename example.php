<?php
include './EpiCurl.php';
$mc = EpiCurl::getInstance();

// call yahoo
$ch = curl_init('http://www.yahoo.com');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$yahoo = $mc->addCurl($ch);

// call google
$ch = curl_init('http://www.google.com');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$google = $mc->addCurl($ch);

// call ebay and microsoft
$ch = curl_init('http://www.ebay.com');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$ebay = $mc->addCurl($ch);

// fetch response from yahoo and google
echo "The response code from Yahoo! was {$yahoo->code}\n";
echo "The response code from Google was {$google->code}\n";

$ch = curl_init('http://www.microsoft.com');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$microsoft = $mc->addCurl($ch);

// fetch response from ebay and microsoft
echo "The response code from Ebay was {$ebay->code}\n";
echo "The response code from Microsoft was {$microsoft->code}\n";
