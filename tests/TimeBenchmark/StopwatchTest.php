<?php

declare(strict_types=1);

namespace TimeBenchmark;

use PHPUnit\Framework\TestCase;

class StopwatchTest extends TestCase
{
    public function testCreateStartedAndStopStatus()
    {
        $stopwatch = Stopwatch::createStarted();

        self::assertTrue($stopwatch->wasStarted());
        self::assertTrue($stopwatch->isRunning());
        self::assertFalse($stopwatch->wasStopped());

        $stopwatch->stop();

        self::assertTrue($stopwatch->wasStarted());
        self::assertFalse($stopwatch->isRunning());
        self::assertTrue($stopwatch->wasStopped());
    }

    public function testCreateAndStopStatus()
    {
        $stopwatch = Stopwatch::create();

        self::assertFalse($stopwatch->wasStarted());
        self::assertFalse($stopwatch->isRunning());
        self::assertFalse($stopwatch->wasStopped());

        $stopwatch->start();

        self::assertTrue($stopwatch->wasStarted());
        self::assertTrue($stopwatch->isRunning());
        self::assertFalse($stopwatch->wasStopped());

        $stopwatch->stop();

        self::assertTrue($stopwatch->wasStarted());
        self::assertFalse($stopwatch->isRunning());
        self::assertTrue($stopwatch->wasStopped());
    }

    public function testCannotStartWhenStarted()
    {
        $stopwatch = Stopwatch::createStarted();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was already started');
        $stopwatch->start();
    }

    public function testCannotStopWhenNotStarted()
    {
        $stopwatch = Stopwatch::create();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was not started');
        $stopwatch->stop();
    }

    public function testCannotStopWhenStopped()
    {
        $stopwatch = Stopwatch::createStarted();
        $stopwatch->stop();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was already stopped');
        $stopwatch->stop();
    }

    public function testStepsStatus()
    {
        $stopwatch = Stopwatch::createStarted();

        self::assertTrue($stopwatch->wasStarted());
        self::assertTrue($stopwatch->isRunning());
        self::assertFalse($stopwatch->wasStopped());

        $stopwatch->step('step1');

        self::assertTrue($stopwatch->wasStarted());
        self::assertTrue($stopwatch->isRunning());
        self::assertFalse($stopwatch->wasStopped());

        $stopwatch->step('step2');

        self::assertTrue($stopwatch->wasStarted());
        self::assertTrue($stopwatch->isRunning());
        self::assertFalse($stopwatch->wasStopped());

        $stopwatch->stop();

        self::assertTrue($stopwatch->wasStarted());
        self::assertFalse($stopwatch->isRunning());
        self::assertTrue($stopwatch->wasStopped());
    }

    public function testStepsNumber()
    {
        $stopwatch = Stopwatch::createStarted();
        self::assertEquals(0, $stopwatch->getStepsNumber());
        $stopwatch->step('step1');
        self::assertEquals(1, $stopwatch->getStepsNumber());
        $stopwatch->step('step2');
        self::assertEquals(2, $stopwatch->getStepsNumber());
        $stopwatch->step('step3');
        self::assertEquals(3, $stopwatch->getStepsNumber());
        $stopwatch->stop();
        self::assertEquals(3, $stopwatch->getStepsNumber());
    }

    public function testCannotStepWhenNotStarted()
    {
        $stopwatch = Stopwatch::create();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was not started');
        $stopwatch->step('step1');
    }

    public function testCannotStepWhenStopped()
    {
        $stopwatch = Stopwatch::createStarted();
        $stopwatch->stop();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was already stopped');
        $stopwatch->step('step1');
    }

    public function testCannotHaveMultipleStepsWithTheSameName()
    {
        $stopwatch = Stopwatch::createStarted();
        $stopwatch->step('step1');
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Step "step1" already used');
        $stopwatch->step('step1');
    }

    public function testElapsedStartStop()
    {
        $stopwatch = Stopwatch::createStarted();
        usleep(10_000);
        $stopwatch->stop();
        $elapsed = $stopwatch->getElapsedMilliseconds();
        self::assertGreaterThanWithBuffer(10, $elapsed, 40);

        $elapsedSeconds = $stopwatch->getElapsedSeconds();
        self::assertGreaterThanWithBuffer(0.01, $elapsedSeconds, 0.04);
        $elapsedMicroseconds = $stopwatch->getElapsedMicroseconds();
        self::assertGreaterThanWithBuffer(10_000, $elapsedMicroseconds, 40_000);
    }

