<?php

use PHPUnit\Framework\TestCase;
use TimeBenchmark\StopwatchFactory;

class StopwatchFactoryTest extends TestCase
{
    private StopwatchFactory|null $stopwatchFactory;

    protected function setUp(): void
    {
        $this->stopwatchFactory = new StopwatchFactory();
    }

    public function testCreateStarted()
    {
        $stopWatch = $this->stopwatchFactory->createStarted();

        $this->assertTrue($stopWatch->wasStarted());
        $this->assertTrue($stopWatch->isRunning());
        $this->assertFalse($stopWatch->wasStopped());
    }

    public function testCreate()
    {
        $stopWatch = $this->stopwatchFactory->create();

        $this->assertFalse($stopWatch->wasStarted());
        $this->assertFalse($stopWatch->isRunning());
        $this->assertFalse($stopWatch->wasStopped());
    }
}
