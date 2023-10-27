<?php

declare(strict_types=1);

namespace TimeBenchmark;

use function hrtime;

final class Stopwatch
{
    private int|float|null $startTime = null;
    private int|float|null $stopTime = null;
    private array $stepTimes = [];
    private array $pauseTimes = [];
    private array $resumeTimes = [];

    private function __construct()
    {
    }

    /**
     * Create a new stopwatch.
     */
    public static function create(): Stopwatch
    {
        return new Stopwatch();
    }

    /**
     * Create a new stopwatch and start it.
     */
    public static function createStarted(): Stopwatch
    {
        $instance = new Stopwatch();
        $instance->startTime = hrtime(true);

        return $instance;
    }

    /**
     * Start the stopwatch.
     * @throws StopwatchException if the stopwatch was already started
     */
    public function start(): void
    {
        $this->validateWasNotStarted();
        $this->startTime = hrtime(true);
    }

    /**
     * Stop the stopwatch.
     * @throws StopwatchException if the stopwatch is not running
     */
    public function stop(): void
    {
        $this->validateIsRunning();
        $this->stopTime = hrtime(true);
    }

    /**
     * Marks a step/lap on the stopwatch.
     * @throws StopwatchException if the stopwatch is not running or there is a step name duplication
     */
    public function step(string $name): void
    {
        $this->validateIsRunning();
        if (isset($this->stepTimes[$name])) {
            throw new StopwatchException('Step "' . $name . '" already used');
        }
        $this->stepTimes[$name] = hrtime(true);
    }

    /**
     * Pause the stopwatch. While paused, it can be stopped or a step can be marked.
     * @throws StopwatchException if the stopwatch is not running or is already paused
     */
    public function pause(): void
    {
        $this->validateIsRunning();
        $this->validateIsNotPaused();
        $this->pauseTimes[] = hrtime(true);
    }

    /**
     * Resume a paused stopwatch.
     * @throws StopwatchException if the stopwatch is not running or is not paused
     */
    public function resume(): void
    {
        $this->validateIsRunning();
        $this->validateIsPaused();
        $this->resumeTimes[] = hrtime(true);
    }

    /**
     * Returns true if the stopwatch was started. After starting it, this method returns true even if stopped or paused.
     */
    public function wasStarted(): bool
    {
        return null !== $this->startTime;
    }

    /**
     * Returns true if the stopwatch was started and not yet stopped. This method will return true even if it is paused or resumed.
     */
    public function isRunning(): bool
    {
        return null !== $this->startTime && null === $this->stopTime;
    }

    /**
     * Returns true if the stopwatch was stopped. This is a final state, and after stopping it will always return true.
     */
    public function wasStopped(): bool
    {
        return null !== $this->stopTime;
    }

    /**
     * Returns true if the stopwatch is paused. When a paused stopwatch is stopped, it is not considered paused anymore.
     */
    public function isPaused(): bool
    {
        return null !== $this->startTime && null === $this->stopTime && count($this->pauseTimes) === 1 + count($this->resumeTimes);
    }

    /**
     * Returns the number of steps marked so far
     */
    public function getStepsNumber(): int
    {
        return count($this->stepTimes);
    }

    /**
     * Calculate the elapsed seconds since the stopwatch started until it was stopped.
     * If the stopwatch is not stopped, it will show the current value.
     * @throws StopwatchException if the stopwatch was not started
     */
    public function getElapsedSeconds(): float
    {
        return $this->computeElapsed(1_000_000_000);
    }

    /**
     * Calculate the elapsed milliseconds since the stopwatch started until it was stopped.
     * If the stopwatch is not stopped, it will show the current value.
     * @throws StopwatchException if the stopwatch was not started
     */
    public function getElapsedMilliseconds(): float
    {
        return $this->computeElapsed(1_000_000);
    }

    /**
     * Calculate the elapsed microseconds since the stopwatch started until it was stopped.
     * If the stopwatch is not stopped, it will show the current value.
     * @throws StopwatchException if the stopwatch was not started
     */
    public function getElapsedMicroseconds(): float
    {
        return $this->computeElapsed(1_000);
    }

    /**
     * Calculate the elapsed seconds for each step marked since the stopwatch started until each step.
     * The result will be an array with the step name as keys.
     * @return array<string, float>
     * @throws StopwatchException if the stopwatch was not started
     */
    public function getElapsedStepsSeconds(): array
    {
        return $this->computeElapsedSteps(1_000_000_000);
    }

