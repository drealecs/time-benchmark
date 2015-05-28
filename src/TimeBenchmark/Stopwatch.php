<?php
namespace TimeBenchmark;

final class Stopwatch
{
    private $startTime = null;
    private $stopTime = null;
    private $stepTimes = [];
    private $pauseTimes = [];
    private $resumeTimes = [];

    private function __construct()
    {
    }

    /**
     * Create a new stopwatch.
     * @return Stopwatch
     */
    public static function create()
    {
        return new Stopwatch();
    }

    /**
     * Create a new stopwatch and start it.
     * @return Stopwatch
     */
    public static function createStarted()
    {
        $instance = new Stopwatch();
        $instance->startTime = microtime(false);

        return $instance;
    }

    /**
     * Start the stopwatch.
     * @throws StopwatchException if the stopwatch was already started
     */
    public function start()
    {
        $this->validateWasNotStarted();
        $this->startTime = microtime(false);
    }

    /**
     * Stop the stopwatch.
     * @throws StopwatchException if the stopwatch is not running
     */
    public function stop()
    {
        $this->validateIsRunning();
        $this->stopTime = microtime(false);
    }

    /**
     * Marks a step/lap on the stopwatch.
     * @param string $name step name
     * @throws StopwatchException if the stopwatch is not running or there is a step name duplication
     */
    public function step($name)
    {
        $this->validateIsRunning();
        if (isset($this->stepTimes[$name])) {
            throw new StopwatchException('Step "' . $name . '" already used');
        }
        $this->stepTimes[$name] = microtime(false);
    }

    /**
     * Pause the stopwatch. While paused it can be stopped or a step can me marked.
     * @throws StopwatchException if the stopwatch is not running or is already paused
     */
    public function pause()
    {
        $this->validateIsRunning();
        $this->validateIsNotPaused();
        $this->pauseTimes[] = microtime(false);
    }

    /**
     * Resume a paused stopwatch.
     * @throws StopwatchException if the stopwatch is not running or is not paused
     */
    public function resume()
    {
        $this->validateIsRunning();
        $this->validateIsPaused();
        $this->resumeTimes[] = microtime(false);
    }

    /**
     * Returns true if the stopwatch was started. After starting it, this method returns true even if stopped or paused.
     * @return bool
     */
    public function wasStarted()
    {
        return null !== $this->startTime;
    }

    /**
     * Returns true if the stopwatch was started and not yet stopped. This method will return true even if it is paused or resumed.
     * @return bool
     */
    public function isRunning()
    {
        return null !== $this->startTime && null === $this->stopTime;
    }

    /**
     * Returns true if the stopwatch was stopped. This is a final state and after stop, it will always return true.
     * @return bool
     */
    public function wasStopped()
    {
        return null !== $this->stopTime;
    }

    /**
     * Returns true if the stopwatch is paused. When a paused stopwatch is stopped, it is not considered paused anymore.
     * @return bool
     */
    public function isPaused()
    {
        return null !== $this->startTime && null === $this->stopTime && count($this->pauseTimes) === 1 + count($this->resumeTimes);
    }

    /**
     * Returns the number of steps marked so far
     * @return int
     */
    public function getStepsNumber()
    {
        return count($this->stepTimes);
    }

    /**
     * Calculate the elapsed seconds since the stopwatch started until is was stopped. If the stopwatch is not stopped it will show the current value.
     * @return float
     */
    public function getElapsedSeconds()
    {
        return $this->computeElapsed();
    }

    /**
     * Calculate the elapsed milliseconds since the stopwatch started until is was stopped. If the stopwatch is not stopped it will show the current value.
     * @return float
     */
    public function getElapsedMilliseconds()
    {
        return $this->computeElapsed(1000);
    }

    /**
     * Calculate the elapsed microseconds since the stopwatch started until is was stopped. If the stopwatch is not stopped it will show the current value.
     * @return float
     */
    public function getElapsedMicroseconds()
    {
        return $this->computeElapsed(1000000);
    }

