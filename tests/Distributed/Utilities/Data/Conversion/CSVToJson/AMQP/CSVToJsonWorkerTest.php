<?php

namespace com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\Test;

use com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\AMQP\CSVToJsonWorker;
use com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\Event;
use com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\EventInput;
use com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\EventOutput;
use com\xcitestudios\Parallelisation\Interfaces\EventInterface;

class CSVToJsonWorkerTest extends \PHPUnit_Framework_TestCase
{
    public function testRestrictedInputType()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $event = $this->getMockBuilder(EventInterface::class)
                      ->getMockForAbstractClass();

        $worker = new CSVToJsonWorker();
        $worker->handle($event);
    }

    public function testHandle()
    {
        $output = null;

        $input = $this->getMockBuilder(EventInput::class)
            ->setMethods(['getHeaders', 'getRows'])
            ->getMock();

        $input->expects($this->atLeastOnce())
            ->method('getHeaders')
            ->will($this->returnValue(['A', 'B']));

        $input->expects($this->atLeastOnce())
            ->method('getRows')
            ->will($this->returnValue([['a', 'b'], ['1', '2']]));

        $event = $this->getMockBuilder(Event::class)
            ->setMethods(['getType', 'getInput', 'setOutput', 'getOutput'])
            ->getMock();

        $event->expects($this->atLeastOnce())
            ->method('getType')
            ->will($this->returnValue((new Event())->getType()));

        $event->expects($this->atLeastOnce())
            ->method('getInput')
            ->will($this->returnValue($input));

        $event->expects($this->any())
              ->method('setOutput')
              ->will($this->returnCallback(function($newOutput) use (&$output){
                  $output = $newOutput;
              }));

        $event->expects($this->any())
              ->method('getOutput')
              ->will($this->returnCallback(function() use (&$output){
                  return $output;
              }));

        $worker = new CSVToJsonWorker();
        $worker->handle($event);

        $this->assertInstanceOf(EventOutput::class, $output);

        $this->assertTrue($output->wasSuccessful());
        $this->assertEquals('{"A":"a","B":"b"}', $output->getJsonObjectStrings()[0]);
        $this->assertEquals('{"A":"1","B":"2"}', $output->getJsonObjectStrings()[1]);
    }
}
