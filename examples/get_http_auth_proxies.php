<?php

use Moriony\FineProxy\Client;

include getcwd() . '../vendor/autoload.php';

$client = new Client('login', 'password');
$proxies = $client->getHttpAuthProxies();

var_dump($proxies);