<?php

namespace Shetabit\Extractor\Contracts;

interface MicroClientInterface
{
    /**
     * Run client
     *
     * @return ResponseInterface
     */
    public function run() : ResponseInterface;
}
