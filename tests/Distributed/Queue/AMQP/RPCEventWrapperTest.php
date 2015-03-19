<?php
/**
 * Created by PhpStorm.
 * User: Wade
 * Date: 19/03/2015
 * Time: 10:42
 */

namespace ios\Parallelisation\Distributed\Queue\AMQP\Test;

use com\xcitestudios\Parallelisation\Distributed\Queue\AMQP\RPCEventWrapper;
use com\xcitestudios\Parallelisation\Interfaces\EventInterface;
use DateTime;

class RPCEventWrapperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSetConsistency()
    {
        $dt = new DateTime();
        $e = $this->getMockForAbstractClass(EventInterface::class);

        $wrapper = new RPCEventWrapper();
        $wrapper->setDatetime($dt);
        $wrapper->setEvent($e);

        $this->assertEquals($dt->format('U'), $wrapper->getDatetime()->format('U'));
        $this->assertEquals($e, $wrapper->getEvent());
    }

    public function testMillisecondsCorrect()
    {
        $dt = new DateTime();

        $wrapper = new RPCEventWrapper();
        $wrapper->setDatetime($dt);

        $this->assertGreaterThan(0, $wrapper->getTotalMilliseconds());
        $this->assertLessThan(1500, $wrapper->getTotalMilliseconds());
    }
}
