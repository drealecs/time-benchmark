<?php

declare(strict_types=1);

namespace TimeBenchmark\Test;

use TimeBenchmark\Stopwatch;
use TimeBenchmark\StopwatchException;
use TimeBenchmark\StopwatchInterface;

final class TestStopwatch implements StopwatchInterface
{
    private float|null $startTimeShift = null;
    private float|null $stopTimeShift = null;
    private array $stepTimeShifts = [];
    private array $pauseTimeShifts = [];
    private array $resumeTimeShifts = [];

    private function __construct(private readonly Stopwatch $stopwatch, private readonly TimeShifter $timeShifter)
    {
    }

    /**
     * Create a new stopwatch.
     */
    public static function create(TimeShifter $timeShifter): TestStopwatch
    {
        return new TestStopwatch(Stopwatch::create(), $timeShifter);
    }

    /**
     * Create a new stopwatch and start it.
     */
    public static function createStarted(TimeShifter $timeShifter): TestStopwatch
    {
        $instance = new TestStopwatch(Stopwatch::createStarted(), $timeShifter);
        $instance->startTimeShift = $timeShifter->getTimeShift();

        return $instance;
    }

    /**
     * Start the stopwatch.
     * @throws StopwatchException if the stopwatch was already started
     */
    public function start(): void
    {
        $this->stopwatch->start();
        $this->startTimeShift = $this->timeShifter->getTimeShift();
    }

    /**
     * Stop the stopwatch.
     * @throws StopwatchException if the stopwatch is not running
     */
    public function stop(): void
    {
        $this->stopwatch->stop();
        $this->stopTimeShift = $this->timeShifter->getTimeShift();
    }

    /**
     * Marks a step/lap on the stopwatch.
     * @throws StopwatchException if the stopwatch is not running or there is a step name duplication
     */
    public function step(string $name): void
    {
        $this->stopwatch->step($name);
        $this->stepTimeShifts[$name] = $this->timeShifter->getTimeShift();
    }

    /**
     * Pause the stopwatch. While paused, it can be stopped or a step can be marked.
     * @throws StopwatchException if the stopwatch is not running or is already paused
     */
    public function pause(): void
    {
        $this->stopwatch->pause();
        $this->pauseTimeShifts[] = $this->timeShifter->getTimeShift();
    }

    /**
     * Resume a paused stopwatch.
     * @throws StopwatchException if the stopwatch is not running or is not paused
     */
    public function resume(): void
    {
        $this->stopwatch->resume();
        $this->resumeTimeShifts[] = $this->timeShifter->getTimeShift();
    }

    /**
     * Returns true if the stopwatch was started. After starting it, this method returns true even if stopped or paused.
     */
    public function wasStarted(): bool
    {
        return $this->stopwatch->wasStarted();
    }

    /**
     * Returns true if the stopwatch was started and not yet stopped. This method will return true even if it is paused or resumed.
     */
    public function isRunning(): bool
    {
        return $this->stopwatch->isRunning();
    }

    /**
     * Returns true if the stopwatch was stopped. This is a final state, and after stopping it will always return true.
     */
    public function wasStopped(): bool
    {
        return $this->stopwatch->wasStopped();
    }

    /**
     * Returns true if the stopwatch is paused. When a paused stopwatch is stopped, it is not considered paused anymore.
     */
    public function isPaused(): bool
    {
        return $this->stopwatch->isPaused();
    }

    /**
     * Returns the number of steps marked so far
     */
    public function getStepsNumber(): int
    {
        return $this->stopwatch->getStepsNumber();
    }

    /**
     * Calculate the elapsed seconds since the stopwatch started until it was stopped.
     * If the stopwatch is not stopped, it will show the current value.
     * @throws StopwatchException if the stopwatch was not started
     */
    public function getElapsedSeconds(): float
    {
        return $this->computeTimeShift($this->stopwatch->getElapsedSeconds(), 1);
    }

    /**
     * Calculate the elapsed milliseconds since the stopwatch started until it was stopped.
     * If the stopwatch is not stopped, it will show the current value.
     * @throws StopwatchException if the stopwatch was not started
     */
    public function getElapsedMilliseconds(): float
    {
        return $this->computeTimeShift($this->stopwatch->getElapsedMilliseconds(), 1_000);
    }

