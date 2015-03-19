<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Queue\AMQP;

use com\xcitestudios\Parallelisation\Distributed\Interfaces\EventTransmissionWrapperInterface;
use com\xcitestudios\Parallelisation\Interfaces\EventInterface;
use DateTime;

/**
 * Used for storing an RPC event along with local information.
 *
 * @package com.xcitestudios.Parallelisation
 * @subpackage Distributed.Queue.AMQP
 */
class RPCEventWrapper implements EventTransmissionWrapperInterface
{
    /**
     * @var EventInterface
     */
    protected $event;

    /**
     * @var DateTime
     */
    protected $datetime;

    /**
     * @var bool
     */
    protected $returned = false;

    /**
     * Set the event this information is related to.
     *
     * @param EventInterface $event
     *
     * @return static
     */
    public function setEvent(EventInterface $event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get the event this information is related to.
     *
     * @return EventInterface
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set the Date/Time the event was wrapped/transmitted/pushed.
     *
     * @param DateTime $datetime
     *
     * @return static
     */
    public function setDatetime(DateTime $datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * Get the Date/Time the event was wrapped/transmitted/pushed.
     *
     * @return DateTime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Get the total number of milliseconds between now and {@see getDatetime()}
     *
     * @return int
     */
    public function getTotalMilliseconds()
    {
        return round((microtime(true) * 1000) - ((int)$this->datetime->format('U') * 1000));
    }
}