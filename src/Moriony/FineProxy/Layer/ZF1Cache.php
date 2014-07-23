<?php

namespace Moriony\FineProxy\Layer;

use Moriony\FineProxy\ClientInterface;
use Moriony\FineProxy\Client;
use Moriony\FineProxy\Exception\InvalidCacheAdapter;

class ZF1Cache implements CacheLayerInterface
{
    const OPT_LIFETIME = 'lifetime';
    const OPT_CACHE_KEY_PREFIX = 'cacheKeyPrefix';
    const OPT_AUTO_UPDATE = 'autoUpdate';

    protected $client;
    protected $cacheAdapter;
    protected $lifeTime;
    protected $autoUpdate;

    /**
     * @param ClientInterface $client
     * @param \Zend_Cache_Backend_Interface|\Zend_Cache_Core $cacheAdapter
     * @param array $options
     * @throws \Moriony\FineProxy\Exception\InvalidCacheAdapter
     */
    public function __construct(ClientInterface $client, $cacheAdapter, array $options = array())
    {
        $validCache = $cacheAdapter instanceof \Zend_Cache_Backend_Interface ||
                      $cacheAdapter instanceof \Zend_Cache_Core;

        if (!$validCache) {
            throw new InvalidCacheAdapter;
        }

        $this->client = $client;
        $this->cache = $cacheAdapter;
        $this->lifeTime = array_key_exists(self::OPT_LIFETIME, $options)
                                ? (int) $options[self::OPT_LIFETIME] : null;
        $this->cacheKeyPrefix = array_key_exists(self::OPT_CACHE_KEY_PREFIX, $options)
                                ? (string) $options[self::OPT_CACHE_KEY_PREFIX] : null;
        $this->autoUpdate = array_key_exists(self::OPT_AUTO_UPDATE, $options)
                                ? (bool) $options[self::OPT_AUTO_UPDATE] : true;
    }

    public function getLogin()
    {
        return $this->client->getLogin();
    }

    public function getPassword()
    {
        return $this->client->getPassword();
    }

    protected function prepareCacheKey($type)
    {
        return sprintf('%s_%s_%s', $this->cacheKeyPrefix, $this->client->getLogin(), $type);
    }

    public function updateProxyListCache($type)
    {
        $proxies = $this->client->getProxyList($type);
        $this->cache->save($proxies, $this->prepareCacheKey($type), [], $this->lifeTime);
        return $proxies;
    }

    public function getProxyList($type)
    {
        $proxies = $this->cache->load($this->prepareCacheKey($type));
        if (!$proxies && $this->autoUpdate) {
            $proxies = $this->updateProxyListCache($type);
        }
        if (!$proxies) {
            $proxies = array();
        }
        return $proxies;
    }

    public function getHttpAuthProxies()
    {
        return $this->getProxyList(Client::TYPE_HTTP_AUTH);
    }

    public function getHttpIpProxies()
    {
        return $this->getProxyList(Client::TYPE_HTTP_IP);
    }

    public function getSock5AuthProxies()
    {
        return $this->getProxyList(Client::TYPE_SOCKS5_AUTH);
    }

    public function getSock5IpProxies()
    {
        return $this->getProxyList(Client::TYPE_SOCKS5_IP);
    }

    /**
     * @return ClientInterface|LayerInterface|CacheLayerInterface
     */
    public function getClient()
    {
        return $this->client;
    }
}