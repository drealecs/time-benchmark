drealecs/time-benchmark [![Build Status](https://travis-ci.org/drealecs/time-benchmark.svg?branch=master)](https://travis-ci.org/drealecs/time-benchmark)
=============

Library for timing execution and benchmarking

Stopwatch
------------

An instance of the stopwatch can be created with the static methods:
``` php
$stopwatch = Stopwatch::create();
```
or
``` php
$stopwatch = Stopwatch::createStarted();
```

As the name suggests `createStarted()` method is also starting the stopwatch.
A not started stopwatch can be started using
``` php
$stopwatch->start();
```
When a stopwatch is started it can be stoped with
``` php
$stopwatch->stop();
```
While capturing time, `microtime(false)` is used in order to not loose decimals due to float php precision limit.
The methods `start()` and `stop()` are fast and the calculation of the difference is done when calling the methods:
``` php
$seconds = $stopwatch->getElapsedSeconds();
$milliseconds = $stopwatch->getElapsedMilliseconds();
$microseconds = $stopwatch->getElapsedMicroseconds();
```

There are also three stopwatch status methods that can be used
``` php
$stopwatch->wasStarted();
$stopwatch->isRunning();
$stopwatch->wasStopped();
```

Another functionality also included is related to steps (or laps).
When a stopwatch is running steps can be marked without affecting the stopwatch status with:
``` php
$stopwatch->step($stepName);
```
$stepName is the name of the step and must not be reused for the same stopwatch.

The number of steps can be retrived with
``` php
$stopwatch->getStepsNumber();
```

There are three methods for retriving the time difference between start and each of the steps. The result is an array indexed by the step name.
``` php
$seconds = $stopwatch->getElapsedStepsSeconds();
$milliseconds = $stopwatch->getElapsedStepsMilliseconds();
$microseconds = $stopwatch->getElapsedStepsMicroseconds();
```

There is support for pausing the stopwatch. The methods to do this are:
``` php
$stopwatch->pause();
$stopwatch->resume();
```
Of course pausing and resuming is as fast as possible and the calculation is done within the `getElapsed*` methods.
