<?php

namespace com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\Test;

use com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\Event;
use com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJsonFile;
use com\xcitestudios\Parallelisation\Interfaces\EventHandlerInterface;

class CSVToJsonFileTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleConversionWithHeaders()
    {
        $file = __DIR__ . '/simple_headers.csv';

        $handlerMock = $this->getMockBuilder(EventHandlerInterface::class)
            ->setMethods(['handle'])
            ->getMockForAbstractClass();

        $handlerMock->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function($event){ /** @var $event Event */
                $this->assertInstanceOf(Event::class, $event);

                $headers = $event->getInput()->getHeaders();
                $rows    = $event->getInput()->getRows();

                foreach($rows as $row) {
                    $ret = new \stdClass();

                    for ($i = 0; $i < count($headers); $i++) {
                        $ret->{$headers[$i]} = array_key_exists($i, $row) ? $row[$i] : null;
                    }

                    $event->getOutput()->addJsonObjectString(json_encode($ret));
                }
            }));

        $converter = new CSVToJsonFile($handlerMock);
        $converter->setRowLimitPerWorker(9999);
        $converter->setFilename($file);
        $result = $converter->process();

        $this->assertEquals('[{"Alpha":"1","Bravo":"banana","Charlie":"zoo keeper","Delta Echo":""},{"Alpha":"2","Bravo":"waffle","Charlie":"mango orange","Delta Echo":"5.2"}]', $result);
    }

    public function testConversionWithCustomHeaders()
    {
        $file = __DIR__ . '/simple_headers.csv';

        $handlerMock = $this->getMockBuilder(EventHandlerInterface::class)
                            ->setMethods(['handle'])
                            ->getMockForAbstractClass();

        $handlerMock->expects($this->once())
                    ->method('handle')
                    ->will($this->returnCallback(function($event){ /** @var $event Event */
                        $this->assertInstanceOf(Event::class, $event);

                        $headers = $event->getInput()->getHeaders();
                        $rows    = $event->getInput()->getRows();

                        foreach($rows as $row) {
                            $ret = new \stdClass();

                            for ($i = 0; $i < count($headers); $i++) {
                                $ret->{$headers[$i]} = array_key_exists($i, $row) ? $row[$i] : "";
                            }

                            $event->getOutput()->addJsonObjectString(json_encode($ret));
                        }
                    }));

        $converter = new CSVToJsonFile($handlerMock);
        $converter->setCustomHeaders(['A', 'B', 'C', 'D', 'E', 'F', 'G']);
        $converter->enableCustomHeaders();
        $converter->setRowLimitPerWorker(9999);
        $converter->setFilename($file);
        $result = $converter->process();

        $this->assertEquals('[{"A":"1","B":"banana","C":"zoo keeper","D":"","E":"","F":"","G":""},{"A":"2","B":"waffle","C":"mango orange","D":"5.2","E":"","F":"","G":""}]', $result);
    }

    public function testNoHeaderFailure()
    {
        $file = __DIR__ . '/simple_headers.csv';

        $handlerMock = $this->getMockBuilder(EventHandlerInterface::class)
                            ->setMethods(['handle'])
                            ->getMockForAbstractClass();

        $converter = new CSVToJsonFile($handlerMock);
        $converter->setFirstRowIsHeaders(false);
        $converter->setRowLimitPerWorker(9999);
        $converter->setFilename($file);

        $this->setExpectedException(\RuntimeException::class);
        $converter->process();
    }
}
