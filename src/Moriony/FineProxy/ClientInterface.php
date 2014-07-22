<?php

namespace Moriony\FineProxy;

interface ClientInterface
{
    public function getLogin();
    public function getPassword();
    public function getProxyList($type);
    public function getHttpAuthProxies();
    public function getHttpIpProxies();
    public function getSock5AuthProxies();
    public function getSock5IpProxies();
}