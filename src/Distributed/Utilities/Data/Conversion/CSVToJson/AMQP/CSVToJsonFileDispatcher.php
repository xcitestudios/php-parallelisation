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
use com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJsonFile as CSVToJsonFileBase;
use com\xcitestudios\Parallelisation\Interfaces\EventInterface;
use stdClass;

/**
 * The dispatch wrapper used to dispatch CSV to JSON events to an AMQP queue.
 *
 * @package com.xcitestudios.Parallelisation
 * @subpackage Distributed.Utilities.Data.Conversion.CSVToJson.AMQP
 */
class CSVToJsonFileDispatcher extends CSVToJsonFileBase
{
    /**
     * @var RPCDispatcherInterface
     */
    protected $handler;

    /**
     * Pass in the RPC dispatcher to use for AMQP as well as a row limit per event.
     *
     * @param RPCDispatcherInterface $dispatcher
     * @param int                    $rowLimit
     */
    public function __construct(RPCDispatcherInterface $dispatcher, $rowLimit = 100)
    {
        parent::__construct($dispatcher, $rowLimit);
    }

    /**
     * Callback called when an event returns.
     *
     * @param callable $callback
     */
    public function addEventReturnedCallback(callable $callback)
    {
        $this->handler->addEventReturnedCallback($callback);
    }

    /**
     * Callback called when an event times out.
     *
     * @param callable $callback
     */
    public function addEventTimedOutCallback(callable $callback)
    {
        $this->handler->addEventTimedOutCallback($callback);
    }

    /**
     * Process the CSV file. Returns immediately so other work can be done.
     *
     * @return void
     */
    public function process()
    {
        $csvHandle = $this->prepareFileAndHeaders();
        $this->handleRows($csvHandle);
    }

    /**
     * Dispatches an event for a collection of CSV rows.
     *
     * @param array $rows
     */
    protected function dispatchEventForRows(array $rows)
    {
        $event = $this->createEventForRows($rows);

        $this->handler->handle($event);
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