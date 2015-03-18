<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Queue\AMQP;

use com\xcitestudios\Parallelisation\Interfaces\EventHandlerInterface;
use com\xcitestudios\Parallelisation\Interfaces\EventInterface;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * RPC worker implementation for AMQP.
 *
 * @package com.xcitestudios.Parallelisation
 * @subpackage Distributed.Queue.AMQP
 */
class RPCWorker
{
    /**
     * @var AbstractConnection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $queueName;

    /**
     * @var EventHandlerInterface
     */
    protected $handler;

    /**
     * @var EventInterface
     */
    protected $eventClass;

    /**
     * @var int
     */
    protected $ackTime;

    /**
     * Constructor.
     *
     * @param AbstractConnection    $connection
     * @param string                $queueName  Name of queue to watch for jobs on.
     * @param EventHandlerInterface $handler    Handler called with each job that comes in.
     * @param EventInterface        $eventClass Class used for incoming events.
     * @param int                   $ackTime    When to send ACK (before or after handling the event).
     */
    public function __construct(AbstractConnection $connection, $queueName, EventHandlerInterface $handler, EventInterface $eventClass, $ackTime = RPCWorkerAckTime::ACK_AFTER)
    {
        $this->connection = $connection;
        $this->queueName  = $queueName;
        $this->handler    = $handler;
        $this->ackTime    = $ackTime;
        $this->eventClass = $eventClass;
    }

    /**
     * Kick off the worker, this will not return.
     */
    public function start()
    {
        $channel = $this->connection->channel();

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($this->queueName, '', false, false, false, false, [$this, 'handleMessage']);

        while (count($channel->callbacks) > 0) {
            $channel->wait();
        }
    }

    public function handleMessage(AMQPMessage $message)
    {
        if ($this->ackTime === RPCWorkerAckTime::ACK_BEFORE) {
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        }

        $event = clone($this->eventClass);
        $event->deserializeJSON($message->body);

        $this->handler->handle($event);

        $response = new AMQPMessage($event->serializeJSON(), ['correlation_id' => $message->get('correlation_id')]);

        $message->delivery_info['channel']->basic_publish($response, '', $message->get('reply_to'));

        if ($this->ackTime === RPCWorkerAckTime::ACK_AFTER) {
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        }
    }
}