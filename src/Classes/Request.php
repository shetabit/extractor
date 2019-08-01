<?php

namespace Shetabit\Extractor\Classes;

use Shetabit\Extractor\Abstracts\RequestAbstract;
use Shetabit\Extractor\Traits\HasParsedUri;

class Request extends RequestAbstract
{
    use HasParsedUri;

    /**
     * Request constructor.
     *
     * @param string $uri
     * @param string $method
     * @param string|null $body
     */
    public function __construct(string $uri = '/', string $method = 'GET', string $body = null)
    {
        $this->setUri($uri);
        $this->setMethod($method);
        $this->setBody($body);
    }
}
