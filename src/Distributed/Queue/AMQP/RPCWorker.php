<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Queue\AMQP;

use com\xcitestudios\Parallelisation\Distributed\Queue\AMQP\Interfaces\RPCWorkerInterface;
use com\xcitestudios\Parallelisation\Interfaces\EventHandlerInterface;
use com\xcitestudios\Parallelisation\Interfaces\EventInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use RuntimeException;
use InvalidArgumentException;

/**
 * RPC worker implementation for AMQP.
 *
 * @package    com.xcitestudios.Parallelisation
 * @subpackage Distributed.Queue.AMQP
 */
class RPCWorker implements RPCWorkerInterface
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
     * Function called frequently to allow the worker to be stopped (or other work performed).
     *
     * @var callable
     */
    protected $tickFunction;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var bool
     */
    protected $running = false;

    /**
     * Constructor.
     *
     * @param AbstractConnection $connection AMQP connection.
     * @param string             $queueName  Name of queue to watch for jobs on.
     * @param string|callable    $handler    Handler called with each job that comes in, can use a callback to be
     *                                       dynamic. Callback signature is Function(body, routingKey). Must return
     *                                       instance of {@see EventHandlerInterface}.
     * @param string|callable    $eventClass Class used for incoming events, can use callback to be dynamic. Callback
     *                                       signature is Function(body, routingKey). Must return class implementing
     *                                       {@see EventInterface}.
     * @param int                $ackTime    When to send ACK (before or after handling the event).
     */
    public function __construct(AbstractConnection $connection, $queueName, $handler, $eventClass, $ackTime = RPCWorkerAckTime::ACK_AFTER)
    {
        $this->connection = $connection;
        $this->queueName  = $queueName;
        $this->handler    = $handler;
        $this->ackTime    = $ackTime;
        $this->eventClass = $eventClass;
    }

    /**
     * Register a function that will be called at most every 50ms to allow other code to run while the
     * worker is running. For example you could use this function to call stop on the worker. One argument
     * will be passed to it, an instance of this.
     *
     * @param callable $function
     *
     * @return static
     */
    public function setLoopCallbackFunction(callable $function)
    {
        $this->tickFunction = $function;

        return $this;
    }

    /**
     * Kick off the worker, this will not return.
     */
    public function start()
    {
        if ($this->running) {
            return;
        }

        $this->running = true;

        $channel = $this->connection->channel();

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($this->queueName, '', false, false, false, false, [$this, 'handleMessage']);

        $this->channel = $channel;

        while (count($channel->callbacks) > 0) {
            try {
                $channel->wait(null, true, 1);
            } catch (AMQPTimeoutException $ex) {
                //I'd prefer it not to do this.
            }

            if ($this->tickFunction !== null && is_callable($this->tickFunction)) {
                $function = $this->tickFunction;
                $function($this);
            }

            usleep(50000);
        }

        $channel->close();
    }

    /**
     * Stop the worker.
     */
    public function stop()
    {
        $this->running = false;

        $this->channel->callbacks = [];
    }

    /**
     * Handle an incoming message and dispatch to a worker class.
     *
     * @internal
     *
     * @param AMQPMessage $message
     *
     * @throws RuntimeException Worker not running.
     */
    public function handleMessage(AMQPMessage $message)
    {
        if (!$this->running) {
            throw new RuntimeException(sprintf('You cannot call %s if the worker is not running', __FUNCTION__));
        }

        if ($this->ackTime === RPCWorkerAckTime::ACK_BEFORE) {
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        }

        $event = $this->getEventClass($message);
        $event->deserializeJSON($message->body);

        $handler = $this->getHandler($message);
        $handler->handle($event);

        $response = new AMQPMessage($event->serializeJSON(), ['correlation_id' => $message->get('correlation_id')]);

        $message->delivery_info['channel']->basic_publish($response, '', $message->get('reply_to'));

        if ($this->ackTime === RPCWorkerAckTime::ACK_AFTER) {
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        }
    }

    /**
     * Get the handler either from the passed in argument or from the callback provided.
     *
     * @param AMQPMessage $message
     *
     * @return EventHandlerInterface
     */
    protected function getHandler(AMQPMessage $message)
    {
        if (is_callable($this->handler)) {
            $callback = $this->handler;
            $handler  = $callback($message->body, $message->get('routing_key'));
        } else {
            $handler = $this->handler;
        }

        if (!$handler instanceof EventHandlerInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'Callback/argument must return an instance of %s, returned type was %s.',
                    EventHandlerInterface::class, get_class($handler)
                )
            );
        }

        return $handler;
    }

    /**
     * Get the event class either from the passed in argument or from the callback provided.
     *
     * @param AMQPMessage $message
     *
     * @return EventInterface
     * @throws InvalidArgumentException Callback didn't return a valid type.
     */
    protected function getEventClass(AMQPMessage $message)
    {
        if (is_callable($this->eventClass)) {
            $callback  = $this->eventClass;
            $classname = $callback($message->body, $message->get('routing_key'));
        } else {
            $classname = $this->eventClass;
        }

        $event = new $classname();

        if (!$event instanceof EventInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'Callback/argument must return an instance of %s, returned type was %s.',
                    EventInterface::class, get_class($event)
                )
            );
        }

        return $event;
    }
}