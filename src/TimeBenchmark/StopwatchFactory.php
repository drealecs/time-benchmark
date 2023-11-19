<?php

declare(strict_types=1);

namespace TimeBenchmark;

final class StopwatchFactory implements StopwatchFactoryInterface
{
    /**
     * Create a new stopwatch.
     */
    public function create(): StopwatchInterface
    {
        return Stopwatch::create();
    }

    /**
     * Create a new stopwatch and start it.
     */
    public function createStarted(): StopwatchInterface
    {
        return Stopwatch::createStarted();
    }
}