    public function testElapsedStartWithoutStop()
    {
        $stopwatch = Stopwatch::createStarted();
        usleep(10_000);
        $elapsed = $stopwatch->getElapsedMilliseconds();
        self::assertGreaterThanWithBuffer(10, $elapsed, 40);

        $elapsedSeconds = $stopwatch->getElapsedSeconds();
        self::assertGreaterThanWithBuffer(0.01, $elapsedSeconds, 0.04);
        $elapsedMicroseconds = $stopwatch->getElapsedMicroseconds();
        self::assertGreaterThanWithBuffer(10_000, $elapsedMicroseconds, 40_000);
    }

    public function testElapsedStepsWithNoSteps()
    {
        $stopwatch = Stopwatch::createStarted();
        $elapsedSteps = $stopwatch->getElapsedStepsMilliseconds();
        self::assertEmpty($elapsedSteps);
    }

    public function testElapsedStepsWorks()
    {
        $stopwatch = Stopwatch::createStarted();
        usleep(10_000);
        $stopwatch->step('step1');
        usleep(30_000);
        $stopwatch->step('step2');
        usleep(160_000);
        $stopwatch->stop();

        $elapsedSteps = $stopwatch->getElapsedStepsMilliseconds();
        self::assertCount(2, $elapsedSteps);
        self::assertArrayHasKey('step1', $elapsedSteps);
        self::assertArrayHasKey('step2', $elapsedSteps);
        self::assertGreaterThanWithBuffer(10, $elapsedSteps['step1'], 40);
        self::assertGreaterThanWithBuffer(40, $elapsedSteps['step2'], 40);

        $elapsedStepsSeconds = $stopwatch->getElapsedStepsSeconds();
        self::assertGreaterThanWithBuffer(0.01, $elapsedStepsSeconds['step1'], 0.04);
        self::assertGreaterThanWithBuffer(0.04, $elapsedStepsSeconds['step2'], 0.04);
        $elapsedStepsMicroseconds = $stopwatch->getElapsedStepsMicroseconds();
        self::assertGreaterThanWithBuffer(10_000, $elapsedStepsMicroseconds['step1'], 40_000);
        self::assertGreaterThanWithBuffer(40_000, $elapsedStepsMicroseconds['step2'], 40_000);
    }

    public function testElapsedIsNotWorkingWithoutStarting()
    {
        $stopwatch = Stopwatch::create();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was not started');
        $stopwatch->getElapsedMilliseconds();
    }

    public function testElapsedStepsIsNotWorkingWithoutStarting()
    {
        $stopwatch = Stopwatch::create();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was not started');
        $stopwatch->getElapsedStepsMilliseconds();
    }

    public function testPauseResumeStatus()
    {
        $stopwatch = Stopwatch::create();

        self::assertFalse($stopwatch->wasStarted());
        self::assertFalse($stopwatch->isRunning());
        self::assertFalse($stopwatch->wasStopped());
        self::assertFalse($stopwatch->isPaused());

        $stopwatch->start();

        self::assertTrue($stopwatch->wasStarted());
        self::assertTrue($stopwatch->isRunning());
        self::assertFalse($stopwatch->wasStopped());
        self::assertFalse($stopwatch->isPaused());

        $stopwatch->pause();

        self::assertTrue($stopwatch->wasStarted());
        self::assertTrue($stopwatch->isRunning());
        self::assertFalse($stopwatch->wasStopped());
        self::assertTrue($stopwatch->isPaused());

        $stopwatch->resume();

        self::assertTrue($stopwatch->wasStarted());
        self::assertTrue($stopwatch->isRunning());
        self::assertFalse($stopwatch->wasStopped());
        self::assertFalse($stopwatch->isPaused());

        $stopwatch->pause();

        self::assertTrue($stopwatch->wasStarted());
        self::assertTrue($stopwatch->isRunning());
        self::assertFalse($stopwatch->wasStopped());
        self::assertTrue($stopwatch->isPaused());

        $stopwatch->stop();

        self::assertTrue($stopwatch->wasStarted());
        self::assertFalse($stopwatch->isRunning());
        self::assertTrue($stopwatch->wasStopped());
        self::assertFalse($stopwatch->isPaused());
    }

    public function testCannotPauseWhenPaused()
    {
        $stopwatch = Stopwatch::createStarted();
        $stopwatch->pause();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch is already paused');
        $stopwatch->pause();
    }

