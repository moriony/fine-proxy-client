<?php

namespace Moriony\FineProxy\Layer;

interface CacheLayerInterface extends LayerInterface
{
    public function updateProxyListCache($type);
}