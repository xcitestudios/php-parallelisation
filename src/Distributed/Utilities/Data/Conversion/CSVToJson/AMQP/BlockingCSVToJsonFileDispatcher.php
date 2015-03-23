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
use com\xcitestudios\Parallelisation\Interfaces\EventInterface;
use stdClass;

/**
 * The dispatch wrapper used to dispatch CSV to JSON events to an AMQP queue, blocks until all complete
 *
 * @package com.xcitestudios.Parallelisation
 * @subpackage Distributed.Utilities.Data.Conversion.CSVToJson.AMQP
 */
class BlockingCSVToJsonFileDispatcher extends CSVToJsonFileDispatcher
{
    /**
     * @var RPCDispatcherInterface
     */
    protected $handler;

    /**
     * @var array
     */
    protected $rows = [];

    /**
     * Pass in the RPC dispatcher to use for AMQP as well as a row limit per event.
     *
     * @param RPCDispatcherInterface $dispatcher
     * @param int                    $rowLimit
     */
    public function __construct(RPCDispatcherInterface $dispatcher, $rowLimit = 100)
    {
        parent::__construct($dispatcher, $rowLimit);

        $this->addEventReturnedCallback([$this, 'eventReturned']);
        $this->addEventTimedOutCallback([$this, 'eventTimedOut']);
    }

    /**
     * Called for each event coming back that timed out
     *
     * @internal
     * @param EventInterface $event
     */
    public function eventTimedOut(EventInterface $event)
    {
        /** @var $event Event */
        $event->getOutput()->getJsonObjectStrings();
    }

    /**
     * Called for each event coming back that returned data.
     *
     * @internal
     * @param EventInterface $event
     */
    public function eventReturned(EventInterface $event)
    {
        /** @var $event Event */
        foreach ($event->getOutput()->getJsonObjectStrings() as $string) {
            $this->rows[] = $string;
        }
    }

    /**
     * Process the CSV file. Does not return until all data is completed.
     * The return will be the data.
     *
     * @return array
     */
    public function process()
    {
        parent::process();

        while(!$this->isFinished()) {
            usleep(50000);
        }

        return $this->rows;
    }

    /**
     * Is processing finished?
     *
     * @return bool
     */
    public function isFinished()
    {
        return $this->handler->getCompletedEvents() + $this->handler->getTimedOutEvents() === $this->handler->getDispatchedEvents();
    }
}