<?php

use Moriony\FineProxy\Client;
use Moriony\FineProxy\Layer\ZF1Cache;

include getcwd() . '../vendor/autoload.php';

// Create fine proxy client
$client = new Client('login', 'password');

// Create file cache adapter
$cacheAdapter = Zend_Cache::factory('Core', 'File', array(
    'automatic_serialization' => true,
));

// Create ZF1Cache layer and use it to get proxies list
$cacheLayer = new ZF1Cache($client, $cacheAdapter, array(
    ZF1Cache::OPT_LIFETIME => 172800,
    ZF1Cache::OPT_CACHE_KEY_PREFIX => 'fine_proxy_list',
    ZF1Cache::OPT_AUTO_UPDATE => true
));
$proxies = $cacheLayer->getHttpAuthProxies();

var_dump($proxies);