    /**
     * Calculate the elapsed seconds for each step marked since the stopwatch started until is was stopped. The result will be an array with the step name as keys.
     * @return float[]
     */
    public function getElapsedStepsSeconds()
    {
        return $this->computeElapsedSteps();
    }

    /**
     * Calculate the elapsed milliseconds for each step marked since the stopwatch started until is was stopped. The result will be an array with the step name as keys.
     * @return float[]
     */
    public function getElapsedStepsMilliseconds()
    {
        return $this->computeElapsedSteps(1000);
    }

    /**
     * Calculate the elapsed microseconds for each step marked since the stopwatch started until is was stopped. The result will be an array with the step name as keys.
     * @return float[]
     */
    public function getElapsedStepsMicroseconds()
    {
        return $this->computeElapsedSteps(1000000);
    }

    /**
     * @param int $multiplier
     * @return float
     * @throws StopwatchException if the stopwatch was not started
     */
    private function computeElapsed($multiplier = 1)
    {
        $this->validateWasStarted();

        if (null === $this->stopTime) {
            $stopTime = microtime(false);
        } else {
            $stopTime = $this->stopTime;
        }

        $pauseDifference = 0;
        foreach ($this->pauseTimes as $pauseTimeIndex => $pauseTime) {
            if (isset($this->resumeTimes[$pauseTimeIndex])) {
                $resumeTime = $this->resumeTimes[$pauseTimeIndex];
                $pauseDifference += $this->calculateDifference($pauseTime, $resumeTime, $multiplier);
            } else {
                $stopTime = $pauseTime;
            }
        }

        return $this->calculateDifference($this->startTime, $stopTime, $multiplier) - $pauseDifference;
    }

    /**
     * @param int $multiplier
     * @return float
     * @throws StopwatchException if the stopwatch was not started
     */
    private function computeElapsedSteps($multiplier = 1)
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
                            $pauseDifference += $this->calculateDifference($pauseLastTime, $resumeTime, $multiplier);
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
            $differenceSteps[$stepName] = $this->calculateDifference($this->startTime, $stepTime, $multiplier) - $pauseDifference;
        }

        return $differenceSteps;
    }

    /**
     * @param string $startTime
     * @param string $stopTime
     * @param int $multiplier
     * @return float
     */
    private function calculateDifference($startTime, $stopTime, $multiplier)
    {
        list ($startMilliseconds, $startSeconds) = explode(' ', $startTime);
        list ($endMilliseconds, $endSeconds) = explode(' ', $stopTime);

        return $multiplier * (($endSeconds - $startSeconds) + ($endMilliseconds - $startMilliseconds));
    }

    /**
     * @throws StopwatchException
     */
    private function validateWasNotStarted()
    {
        if (null !== $this->startTime) {
            throw new StopwatchException('Stopwatch was already started');
        }
    }

    /**
     * @throws StopwatchException
     */
    private function validateWasStarted()
    {
        if (null === $this->startTime) {
            throw new StopwatchException('Stopwatch was not started');
        }
    }

    /**
     * @throws StopwatchException
     */
    private function validateWasNotStopped()
    {
        if (null !== $this->stopTime) {
            throw new StopwatchException('Stopwatch was already stopped');
        }
    }

    /**
     * @throws StopwatchException
     */
    private function validateIsRunning()
    {
        $this->validateWasStarted();
        $this->validateWasNotStopped();
    }

    /**
     * @throws StopwatchException
     */
    private function validateIsNotPaused()
    {
        if (count($this->pauseTimes) === 1 + count($this->resumeTimes)) {
            throw new StopwatchException('Stopwatch is already paused');
        }
    }

    /**
     * @throws StopwatchException
     */
    private function validateIsPaused()
    {
        if (count($this->pauseTimes) === count($this->resumeTimes)) {
            throw new StopwatchException('Stopwatch is not paused');
        }
    }
}