    /**
     * Calculate the elapsed microseconds since the stopwatch started until it was stopped.
     * If the stopwatch is not stopped, it will show the current value.
     * @throws StopwatchException if the stopwatch was not started
     */
    public function getElapsedMicroseconds(): float
    {
        return $this->computeTimeShift($this->stopwatch->getElapsedMicroseconds(), 1_000_000);
    }

    /**
     * Calculate the elapsed seconds for each step marked since the stopwatch started until each step.
     * The result will be an array with the step name as keys.
     * @return array<string, float>
     * @throws StopwatchException if the stopwatch was not started
     */
    public function getElapsedStepsSeconds(): array
    {
        return $this->computeTimeShiftSteps($this->stopwatch->getElapsedStepsSeconds(), 1);
    }

    /**
     * Calculate the elapsed milliseconds for each step marked since the stopwatch started until each step.
     * The result will be an array with the step name as keys.
     * @return array<string, float>
     * @throws StopwatchException if the stopwatch was not started
     */
    public function getElapsedStepsMilliseconds(): array
    {
        return $this->computeTimeShiftSteps($this->stopwatch->getElapsedStepsMilliseconds(), 1_000);
    }

    /**
     * Calculate the elapsed microseconds for each step marked since the stopwatch started until each step.
     * The result will be an array with the step name as keys.
     * @return array<string, float>
     * @throws StopwatchException if the stopwatch was not started
     */
    public function getElapsedStepsMicroseconds(): array
    {
        return $this->computeTimeShiftSteps($this->stopwatch->getElapsedStepsMicroseconds(), 1_000_000);
    }

    private function computeTimeShift(float $elapsed, int $multiplier): float
    {
        if (null === $this->stopTimeShift) {
            $stopTimeShift = $this->timeShifter->getTimeShift();
        } else {
            $stopTimeShift = $this->stopTimeShift;
        }

        $pauseShiftDifference = 0;
        foreach ($this->pauseTimeShifts as $pauseTimeShiftIndex => $pauseTimeShift) {
            if (isset($this->resumeTimeShifts[$pauseTimeShiftIndex])) {
                $pauseShiftDifference += $this->resumeTimeShifts[$pauseTimeShiftIndex] - $pauseTimeShift;
            } else {
                $stopTimeShift = $pauseTimeShift;
            }
        }

        return $elapsed + ($stopTimeShift - $this->startTimeShift - $pauseShiftDifference) * $multiplier;
    }

    /**
     * @param array<string, float> $elapsedSteps
     * @param int $multiplier
     * @return array<string, float>
     */
    private function computeTimeShiftSteps(array $elapsedSteps, int $multiplier): array
    {
        $pauseShiftDifference = 0;
        $pauseTimeIndex = 0;
        $pauseState = false;
        $pauseLastTimeShift = null;
        foreach ($this->stepTimeShifts as $stepName => $stepTimeShift) {
            $stepReached = false;
            do {
                if (!$pauseState) {
                    if (isset($this->pauseTimeShifts[$pauseTimeIndex])) {
                        $pauseTimeShift = $this->pauseTimeShifts[$pauseTimeIndex];
                        if ($pauseTimeShift < $stepTimeShift) {
                            $pauseState = true;
                            $pauseLastTimeShift = $pauseTimeShift;
                        } else {
                            $stepReached = true;
                        }
                    } else {
                        $stepReached = true;
                    }
                } else {
                    if (isset($this->resumeTimeShifts[$pauseTimeIndex])) {
                        $resumeTimeShift = $this->resumeTimeShifts[$pauseTimeIndex];
                        if ($resumeTimeShift < $stepTimeShift) {
                            $pauseState = false;
                            $pauseTimeIndex++;
                            $pauseShiftDifference += $resumeTimeShift - $pauseLastTimeShift;
                        } else {
                            $stepTimeShift = $pauseLastTimeShift;
                            $stepReached = true;
                        }
                    } else {
                        $stepTimeShift = $pauseLastTimeShift;
                        $stepReached = true;
                    }
                }
            } while (!$stepReached);
            $elapsedSteps[$stepName] += ($stepTimeShift - $this->startTimeShift - $pauseShiftDifference) * $multiplier;
        }

        return $elapsedSteps;
    }
}
