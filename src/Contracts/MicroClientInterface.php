<?php

namespace Shetabit\Extractor\Contracts;

interface MicroClientInterface
{
    /**
     * Run client
     */
    public function run() : ResponseInterface|null;
}
