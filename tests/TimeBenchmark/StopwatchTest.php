<?php
namespace TimeBenchmark;

class StopwatchTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Stopwatch */
    private $stopWatch;

    protected function setUp()
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

    public function testCreateUnstartedAndStopStatus()
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

    /**
     * @expectedException \TimeBenchmark\StopwatchException
     * @expectedExceptionMessage Stopwatch was already started
     */
    public function testCannotStartWhenStarted()
    {
        $this->stopWatch->start();
        $this->stopWatch->start();
    }

    /**
     * @expectedException \TimeBenchmark\StopwatchException
     * @expectedExceptionMessage Stopwatch is not started
     */
    public function testCannotStopWhenNotStarted()
    {
        $this->stopWatch->stop();
    }

    /**
     * @expectedException \TimeBenchmark\StopwatchException
     * @expectedExceptionMessage Stopwatch was already stopped
     */
    public function testCannotStopWhenStopped()
    {
        $this->stopWatch->start();
        $this->stopWatch->stop();
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

    /**
     * @expectedException \TimeBenchmark\StopwatchException
     * @expectedExceptionMessage Stopwatch is not started
     */
    public function testCannotStepWhenNotStarted()
    {
        $this->stopWatch->step('step1');
    }

    /**
     * @expectedException \TimeBenchmark\StopwatchException
     * @expectedExceptionMessage Stopwatch is stopped
     */
    public function testCannotStepWhenStopped()
    {
        $this->stopWatch->start();
        $this->stopWatch->stop();
        $this->stopWatch->step('step1');
    }

    /**
     * @expectedException \TimeBenchmark\StopwatchException
     * @expectedExceptionMessage Step "step1" already used
     */
    public function testCannotHaveMultipleStepsWithTheSameName()
    {
        $this->stopWatch->start();
        $this->stopWatch->step('step1');
        $this->stopWatch->step('step1');
    }

    public function testElapsedStartStop()
    {
        $this->stopWatch->start();
        usleep(1500);
        $this->stopWatch->stop();
        $elapsed = $this->stopWatch->getElapsedMilliseconds();
        $this->assertGreaterThanOrEqual(1, $elapsed);
        $this->assertLessThan(4, $elapsed);
    }

    public function testElapsedStartWithoutStop()
    {
        $this->stopWatch->start();
        usleep(1500);
        $elapsed = $this->stopWatch->getElapsedMilliseconds();
        $this->assertGreaterThanOrEqual(1, $elapsed);
        $this->assertLessThan(4, $elapsed);
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
        usleep(1500);
        $this->stopWatch->step('step1');
        usleep(4000);
        $this->stopWatch->step('step2');

        $elapsedSteps = $this->stopWatch->getElapsedStepsMilliseconds();
        $this->assertCount(2, $elapsedSteps);
        $this->assertArrayHasKey('step1', $elapsedSteps);
        $this->assertArrayHasKey('step2', $elapsedSteps);
        $this->assertGreaterThanOrEqual(1, $elapsedSteps['step1']);
        $this->assertLessThan(4, $elapsedSteps['step1']);
        $this->assertGreaterThanOrEqual(5, $elapsedSteps['step2']);
        $this->assertLessThan(8, $elapsedSteps['step2']);
    }


    /**
     * @expectedException \TimeBenchmark\StopwatchException
     * @expectedExceptionMessage Stopwatch is not started
     */
    public function testElapsedIsNotWorkingWithoutStarting()
    {
        $this->stopWatch->getElapsedMilliseconds();
    }

    /**
     * @expectedException \TimeBenchmark\StopwatchException
     * @expectedExceptionMessage Stopwatch is not started
     */
    public function testElapsedStepsIsNotWorkingWithoutStarting()
    {
        $this->stopWatch->getElapsedStepsMilliseconds();
    }
}
