<?php
namespace TimeBenchmark;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

class StopwatchTest extends TestCase
{
    /** @var  Stopwatch */
    private $stopWatch;

    protected function setUp(): void
    {
        $this->stopWatch = Stopwatch::create();
    }

    public function testCreateStartedAndStopStatus()
    {
        $stopWatch = Stopwatch::createStarted();

        $this->assertTrue($stopWatch->wasStarted());
        $this->assertTrue($stopWatch->isRunning());
        $this->assertFalse($stopWatch->wasStopped());

        $stopWatch->stop();

        $this->assertTrue($stopWatch->wasStarted());
        $this->assertFalse($stopWatch->isRunning());
        $this->assertTrue($stopWatch->wasStopped());
    }

    public function testCreateAndStopStatus()
    {
        $stopWatch = Stopwatch::create();

        $this->assertFalse($stopWatch->wasStarted());
        $this->assertFalse($stopWatch->isRunning());
        $this->assertFalse($stopWatch->wasStopped());

        $stopWatch->start();

        $this->assertTrue($stopWatch->wasStarted());
        $this->assertTrue($stopWatch->isRunning());
        $this->assertFalse($stopWatch->wasStopped());

        $stopWatch->stop();

        $this->assertTrue($stopWatch->wasStarted());
        $this->assertFalse($stopWatch->isRunning());
        $this->assertTrue($stopWatch->wasStopped());
    }

    public function testCannotStartWhenStarted()
    {
        $this->stopWatch->start();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was already started');
        $this->stopWatch->start();
    }

    public function testCannotStopWhenNotStarted()
    {
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was not started');
        $this->stopWatch->stop();
    }

    public function testCannotStopWhenStopped()
    {
        $this->stopWatch->start();
        $this->stopWatch->stop();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was already stopped');
        $this->stopWatch->stop();
    }

    public function testStepsStatus()
    {
        $this->stopWatch->start();

        $this->assertTrue($this->stopWatch->wasStarted());
        $this->assertTrue($this->stopWatch->isRunning());
        $this->assertFalse($this->stopWatch->wasStopped());

        $this->stopWatch->step('step1');

        $this->assertTrue($this->stopWatch->wasStarted());
        $this->assertTrue($this->stopWatch->isRunning());
        $this->assertFalse($this->stopWatch->wasStopped());

        $this->stopWatch->step('step2');

        $this->assertTrue($this->stopWatch->wasStarted());
        $this->assertTrue($this->stopWatch->isRunning());
        $this->assertFalse($this->stopWatch->wasStopped());

        $this->stopWatch->stop();

        $this->assertTrue($this->stopWatch->wasStarted());
        $this->assertFalse($this->stopWatch->isRunning());
        $this->assertTrue($this->stopWatch->wasStopped());
    }

    public function testStepsNumber()
    {
        $this->stopWatch->start();
        $this->assertEquals(0, $this->stopWatch->getStepsNumber());
        $this->stopWatch->step('step1');
        $this->assertEquals(1, $this->stopWatch->getStepsNumber());
        $this->stopWatch->step('step2');
        $this->assertEquals(2, $this->stopWatch->getStepsNumber());
        $this->stopWatch->step('step3');
        $this->assertEquals(3, $this->stopWatch->getStepsNumber());
        $this->stopWatch->stop();
        $this->assertEquals(3, $this->stopWatch->getStepsNumber());
    }

    public function testCannotStepWhenNotStarted()
    {
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was not started');
        $this->stopWatch->step('step1');
    }

    public function testCannotStepWhenStopped()
    {
        $this->stopWatch->start();
        $this->stopWatch->stop();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was already stopped');
        $this->stopWatch->step('step1');
    }

    public function testCannotHaveMultipleStepsWithTheSameName()
    {
        $this->stopWatch->start();
        $this->stopWatch->step('step1');
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Step "step1" already used');
        $this->stopWatch->step('step1');
    }

    public function testElapsedStartStop()
    {
        $this->stopWatch->start();
        usleep(10_000);
        $this->stopWatch->stop();
        $elapsed = $this->stopWatch->getElapsedMilliseconds();
        try {
            $this->assertGreaterThanOrEqual(10, $elapsed);
        } catch (AssertionFailedError $e) {
            var_dump($this->stopWatch);
            throw $e;
        }
        $this->assertLessThan(50, $elapsed);

        $elapsedSeconds = $this->stopWatch->getElapsedSeconds();
        $this->assertGreaterThanOrEqual(0.01, $elapsedSeconds);
        $this->assertLessThan(0.05, $elapsedSeconds);
        $elapsedMicroseconds = $this->stopWatch->getElapsedMicroseconds();
        $this->assertGreaterThanOrEqual(10_000, $elapsedMicroseconds);
        $this->assertLessThan(50_000, $elapsedMicroseconds);
    }

