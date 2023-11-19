<?php

declare(strict_types=1);

namespace TimeBenchmark;

interface StopwatchInterface
{
    /**
     * Start the stopwatch.
     * @throws StopwatchException if the stopwatch was already started
     */
    public function start(): void;

    /**
     * Stop the stopwatch.
     * @throws StopwatchException if the stopwatch is not running
     */
    public function stop(): void;

    /**
     * Marks a step/lap on the stopwatch.
     * @throws StopwatchException if the stopwatch is not running or there is a step name duplication
     */
    public function step(string $name): void;

    /**
     * Pause the stopwatch. While paused, it can be stopped or a step can be marked.
     * @throws StopwatchException if the stopwatch is not running or is already paused
     */
    public function pause(): void;

    /**
     * Resume a paused stopwatch.
     * @throws StopwatchException if the stopwatch is not running or is not paused
     */
    public function resume(): void;

    /**
     * Returns true if the stopwatch was started. After starting it, this method returns true even if stopped or paused.
     */
    public function wasStarted(): bool;

    /**
     * Returns true if the stopwatch was started and not yet stopped. This method will return true even if it is paused or resumed.
     */
    public function isRunning(): bool;

    /**
     * Returns true if the stopwatch was stopped. This is a final state, and after stopping it will always return true.
     */
    public function wasStopped(): bool;

    /**
     * Returns true if the stopwatch is paused. When a paused stopwatch is stopped, it is not considered paused anymore.
     */
    public function isPaused(): bool;

    /**
     * Returns the number of steps marked so far
     */
    public function getStepsNumber(): int;

    /**
     * Calculate the elapsed seconds since the stopwatch started until it was stopped.
     * If the stopwatch is not stopped, it will show the current value.
     * @throws StopwatchException if the stopwatch was not started
     */
    public function getElapsedSeconds(): float;

    /**
     * Calculate the elapsed milliseconds since the stopwatch started until it was stopped.
     * If the stopwatch is not stopped, it will show the current value.
     * @throws StopwatchException if the stopwatch was not started
     */
    public function getElapsedMilliseconds(): float;

    /**
     * Calculate the elapsed microseconds since the stopwatch started until it was stopped.
     * If the stopwatch is not stopped, it will show the current value.
     * @throws StopwatchException if the stopwatch was not started
     */
    public function getElapsedMicroseconds(): float;

    /**
     * Calculate the elapsed seconds for each step marked since the stopwatch started until each step.
     * The result will be an array with the step name as keys.
     * @return array<string, float>
     * @throws StopwatchException if the stopwatch was not started
     */
    public function getElapsedStepsSeconds(): array;

    /**
     * Calculate the elapsed milliseconds for each step marked since the stopwatch started until each step.
     * The result will be an array with the step name as keys.
     * @return array<string, float>
     * @throws StopwatchException if the stopwatch was not started
     */
    public function getElapsedStepsMilliseconds(): array;

    /**
     * Calculate the elapsed microseconds for each step marked since the stopwatch started until each step.
     * The result will be an array with the step name as keys.
     * @return array<string, float>
     * @throws StopwatchException if the stopwatch was not started
     */
    public function getElapsedStepsMicroseconds(): array;
}
