<?php

namespace Moriony\FineProxy;

use Buzz\Browser;
use Buzz\Client\ClientInterface;
use Moriony\FineProxy\Exception\AuthorizationError;
use Moriony\FineProxy\Exception\UnexpectedHttpResponse;

class Client
{
    const URL_GET_PROXY_PATTERN = 'http://account.fineproxy.org/api/getproxy/?format=txt&type=%s&login=%s&password=%s';

    const TYPE_HTTP_AUTH = 'httpauth';
    const TYPE_SOCKS5_AUTH = 'socksauth';
    const TYPE_HTTP_IP = 'httpip';
    const TYPE_SOCKS5_IP = 'socksip';

    const AUTH_ERROR_CONTENT = 'AUTH ERROR';

    protected $login;
    protected $password;
    protected $browser;

    public function __construct($login, $password, ClientInterface $client = null)
    {
        $this->login = $login;
        $this->password = $password;
        $this->browser = new Browser($client);
    }

    protected function getProxyList($type, $showAuth = false)
    {
        $url = sprintf(self::URL_GET_PROXY_PATTERN, $type, $this->login, $this->password);
        /** @var \Buzz\Message\Response $response */
        $response = $this->browser->get($url);

        if (!$response->isSuccessful()) {
            throw new UnexpectedHttpResponse;
        }

        if (self::AUTH_ERROR_CONTENT == $response->getContent()) {
            throw new AuthorizationError;
        }

        $proxies = preg_split("/(\r\n|\n\r|\n|\r)/", trim($response->getContent()));
        if ($showAuth) {
            $proxies = $this->prepareAuthData($proxies);
        }
        return $proxies;
    }

    protected function prepareAuthData(array $proxies)
    {
        $result = [];
        foreach ($proxies as $proxy) {
            $result[] = sprintf('%s:%s@%s', $this->login, $this->password, $proxy);
        }
        return $result;
    }

    public function getHttpAuthProxies($showAuth = true)
    {
        return $this->getProxyList(self::TYPE_HTTP_AUTH, $showAuth);
    }

    public function getHttpIpProxies($showAuth = false)
    {
        return $this->getProxyList(self::TYPE_HTTP_IP, $showAuth);
    }

    public function getSock5AuthProxies($showAuth = true)
    {
        return $this->getProxyList(self::TYPE_SOCKS5_AUTH, $showAuth);
    }

    public function getSock5IpProxies($showAuth = false)
    {
        return $this->getProxyList(self::TYPE_SOCKS5_IP, $showAuth);
    }
}