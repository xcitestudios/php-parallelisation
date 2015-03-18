<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Queue\AMQP\Interfaces;

use com\xcitestudios\Parallelisation\Interfaces\EventInterface;

/**
 * Extends EventInterface to imply a routable event via AMQP exchanges
 * and routing keys.
 *
 * @package com.xcitestudios.Parallelisation
 * @subpackage Distributed.Queue.AMQP.Interfaces
 */
interface RoutableEventInterface extends EventInterface
{
    /**
     * Set the name of the exchange the event should be sent to.
     * Characters are restricted to: a-zA-Z0-9-_.:
     *
     * @param string $name
     * @return void
     */
    public function setExchangeName($name);

    /**
     * Get the name of the exchange the event should be sent to.
     *
     * @return string
     */
    public function getExchangeName();

    /**
     * Set the routing key used for routing the event inside AMQP.
     *
     * @param string $key
     * @return void
     */
    public function setRoutingKey($key);

    /**
     * Get the routing key used for routing the event inside AMQP.
     *
     * @return string
     */
    public function getRoutingKey();
}