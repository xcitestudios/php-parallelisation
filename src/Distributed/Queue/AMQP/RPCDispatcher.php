<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Queue\AMQP;

use com\xcitestudios\Parallelisation\Distributed\Queue\AMQP\Interfaces\RoutableEventInterface;
use com\xcitestudios\Parallelisation\Interfaces\EventInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;
use DateTime;
use stdClass;

/**
 * AMQP dispatcher for events with an RPC style response via AMQP.
 *
 * @package com.xcitestudios.Parallelisation
 * @subpackage Distributed.Queue.AMQP
 */
class RPCDispatcher
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
     * @var bool
     */
    protected $running = false;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var RPCEventWrapper[]
     */
    protected $events = [];

    /**
     * @var int
     */
    protected $maximumExecutionMilliseconds = 0;

    /**
     * Constructor.
     *
     * @param AbstractConnection $connection
     * @param string             $queueName                    Name of return queue (default is to generate)
     * @param int                $maximumExecutionMilliseconds Maximum execution time for an event.
     */
    public function __construct(AbstractConnection $connection, $queueName = null, $maximumExecutionMilliseconds = 0)
    {
        $this->connection                   = $connection;
        $this->queueName                    = $queueName;
        $this->maximumExecutionMilliseconds = $maximumExecutionMilliseconds;
    }

    /**
     * Kick off the worker, this will not return.
     */
    public function start()
    {
        if ($this->running) {
            return;
        }

        $channel = $this->connection->channel();

        if ($this->queueName === null) {
            list($this->queueName, ,) = $channel->queue_declare('');
        }

        $channel->basic_consume($this->queueName, '', false, true, false, false, [$this, 'handleMessage']);

        $this->running = true;
        $this->channel = $channel;
    }

    /**
     * Stop this dispatcher.
     */
    public function stop()
    {
        $this->running = false;

        if ($this->channel instanceof AMQPChannel) {
            try {
                $this->channel->close();
            } catch (\Exception $ex) {
                // No one cares.
            }
        }
    }

    /**
     * @return void
     */
    public function waitForAllEvents()
    {
        if (!$this->running) {
            return;
        }

        if (count($this->events) === 0) {
            return;
        }

        while (count($this->events) > 0) {
            $this->channel->wait();
        }
    }

    /**
     * Take the event and either check the type to handle it appropriately or strongly
     * type the event and read the input to create output.
     *
     * It is recommended output on the event should be presumed null and set here; however
     * if the event is to be handled by multiple objects then it could have output set in those cases.
     *
     * @param EventInterface $event The IEvent instance to handle.
     *
     * @return void
     */
    public function handle(RoutableEventInterface $event)
    {
        $correlationID = uniqid("", true);

        $wrapped = new RPCEventWrapper();
        $wrapped->setDatetime(new DateTime());
        $wrapped->setEvent($event);

        $this->events[$correlationID] = $wrapped;

        $message = new AMQPMessage($event->serializeJSON(), ['correlation_id' => $correlationID, 'reply_to' => $this->queueName,]);

        $this->channel->basic_publish($message, $event->getExchangeName(), $event->getRoutingKey());

        $this->channel->wait_for_pending_acks();
    }

    /**
     * Handles an incoming message and ties it up with a local event.
     *
     * @param AMQPMessage $message
     */
    public function handleMessage(AMQPMessage $message)
    {
        $correlationID = $message->get('correlation_id');

        if (!array_key_exists($correlationID, $this->events)) {
            return;
        }

        $wrapper = $this->events[$correlationID]; /** @var RPCEventWrapper $wrapper */
        $event = $wrapper->getEvent();

        unset($this->events[$correlationID]);

        $tempEvent = clone $event;
        $tempEvent->deserializeJSON($message->body);
        $event->setOutput($tempEvent->getOutput());

        $this->checkLongRunning();
    }

    /**
     * Check for long running events and mark as failed.
     */
    protected function checkLongRunning()
    {
        if ($this->maximumExecutionMilliseconds <= 0) {
            return;
        }

        foreach ($this->events as $correlationID => $wrapped) { /** @var RPCEventWrapper $wrapped */
            if ($wrapped->getTotalMilliseconds() > $this->maximumExecutionMilliseconds) {
                $event     = $wrapped->getEvent();
                $tempEvent = clone $event;

                $error                          = new stdClass();
                $error->output                  = new stdClass();
                $error->output->success         = false;
                $error->output->responseMessage = 'Timed out';

                $tempEvent->deserializeJSON(json_encode($error));
                $event->setOutput($tempEvent->getOutput());

                unset($this->events[$correlationID]);
            }
        }
    }
}