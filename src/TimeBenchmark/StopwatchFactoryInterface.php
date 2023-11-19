<?php

declare(strict_types=1);

namespace TimeBenchmark;

interface StopwatchFactoryInterface
{
    /**
     * Create a new stopwatch.
     */
    public function create(): StopwatchInterface;

    /**
     * Create a new stopwatch and start it.
     */
    public function createStarted(): StopwatchInterface;
}
