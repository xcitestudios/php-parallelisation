<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Queue\AMQP;

use com\xcitestudios\Parallelisation\Interfaces\EventInterface;
use RuntimeException;

/**
 * AMQP dispatcher for events with an RPC style response via AMQP - blocking.
 *
 * @package com.xcitestudios.Parallelisation
 * @subpackage Distributed.Queue.AMQP
 */
class BlockingRPCDispatcher extends RPCDispatcher
{
    /**
     * Dispatch the event via AMQP then wait for the response before returning
     * execution back to the calling code.
     *
     * @param EventInterface $event        The EventInterface instance to handle.
     * @param string         $exchangeName Name of exchange to publish to
     * @param string         $routingKey   Key used for routing inside AMQP
     *
     * @throws RuntimeException Dispatcher isn't running.
     * @return void
     */
    public function handle(EventInterface $event, $exchangeName = null, $routingKey = null)
    {
        parent::handle($event, $exchangeName, $routingKey);
        $this->waitForAllEvents();
    }
}