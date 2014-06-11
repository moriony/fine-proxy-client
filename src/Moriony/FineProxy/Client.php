<?php

namespace Moriony\FineProxy;

use Buzz\Browser;
use Buzz\Client\ClientInterface;
use Moriony\FineProxy\Exception\AuthorizationError;
use Moriony\FineProxy\Exception\UnexpectedHttpResponse;
use Moriony\FineProxy\Exception\UnexpectedProxyType;

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

    protected static $proxyTypes = [
        self::TYPE_SOCKS5_IP,
        self::TYPE_HTTP_IP,
        self::TYPE_HTTP_AUTH,
        self::TYPE_SOCKS5_AUTH
    ];

    public function __construct($login, $password, ClientInterface $client = null)
    {
        $this->login = $login;
        $this->password = $password;
        $this->browser = new Browser($client);
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function getProxyList($type)
    {
        if (!in_array($type, self::$proxyTypes)) {
            throw new UnexpectedProxyType;
        }

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
        return $proxies;
    }

    public function getHttpAuthProxies()
    {
        return $this->getProxyList(self::TYPE_HTTP_AUTH);
    }

    public function getHttpIpProxies()
    {
        return $this->getProxyList(self::TYPE_HTTP_IP);
    }

    public function getSock5AuthProxies()
    {
        return $this->getProxyList(self::TYPE_SOCKS5_AUTH);
    }

    public function getSock5IpProxies()
    {
        return $this->getProxyList(self::TYPE_SOCKS5_IP);
    }
}