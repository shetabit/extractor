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

    /**
    * Parse Query string and convert it to an associative array
    *
    * @param string $queryString
    * @return array
    */
    public function parseQueryString(string $queryString)
    {
        return parse_str($queryString);
    }
}
