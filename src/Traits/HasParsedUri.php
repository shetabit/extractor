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
}
