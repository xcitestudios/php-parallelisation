<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\AMQP;

use com\xcitestudios\Parallelisation\Distributed\Queue\AMQP\Interfaces\RPCDispatcherInterface;
use com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\Event;
use com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\EventInput;
use com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\EventOutput;
use com\xcitestudios\Parallelisation\Interfaces\EventHandlerInterface;
use com\xcitestudios\Parallelisation\Interfaces\EventInterface;
use InvalidArgumentException;

class DispatchEventHandler implements EventHandlerInterface
{
    /**
     * @var RPCDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @param RPCDispatcherInterface $dispatcher
     */
    public function __construct(RPCDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
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
    public function handle(EventInterface $event)
    {
        if (!$event instanceof Event) {
            throw new InvalidArgumentException(sprintf('You can only specify a more specific type of %s to %s.', Event::class, __FUNCTION__));
        }

        $this->dispatcher->handle($event);
    }

    /**
     * Calls the wait event on the dispatcher, blocking or non doesn't matter.
     */
    public function waitForAllEvents()
    {
        $this->dispatcher->waitForAllEvents();
    }
}