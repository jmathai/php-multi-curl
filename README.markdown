PHP-multi-curl is a high performance multi curl library which can be used to parallel http web service calls.  It has gone through various revisions and was most widely used as part of the twitter-async project.  It's the single most useful library I've written and has been integrated into various libraries for Twitter, Foursquare, Paypal and Mail Chimp.

One of the key aspects that differentiates it from many similar multi curl libraries is that it allows you to cherry pick responses.  You can fire off 10 http calls and retrieve results for a specific one without having to wait for all 10 to be completed.  If you rely heavily on web services (as I do) then it'll be a tool you can't live without.

Usage
<?php
  $mc = EpiCurl::getInstance();
  $yahoo = $mc->addURL('http://www.yahoo.com/');
  $google = $mc->addURL('http://www.google.com/');

  echo "The response code for Yahoo! was {$yahoo->code} and Google was {$google->code}";
?>

Authors
   * jmathai
   
Contributors
   * Lewis Cowles (LewisCowles1986) - Usability for adding url's without needing to worry about CURL, but provisioning also for specifying additional parameters

Documentation:
   * http://wiki.github.com/jmathai/php-multi-curl