    public function testElapsedStartWithoutStop()
    {
        $this->stopWatch->start();
        usleep(10_000);
        $elapsed = $this->stopWatch->getElapsedMilliseconds();
        $this->assertGreaterThanOrEqual(10, $elapsed);
        $this->assertLessThan(50, $elapsed);

        $elapsedSeconds = $this->stopWatch->getElapsedSeconds();
        $this->assertGreaterThanOrEqual(0.01, $elapsedSeconds);
        $this->assertLessThan(0.05, $elapsedSeconds);
        $elapsedMicroseconds = $this->stopWatch->getElapsedMicroseconds();
        $this->assertGreaterThanOrEqual(10_000, $elapsedMicroseconds);
        $this->assertLessThan(50_000, $elapsedMicroseconds);
    }

    public function testElapsedStepsWithNoSteps()
    {
        $this->stopWatch->start();
        $elapsedSteps = $this->stopWatch->getElapsedStepsMilliseconds();
        $this->assertEmpty($elapsedSteps);
    }

    public function testElapsedStepsWorks()
    {
        $this->stopWatch->start();
        usleep(10_000);
        $this->stopWatch->step('step1');
        usleep(30_000);
        $this->stopWatch->step('step2');
        usleep(160_000);
        $this->stopWatch->stop();

        $elapsedSteps = $this->stopWatch->getElapsedStepsMilliseconds();
        $this->assertCount(2, $elapsedSteps);
        $this->assertArrayHasKey('step1', $elapsedSteps);
        $this->assertArrayHasKey('step2', $elapsedSteps);
        $this->assertGreaterThanOrEqual(10, $elapsedSteps['step1']);
        $this->assertLessThan(50, $elapsedSteps['step1']);
        $this->assertGreaterThanOrEqual(40, $elapsedSteps['step2']);
        $this->assertLessThan(80, $elapsedSteps['step2']);

        $elapsedStepsSeconds = $this->stopWatch->getElapsedStepsSeconds();
        $this->assertGreaterThanOrEqual(0.01, $elapsedStepsSeconds['step1']);
        $this->assertLessThan(0.05, $elapsedStepsSeconds['step1']);
        $this->assertGreaterThanOrEqual(0.04, $elapsedStepsSeconds['step2']);
        $this->assertLessThan(0.08, $elapsedStepsSeconds['step2']);
        $elapsedStepsMicroseconds = $this->stopWatch->getElapsedStepsMicroseconds();
        $this->assertGreaterThanOrEqual(10_000, $elapsedStepsMicroseconds['step1']);
        $this->assertLessThan(50_000, $elapsedStepsMicroseconds['step1']);
        $this->assertGreaterThanOrEqual(40_000, $elapsedStepsMicroseconds['step2']);
        $this->assertLessThan(80_000, $elapsedStepsMicroseconds['step2']);
    }

    public function testElapsedIsNotWorkingWithoutStarting()
    {
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was not started');
        $this->stopWatch->getElapsedMilliseconds();
    }

    public function testElapsedStepsIsNotWorkingWithoutStarting()
    {
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was not started');
        $this->stopWatch->getElapsedStepsMilliseconds();
    }

    public function testPauseResumeStatus()
    {
        $stopWatch = Stopwatch::create();

        $this->assertFalse($stopWatch->wasStarted());
        $this->assertFalse($stopWatch->isRunning());
        $this->assertFalse($stopWatch->wasStopped());
        $this->assertFalse($stopWatch->isPaused());

        $stopWatch->start();

        $this->assertTrue($stopWatch->wasStarted());
        $this->assertTrue($stopWatch->isRunning());
        $this->assertFalse($stopWatch->wasStopped());
        $this->assertFalse($stopWatch->isPaused());

        $stopWatch->pause();

        $this->assertTrue($stopWatch->wasStarted());
        $this->assertTrue($stopWatch->isRunning());
        $this->assertFalse($stopWatch->wasStopped());
        $this->assertTrue($stopWatch->isPaused());

        $stopWatch->resume();

        $this->assertTrue($stopWatch->wasStarted());
        $this->assertTrue($stopWatch->isRunning());
        $this->assertFalse($stopWatch->wasStopped());
        $this->assertFalse($stopWatch->isPaused());

        $stopWatch->pause();

        $this->assertTrue($stopWatch->wasStarted());
        $this->assertTrue($stopWatch->isRunning());
        $this->assertFalse($stopWatch->wasStopped());
        $this->assertTrue($stopWatch->isPaused());

        $stopWatch->stop();

        $this->assertTrue($stopWatch->wasStarted());
        $this->assertFalse($stopWatch->isRunning());
        $this->assertTrue($stopWatch->wasStopped());
        $this->assertFalse($stopWatch->isPaused());
    }

    public function testCannotPauseWhenPaused()
    {
        $this->stopWatch->start();
        $this->stopWatch->pause();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch is already paused');
        $this->stopWatch->pause();
    }