    /**
     * Calculate the elapsed milliseconds for each step marked since the stopwatch started until each step.
     * The result will be an array with the step name as keys.
     * @return array<string, float>
     * @throws StopwatchException if the stopwatch was not started
     */
    public function getElapsedStepsMilliseconds(): array
    {
        return $this->computeElapsedSteps(1_000_000);
    }

    /**
     * Calculate the elapsed microseconds for each step marked since the stopwatch started until each step.
     * The result will be an array with the step name as keys.
     * @return array<string, float>
     * @throws StopwatchException if the stopwatch was not started
     */
    public function getElapsedStepsMicroseconds(): array
    {
        return $this->computeElapsedSteps(1_000);
    }

    /**
     * @throws StopwatchException if the stopwatch was not started
     */
    private function computeElapsed(int $divider): float
    {
        $this->validateWasStarted();

        if (null === $this->stopTime) {
            $stopTime = hrtime(true);
        } else {
            $stopTime = $this->stopTime;
        }

        $pauseDifference = 0;
        foreach ($this->pauseTimes as $pauseTimeIndex => $pauseTime) {
            if (isset($this->resumeTimes[$pauseTimeIndex])) {
                $pauseDifference += $this->resumeTimes[$pauseTimeIndex] - $pauseTime;
            } else {
                $stopTime = $pauseTime;
            }
        }

        return ($stopTime - $this->startTime - $pauseDifference) / $divider;
    }

    /**
     * @return array<string, float>
     * @throws StopwatchException if the stopwatch was not started
     */
    private function computeElapsedSteps(int $divider): array
    {
        $this->validateWasStarted();

        $differenceSteps = [];
        $pauseDifference = 0;
        $pauseTimeIndex = 0;
        $pauseState = false;
        $pauseLastTime = null;
        foreach ($this->stepTimes as $stepName => $stepTime) {
            $stepReached = false;
            do {
                if (!$pauseState) {
                    if (isset($this->pauseTimes[$pauseTimeIndex])) {
                        $pauseTime = $this->pauseTimes[$pauseTimeIndex];
                        if ($pauseTime < $stepTime) {
                            $pauseState = true;
                            $pauseLastTime = $pauseTime;
                        } else {
                            $stepReached = true;
                        }
                    } else {
                        $stepReached = true;
                    }
                } else {
                    if (isset($this->resumeTimes[$pauseTimeIndex])) {
                        $resumeTime = $this->resumeTimes[$pauseTimeIndex];
                        if ($resumeTime < $stepTime) {
                            $pauseState = false;
                            $pauseTimeIndex++;
                            $pauseDifference += $resumeTime - $pauseLastTime;
                        } else {
                            $stepTime = $pauseLastTime;
                            $stepReached = true;
                        }
                    } else {
                        $stepTime = $pauseLastTime;
                        $stepReached = true;
                    }
                }
            } while (!$stepReached);
            $differenceSteps[$stepName] = ($stepTime - $this->startTime - $pauseDifference) / $divider;
        }

        return $differenceSteps;
    }

    /**
     * @throws StopwatchException
     */
    private function validateWasNotStarted(): void
    {
        if (null !== $this->startTime) {
            throw new StopwatchException('Stopwatch was already started');
        }
    }

    /**
     * @throws StopwatchException
     */
    private function validateWasStarted(): void
    {
        if (null === $this->startTime) {
            throw new StopwatchException('Stopwatch was not started');
        }
    }

    /**
     * @throws StopwatchException
     */
    private function validateWasNotStopped(): void
    {
        if (null !== $this->stopTime) {
            throw new StopwatchException('Stopwatch was already stopped');
        }
    }

    /**
     * @throws StopwatchException
     */
    private function validateIsRunning(): void
    {
        $this->validateWasStarted();
        $this->validateWasNotStopped();
    }

    /**
     * @throws StopwatchException
     */
    private function validateIsNotPaused(): void
    {
        if (count($this->pauseTimes) === 1 + count($this->resumeTimes)) {
            throw new StopwatchException('Stopwatch is already paused');
        }
    }

    /**
     * @throws StopwatchException
     */
    private function validateIsPaused(): void
    {
        if (count($this->pauseTimes) === count($this->resumeTimes)) {
            throw new StopwatchException('Stopwatch is not paused');
        }
    }
}