    public function testCannotResumeWhenNotPaused()
    {
        $stopwatch = Stopwatch::createStarted();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch is not paused');
        $stopwatch->resume();
    }

    public function testCannotPauseWhenNotStarted()
    {
        $stopwatch = Stopwatch::create();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was not started');
        $stopwatch->pause();
    }

    public function testCannotResumeWhenNotStarted()
    {
        $stopwatch = Stopwatch::create();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was not started');
        $stopwatch->resume();
    }

    public function testCannotPauseWhenStopped()
    {
        $stopwatch = Stopwatch::createStarted();
        $stopwatch->stop();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was already stopped');
        $stopwatch->pause();
    }

    public function testCannotResumeWhenStopped()
    {
        $stopwatch = Stopwatch::createStarted();
        $stopwatch->stop();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was already stopped');
        $stopwatch->resume();
    }

    public function testElapsedStartPauseResumeStop()
    {
        $stopwatch = Stopwatch::createStarted();
        usleep(10_000);
        $stopwatch->pause();
        usleep(100_000);
        $stopwatch->resume();
        usleep(10_000);
        $stopwatch->stop();
        $elapsed = $stopwatch->getElapsedMilliseconds();
        self::assertGreaterThanWithBuffer(20, $elapsed, 40);
    }

    public function testElapsedStartPauseStop()
    {
        $stopwatch = Stopwatch::createStarted();
        usleep(10_000);
        $stopwatch->pause();
        usleep(100_000);
        $stopwatch->stop();
        $elapsed = $stopwatch->getElapsedMilliseconds();
        self::assertGreaterThanWithBuffer(10, $elapsed, 40);
    }

    public function testElapsedStepsWithPause()
    {
        $stopwatch = Stopwatch::createStarted();
        usleep(10_000);
        $stopwatch->pause();
        usleep(100_000);
        $stopwatch->step('step1');
        usleep(100_000);
        $stopwatch->resume();
        usleep(20_000);
        $stopwatch->pause();
        usleep(100_000);
        $stopwatch->resume();
        usleep(10_000);
        $stopwatch->step('step2');
        usleep(60_000);
        $stopwatch->pause();
        usleep(100_000);
        $stopwatch->step('step3');
        usleep(100_000);
        $stopwatch->stop();

        $elapsedSteps = $stopwatch->getElapsedStepsMilliseconds();
        self::assertCount(3, $elapsedSteps);
        self::assertArrayHasKey('step1', $elapsedSteps);
        self::assertArrayHasKey('step2', $elapsedSteps);
        self::assertArrayHasKey('step3', $elapsedSteps);
        self::assertGreaterThanWithBuffer(10, $elapsedSteps['step1'], 40);
        self::assertGreaterThanWithBuffer(40, $elapsedSteps['step2'], 40);
        self::assertGreaterThanWithBuffer(100, $elapsedSteps['step3'], 40);

        $elapsedSteps = $stopwatch->getElapsedStepsSeconds();
        self::assertCount(3, $elapsedSteps);
        self::assertArrayHasKey('step1', $elapsedSteps);
        self::assertArrayHasKey('step2', $elapsedSteps);
        self::assertArrayHasKey('step3', $elapsedSteps);
        self::assertGreaterThanWithBuffer(0.01, $elapsedSteps['step1'], 0.04);
        self::assertGreaterThanWithBuffer(0.04, $elapsedSteps['step2'], 0.04);
        self::assertGreaterThanWithBuffer(0.10, $elapsedSteps['step3'], 0.04);
        $elapsedSteps = $stopwatch->getElapsedStepsMicroseconds();
        self::assertCount(3, $elapsedSteps);
        self::assertArrayHasKey('step1', $elapsedSteps);
        self::assertArrayHasKey('step2', $elapsedSteps);
        self::assertArrayHasKey('step3', $elapsedSteps);
        self::assertGreaterThanWithBuffer(10_000, $elapsedSteps['step1'], 40_000);
        self::assertGreaterThanWithBuffer(40_000, $elapsedSteps['step2'], 40_000);
        self::assertGreaterThanWithBuffer(100_000, $elapsedSteps['step3'], 40_000);
    }

    private static function assertGreaterThanWithBuffer(float $expected, float $actual, float $errorMargin): void
    {
        self::assertGreaterThanOrEqual($expected, $actual);
        self::assertLessThan($expected + $errorMargin, $actual);
    }
}
