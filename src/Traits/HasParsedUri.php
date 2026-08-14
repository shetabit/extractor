<?php

namespace Shetabit\Extractor\Traits;

trait HasParsedUri
{
    use HttpURL;

    /**
     * Retrieve the uri that is parsed.
     */
    abstract public function getUri() : string;

    /**
     * Parse uri
     *
     * @return array<string, int|string>
     */
    public function getParsedUri() : array
    {
        return $this->parseURL($this->getUri());
    }

    /**
     * Parse query string
     *
     * @return array<array-key, mixed>
     */
    public function getParsedQueryString() : array
    {
        $queryString = $this->getParsedUri()['query'] ?? null;

        return $this->parseQueryString($queryString === null ? null : (string) $queryString);
    }
}
