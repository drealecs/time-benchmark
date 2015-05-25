<?php
namespace TimeBenchmark;

final class Stopwatch
{
    private $startTime = null;
    private $stopTime = null;
    private $stepTimes = [];

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
        if (null !== $this->startTime) {
            throw new StopwatchException('Stopwatch was already started');
        }
        $this->startTime = microtime(false);
    }

    public function stop()
    {
        if (null === $this->startTime) {
            throw new StopwatchException('Stopwatch is not started');
        }
        if (null !== $this->stopTime) {
            throw new StopwatchException('Stopwatch was already stopped');
        }
        $this->stopTime = microtime(false);
    }

    public function step($name)
    {
        if (null === $this->startTime) {
            throw new StopwatchException('Stopwatch is not started');
        }
        if (null !== $this->stopTime) {
            throw new StopwatchException('Stopwatch is stopped');
        }
        if (isset($this->stepTimes[$name])) {
            throw new StopwatchException('Step "' . $name . '" already used');
        }
        $this->stepTimes[$name] = microtime(false);
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
        if (null === $this->startTime) {
            throw new StopwatchException('Stopwatch is not started');
        }

        if (null === $this->stopTime) {
            $stopTime = microtime(false);
        } else {
            $stopTime = $this->stopTime;
        }

        return $this->calculateDifference($this->startTime, $stopTime, $multiplier);
    }

    private function computeElapsedSteps($multiplier = 1)
    {
        if (null === $this->startTime) {
            throw new StopwatchException('Stopwatch is not started');
        }

        $differenceSteps = [];
        foreach ($this->stepTimes as $stepName => $stepTime) {
            $differenceSteps[$stepName] = $this->calculateDifference($this->startTime, $stepTime, $multiplier);
        }

        return $differenceSteps;
    }

    private function calculateDifference($startTime, $stopTime, $multiplier)
    {
        list ($startMilliseconds, $startSeconds) = explode(' ', $startTime);
        list ($endMilliseconds, $endSeconds) = explode(' ', $stopTime);

        return $multiplier * (($endSeconds - $startSeconds) + ($endMilliseconds - $startMilliseconds));
    }

}
