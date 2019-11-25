<?php

namespace Shetabit\Extractor\Traits;

trait HasParsedUri
{
    use HttpURL;

    /**
     * Parse uri
     *
     * @return array
     */
    public function getParsedUri()
    {
        return $this->parseURL($this->getUri());
    }

    /**
     * Parse query string
     *
     * @return array
     */
    public function getParsedQueryString()
    {
        $parsedUri = $this->getParsedUri();

        $queryString = $parsedUri['query'] ?? null;

        return $this->parseQueryString($queryString);
    }
}
