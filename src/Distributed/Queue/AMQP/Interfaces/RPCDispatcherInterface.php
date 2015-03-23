<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Queue\AMQP\Interfaces;
use com\xcitestudios\Parallelisation\Interfaces\EventHandlerInterface;
use com\xcitestudios\Parallelisation\Interfaces\EventInterface;

/**
 * AMQP dispatcher for events with an RPC style response via AMQP.
 *
 * @package com.xcitestudios.Parallelisation
 * @subpackage Distributed.Queue.AMQP.Interfaces
 */

interface RPCDispatcherInterface extends EventHandlerInterface
{
    /**
     * Dispatch the event via AMQP.
     *
     * @param EventInterface $event        The EventInterface instance to handle.
     * @param string         $exchangeName Name of exchange to publish to
     * @param string         $routingKey   Key used for routing inside AMQP
     *
     * @return void
     */
    public function handle(EventInterface $event, $exchangeName = null, $routingKey = null);

    /**
     * Kick off the dispatcher allowing events to be sent.
     */
    public function start();

    /**
     * Stop this dispatcher.
     */
    public function stop();

    /**
     * Block until all events have been returned or timed out.
     *
     * @return void
     */
    public function waitForAllEvents();

    /**
     * Get the running total of events that have been dispatched.
     *
     * @return int
     */
    public function getDispatchedEvents();

    /**
     * Get the running total of events that have returned..
     *
     * @return int
     */
    public function getCompletedEvents();

    /**
     * Get the running total of events that have timed out.
     *
     * @return int
     */
    public function getTimedOutEvents();

    /**
     * Get the callback to call when an event returns.
     *
     * @return callable[]
     */
    public function getEventReturnedCallbacks();

    /**
     * Set the callback to call when an event returns.
     *
     * @param callable $eventReturnedCallback
     *
     * @return static
     */
    public function addEventReturnedCallback(callable $eventReturnedCallback);

    /**
     * Get the callback to call when an event times out.
     *
     * @return callable[]
     */
    public function getEventTimedOutCallbacks();

    /**
     * Set the callback to call when an event times out.
     *
     * @param callable $eventTimedOutCallback
     *
     * @return static
     */
    public function addEventTimedOutCallback(callable $eventTimedOutCallback);
}