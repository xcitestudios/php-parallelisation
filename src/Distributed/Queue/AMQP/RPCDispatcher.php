<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Queue\AMQP;

use com\xcitestudios\Parallelisation\Distributed\Queue\AMQP\Interfaces\RPCDispatcherInterface;
use com\xcitestudios\Parallelisation\Interfaces\EventInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use DateTime;
use stdClass;
use RuntimeException;
use InvalidArgumentException;

/**
 * AMQP dispatcher for events with an RPC style response via AMQP.
 *
 * @package com.xcitestudios.Parallelisation
 * @subpackage Distributed.Queue.AMQP
 */
class RPCDispatcher implements RPCDispatcherInterface
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
     * @var string
     */
    protected $defaultExchange = null;

    /**
     * @var string
     */
    protected $defaultRoutingKey = null;

    /**
     * Running total of events to have returned.
     *
     * @var int
     */
    protected $completedEvents = 0;

    /**
     * Running total of events to have timed out.
     *
     * @var int
     */
    protected $timedOutEvents = 0;

    /**
     * Running total of events to be sent out.
     *
     * @var int
     */
    protected $dispatchedEvents = 0;

    /**
     * @var callable[]
     */
    protected $eventReturnedCallback = [];

    /**
     * @var callable[]
     */
    protected $eventTimedOutCallback = [];

    /**
     * Constructor.
     *
     * @param AbstractConnection $connection
     * @param string             $queueName                    Name of return queue (default is to generate)
     * @param int                $maximumExecutionMilliseconds Maximum execution time for an event.
     * @param callable           $eventReturnedCallback        Callback when events return.
     * @param callable           $eventTimedOutCallback        Callback when events time out.
     */
    public function __construct(AbstractConnection $connection, $queueName = null, $maximumExecutionMilliseconds = 0,
        callable $eventReturnedCallback = null, callable $eventTimedOutCallback = null)
    {
        $this->connection                   = $connection;
        $this->queueName                    = $queueName;
        $this->maximumExecutionMilliseconds = $maximumExecutionMilliseconds;

        if (is_callable($eventReturnedCallback)) {
            $this->eventReturnedCallback[] = $eventReturnedCallback;
        }

        if (is_callable($eventTimedOutCallback)) {
            $this->eventTimedOutCallback[] = $eventTimedOutCallback;
        }

    }

    /**
     * Kick off the dispatcher allowing events to be sent.
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
     * Get the default routing key.
     *
     * @return string
     */
    public function getDefaultRoutingKey()
    {
        return $this->defaultRoutingKey;
    }

    /**
     * Set the default routing key.
     *
     * @param string $defaultRoutingKey
     *
     * @return static
     */
    public function setDefaultRoutingKey($defaultRoutingKey)
    {
        $this->defaultRoutingKey = $defaultRoutingKey;

        return $this;
    }

    /**
     * Get the default exchange to send to.
     *
     * @return string
     */
    public function getDefaultExchange()
    {
        return $this->defaultExchange;
    }

    /**
     * Set the default exchange to send to.
     *
     * @param string $defaultExchange
     *
     * @return static
     */
    public function setDefaultExchange($defaultExchange)
    {
        $this->defaultExchange = $defaultExchange;

        return $this;
    }

    /**
     * Block until all events have been returned or timed out.
     *
     * @return void
     */
    public function waitForAllEvents()
    {
        if (!$this->running) {
            return;
        }

        while (count($this->events) > 0) {
            $this->quickWait();
            $this->checkLongRunning();
            usleep(50000);
        }
    }

    /**
     * Lets AMQP do its thing.
     */
    protected function quickWait()
    {
        try {
            $this->channel->wait(null, true, 0.05);
        } catch (AMQPTimeoutException $ex) {
        }
    }

    /**
     * Dispatch the event via AMQP then return immediately. It is up to the user
     * to check for data coming back.
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
        if (!$this->running) {
            throw new RuntimeException('You cannot send an event without first starting the dispatcher.');
        }

        $exchangeName = $exchangeName !== null ? $exchangeName : $this->defaultExchange;
        $routingKey   = $routingKey !== null ? $routingKey : $this->defaultRoutingKey;

        if ($exchangeName === null) {
            throw new InvalidArgumentException('No exchange specified for dispatch. Either pass one in or use setDefaultExchange.');
        }

        if ($routingKey === null) {
            throw new InvalidArgumentException('No routing key specified for dispatch. Either pass one in or use setDefaultRoutingKey');
        }

        $correlationID = uniqid("", true);

        $wrapped = new RPCEventWrapper();
        $wrapped->setDatetime(new DateTime());
        $wrapped->setEvent($event);

        $this->events[$correlationID] = $wrapped;

        $message = new AMQPMessage($event->serializeJSON(), ['correlation_id' => $correlationID, 'reply_to' => $this->queueName,]);

        $this->channel->basic_publish($message, $exchangeName, $routingKey);
        $this->channel->wait_for_pending_acks();

        $this->dispatchedEvents++;
    }

    /**
     * Handles an incoming message and ties it up with a local event.
     *
     * @internal
     *
     * @param AMQPMessage $message
     *
     * @throws RuntimeException Dispatcher isn't running.
     */
    public function handleMessage(AMQPMessage $message)
    {
        if (!$this->running) {
            throw new RuntimeException('You cannot send an event without first starting the dispatcher.');
        }

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

        $this->doEventReturnedCallbacks($event);

        $this->completedEvents++;

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

                $this->doEventTimedOutCallbacks($event);

                unset($this->events[$correlationID]);

                $this->timedOutEvents++;
            }
        }
    }

    /**
     * Get the running total of events that have been dispatched.
     *
     * @return int
     */
    public function getDispatchedEvents()
    {
        $this->quickWait();

        return $this->dispatchedEvents;
    }

    /**
     * Get the running total of events that have returned..
     *
     * @return int
     */
    public function getCompletedEvents()
    {
        $this->quickWait();

        return $this->completedEvents;
    }

    /**
     * Get the running total of events that have timed out.
     *
     * @return int
     */
    public function getTimedOutEvents()
    {
        $this->quickWait();
        return $this->timedOutEvents;
    }

    /**
     * @return callable[]
     */
    public function getEventReturnedCallbacks()
    {
        return $this->eventReturnedCallback;
    }

    /**
     * @param callable $eventReturnedCallback
     *
     * @return static
     */
    public function addEventReturnedCallback(callable $eventReturnedCallback)
    {
        $this->eventReturnedCallback[] = $eventReturnedCallback;

        return $this;
    }

    /**
     * @return callable[]
     */
    public function getEventTimedOutCallbacks()
    {
        return $this->eventTimedOutCallback;
    }

    /**
     * @param callable $eventTimedOutCallback
     *
     * @return static
     */
    public function addEventTimedOutCallback(callable $eventTimedOutCallback)
    {
        $this->eventTimedOutCallback[] = $eventTimedOutCallback;

        return $this;
    }

    /**
     * Run any event returned callbacks registered with the dispatcher.
     *
     * @param EventInterface $event
     */
    protected function doEventReturnedCallbacks(EventInterface $event)
    {
        foreach($this->eventReturnedCallback as $callback) {
            $callback($event);
        }
    }

    /**
     * Run any even timed out callbacks registered with the dispatcher.
     *
     * @param EventInterface $event
     */
    protected function doEventTimedOutCallbacks(EventInterface $event)
    {
        foreach($this->eventTimedOutCallback as $callback) {
            $callback($event);
        }
    }
}