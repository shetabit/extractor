<?php

namespace Shetabit\Extractor\Traits;

trait HttpURL
{
    /**
     * Parse HTTP url
     *
     * @param string $url
     * @return array
     */
    public function parseURL(string $url) : array
    {
        return (array) parse_url($url);
    }
}
