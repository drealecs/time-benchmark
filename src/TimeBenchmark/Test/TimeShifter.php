<?php

namespace TimeBenchmark\Test;

use function hrtime;

class TimeShifter
{
    private readonly float|null $startTime;

    private float $timeShift = 0.;

    public function __construct(private readonly bool $normalTimeFlow = true)
    {
        if ($this->normalTimeFlow) {
            $this->startTime = hrtime(true) / 1e9;
        }
    }

    public function getTimeShift(): false|float|int
    {
        if ($this->normalTimeFlow) {
            return (hrtime(true) / 1e9 - $this->startTime) + $this->timeShift;
        }

        return $this->timeShift;
    }

    /**
     * @param float $timeShift The time shift in seconds.
     * @return void
     */
    public function addTimeShift(float $timeShift): void
    {
        $this->timeShift += $timeShift;
    }
}
