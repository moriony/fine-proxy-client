<?php

namespace Moriony\FineProxy\Layer;

use Moriony\FineProxy\ClientInterface;

interface LayerInterface extends ClientInterface
{
    public function getClient();
}