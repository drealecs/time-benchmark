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

    public static function createStarted()
    {
        $instance = new Stopwatch();
        $instance->startTime = microtime(false);

        return $instance;
    }

    public static function create()
    {
        return new Stopwatch();
    }

    public function start()
    {
        $this->validateWasNotStarted();
        $this->startTime = microtime(false);
    }

    public function stop()
    {
        $this->validateIsRunning();
        $this->stopTime = microtime(false);
    }

    public function step($name)
    {
        $this->validateIsRunning();
        if (isset($this->stepTimes[$name])) {
            throw new StopwatchException('Step "' . $name . '" already used');
        }
        $this->stepTimes[$name] = microtime(false);
    }

    public function pause()
    {
        $this->validateIsRunning();
        $this->validateIsNotPaused();
        $this->pauseTimes[] = microtime(false);
    }

    public function resume()
    {
        $this->validateIsRunning();
        $this->validateIsPaused();
        $this->resumeTimes[] = microtime(false);
    }

    public function wasStarted()
    {
        return null !== $this->startTime;
    }

    public function isRunning()
    {
        return null !== $this->startTime && null === $this->stopTime;
    }

    public function wasStopped()
    {
        return null !== $this->stopTime;
    }

    public function isPaused()
    {
        return null !== $this->startTime && null === $this->stopTime && count($this->pauseTimes) === 1 + count($this->resumeTimes);
    }

    public function getStepsNumber()
    {
        return count($this->stepTimes);
    }

    public function getElapsedSeconds()
    {
        return $this->computeElapsed();
    }

    public function getElapsedMilliseconds()
    {
        return $this->computeElapsed(1000);
    }

    public function getElapsedMicroseconds()
    {
        return $this->computeElapsed(1000000);
    }

    public function getElapsedStepsSeconds()
    {
        return $this->computeElapsedSteps();
    }

    public function getElapsedStepsMilliseconds()
    {
        return $this->computeElapsedSteps(1000);
    }

    public function getElapsedStepsMicroseconds()
    {
        return $this->computeElapsedSteps(1000000);
    }

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

    private function calculateDifference($startTime, $stopTime, $multiplier)
    {
        list ($startMilliseconds, $startSeconds) = explode(' ', $startTime);
        list ($endMilliseconds, $endSeconds) = explode(' ', $stopTime);

        return $multiplier * (($endSeconds - $startSeconds) + ($endMilliseconds - $startMilliseconds));
    }

    private function validateWasNotStarted()
    {
        if (null !== $this->startTime) {
            throw new StopwatchException('Stopwatch was already started');
        }
    }

    private function validateWasStarted()
    {
        if (null === $this->startTime) {
            throw new StopwatchException('Stopwatch was not started');
        }
    }

    private function validateWasNotStopped()
    {
        if (null !== $this->stopTime) {
            throw new StopwatchException('Stopwatch was already stopped');
        }
    }

    private function validateIsRunning()
    {
        $this->validateWasStarted();
        $this->validateWasNotStopped();
    }

    private function validateIsNotPaused()
    {
        if (count($this->pauseTimes) === 1 + count($this->resumeTimes)) {
            throw new StopwatchException('Stopwatch is already paused');
        }
    }

    private function validateIsPaused()
    {
        if (count($this->pauseTimes) === count($this->resumeTimes)) {
            throw new StopwatchException('Stopwatch is not paused');
        }
    }
}
