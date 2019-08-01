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
    function getParsedUri()
    {
        return $this->parseURL($this->getUri());
    }
}