    public function testCannotResumeWhenNotPaused()
    {
        $this->stopWatch->start();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch is not paused');
        $this->stopWatch->resume();
    }

    public function testCannotPauseWhenNotStarted()
    {
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was not started');
        $this->stopWatch->pause();
    }

    public function testCannotResumeWhenNotStarted()
    {
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was not started');
        $this->stopWatch->resume();
    }

    public function testCannotPauseWhenStopped()
    {
        $this->stopWatch->start();
        $this->stopWatch->stop();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was already stopped');
        $this->stopWatch->pause();
    }

    public function testCannotResumeWhenStopped()
    {
        $this->stopWatch->start();
        $this->stopWatch->stop();
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Stopwatch was already stopped');
        $this->stopWatch->resume();
    }

    public function testElapsedStartPauseResumeStop()
    {
        $this->stopWatch->start();
        usleep(10_000);
        $this->stopWatch->pause();
        usleep(100_000);
        $this->stopWatch->resume();
        usleep(10_000);
        $this->stopWatch->stop();
        $elapsed = $this->stopWatch->getElapsedMilliseconds();
        $this->assertGreaterThanOrEqual(20, $elapsed);
        $this->assertLessThan(60, $elapsed);
    }

    public function testElapsedStartPauseStop()
    {
        $this->stopWatch->start();
        usleep(10_000);
        $this->stopWatch->pause();
        usleep(100_000);
        $this->stopWatch->stop();
        $elapsed = $this->stopWatch->getElapsedMilliseconds();
        $this->assertGreaterThanOrEqual(10, $elapsed);
        $this->assertLessThan(50, $elapsed);
    }

    public function testElapsedStepsWithPause()
    {
        $this->stopWatch->start();
        usleep(10_000);
        $this->stopWatch->pause();
        usleep(100_000);
        $this->stopWatch->step('step1');
        usleep(100_000);
        $this->stopWatch->resume();
        usleep(20_000);
        $this->stopWatch->pause();
        usleep(100_000);
        $this->stopWatch->resume();
        usleep(10_000);
        $this->stopWatch->step('step2');
        usleep(60_000);
        $this->stopWatch->pause();
        usleep(100_000);
        $this->stopWatch->step('step3');
        usleep(100_000);
        $this->stopWatch->stop();

        $elapsedSteps = $this->stopWatch->getElapsedStepsMilliseconds();
        $this->assertCount(3, $elapsedSteps);
        $this->assertArrayHasKey('step1', $elapsedSteps);
        $this->assertArrayHasKey('step2', $elapsedSteps);
        $this->assertArrayHasKey('step3', $elapsedSteps);
        $this->assertGreaterThanOrEqual(10, $elapsedSteps['step1']);
        $this->assertLessThan(50, $elapsedSteps['step1']);
        $this->assertGreaterThanOrEqual(40, $elapsedSteps['step2']);
        $this->assertLessThan(80, $elapsedSteps['step2']);
        $this->assertGreaterThanOrEqual(100, $elapsedSteps['step3']);
        $this->assertLessThan(140, $elapsedSteps['step3']);

        $elapsedSteps = $this->stopWatch->getElapsedStepsSeconds();
        $this->assertCount(3, $elapsedSteps);
        $this->assertArrayHasKey('step1', $elapsedSteps);
        $this->assertArrayHasKey('step2', $elapsedSteps);
        $this->assertArrayHasKey('step3', $elapsedSteps);
        $this->assertGreaterThanOrEqual(0.01, $elapsedSteps['step1']);
        $this->assertLessThan(0.05, $elapsedSteps['step1']);
        $this->assertGreaterThanOrEqual(0.04, $elapsedSteps['step2']);
        $this->assertLessThan(0.08, $elapsedSteps['step2']);
        $this->assertGreaterThanOrEqual(0.10, $elapsedSteps['step3']);
        $this->assertLessThan(0.14, $elapsedSteps['step3']);
        $elapsedSteps = $this->stopWatch->getElapsedStepsMicroseconds();
        $this->assertCount(3, $elapsedSteps);
        $this->assertArrayHasKey('step1', $elapsedSteps);
        $this->assertArrayHasKey('step2', $elapsedSteps);
        $this->assertArrayHasKey('step3', $elapsedSteps);
        $this->assertGreaterThanOrEqual(10000, $elapsedSteps['step1']);
        $this->assertLessThan(50000, $elapsedSteps['step1']);
        $this->assertGreaterThanOrEqual(40000, $elapsedSteps['step2']);
        $this->assertLessThan(80000, $elapsedSteps['step2']);
        $this->assertGreaterThanOrEqual(100000, $elapsedSteps['step3']);
        $this->assertLessThan(140000, $elapsedSteps['step3']);
    }

    final public static function assertGreaterThanWithBuffer(float $expected, float $actual, float $errorMargin): void
    {
        self::assertGreaterThanOrEqual($expected, $actual);
        self::assertLessThan($expected + $errorMargin, $actual);
    }
}
