<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Interfaces;

use com\xcitestudios\Parallelisation\Interfaces\EventInterface;
use DateTime;

/**
 * Useful for storing an event alongside when the event got "wrapped". Convenience
 * interface for things such as storing a local copy of an event and when it got, for example
 * pushed in to a queue to be handled remotely.
 *
 * @package com.xcitestudios.Parallelisation
 * @subpackage Distributed.Interfaces
 */
interface EventTransmissionWrapperInterface
{
    /**
     * Set the event this information is related to.
     * @param EventInterface $event
     * @return void
     */
    public function setEvent(EventInterface $event);

    /**
     * Get the event this information is related to.
     *
     * @return EventInterface
     */
    public function getEvent();

    /**
     * Set the Date/Time the event was wrapped/transmitted/pushed.
     *
     * @param DateTime $datetime
     * @return void
     */
    public function setDatetime(DateTime $datetime);

    /**
     * Get the Date/Time the event was wrapped/transmitted/pushed.
     *
     * @return DateTime
     */
    public function getDatetime();

    /**
     * Get the total number of milliseconds between now and {@see getDatetime()}
     *
     * @return int
     */
    public function getTotalMilliseconds();
}