<?php
include './EpiCurl.php';
$mc = EpiCurl::getInstance();

$mc->addURL('http://www.yahoo.com'); // call yahoo
$mc->addURL('http://www.google.com'); // call google
$mc->addURL('http://www.ebay.com'); // call ebay

// fetch response from yahoo and google
echo "The response code from Yahoo! was {$yahoo->code}\n";
echo "The response code from Google was {$google->code}\n";

$mc->addURL('http://www.microsoft.com'); // call microsoft

// fetch response from ebay and microsoft
echo "The response code from Ebay was {$ebay->code}\n";
echo "The response code from Microsoft was {$microsoft->code}\n";
