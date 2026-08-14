<?php

namespace Shetabit\Extractor\Traits;

trait HttpURL
{
    /**
     * Parse HTTP url
     *
     * @return array<string, int|string>
     */
    public function parseURL(string $url) : array
    {
        $parsed = parse_url($url);

        return is_array($parsed) ? $parsed : [];
    }

    /**
     * Parse Query string and convert it to an associative array
     *
     * @return array<array-key, mixed>
     */
    public function parseQueryString(string|null $queryString) : array
    {
        $query = [];

        if ($queryString !== null && $queryString !== '') {
            parse_str($queryString, $query);
        }

        return $query;
    }
}
