<?php

declare(strict_types=1);

namespace TimeBenchmark\Test;

use TimeBenchmark\StopwatchFactoryInterface;
use TimeBenchmark\StopwatchInterface;

final class TestStopwatchFactory implements StopwatchFactoryInterface
{
    private readonly TimeShifter $timeShifter;

    public function __construct(TimeShifter $timeShifter = null)
    {
        $this->timeShifter = $timeShifter ?? new TimeShifter();
    }

    /**
     * Create a new stopwatch.
     */
    public function create(): StopwatchInterface
    {
        return TestStopwatch::create($this->timeShifter);
    }

    /**
     * Create a new stopwatch and start it.
     */
    public function createStarted(): StopwatchInterface
    {
        return TestStopwatch::createStarted($this->timeShifter);
    }
}
