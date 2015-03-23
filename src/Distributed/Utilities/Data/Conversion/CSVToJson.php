<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion;

use com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\EventInput;
use com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\EventOutput;
use com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\Event;
use com\xcitestudios\Parallelisation\Interfaces\EventHandlerInterface;
use RuntimeException;
use stdClass;

/**
 * Base class for CSVToJson converters.
 *
 * @package com.xcitestudios.Parallelisation
 * @subpackage Distributed.Utilities.Data.Conversion
 */
abstract class CSVToJson
{
    /**
     * @var int
     */
    protected $rowLimit = 100;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $customHeaders = [];

    /**
     * @var bool
     */
    protected $useCustomHeaders = false;

    /**
     * @var bool
     */
    protected $firstRowIsHeaders = true;

    /**
     * @var EventHandlerInterface
     */
    protected $handler;

    /**
     * @var array
     */
    protected $events = [];

    /**
     * Specify the handler used for each event and the limit on the number of rows per event.
     *
     * @param EventHandlerInterface $handler
     * @param int                   $rowLimit
     */
    public function __construct(EventHandlerInterface $handler, $rowLimit = 100)
    {
        $this->handler  = $handler;
        $this->rowLimit = $rowLimit;
    }

    /**
     * Set the row limit for each event.
     *
     * @param int $limit
     *
     * @return static
     */
    public function setRowLimitPerWorker($limit = 100)
    {
        $this->rowLimit = $limit;

        return $this;
    }

    /**
     * Get the row limit for each event.
     *
     * @return int
     */
    public function getRowLimitPerWorker()
    {
        return $this->rowLimit;
    }

    /**
     * Set the custom headers to use.
     *
     * @param array $headers
     *
     * @return static
     */
    public function setCustomHeaders(array $headers)
    {
        $this->customHeaders = $headers;

        return $this;
    }

    /**
     * Get the custom headers specified.
     *
     * @return array
     */
    public function getCustomHeaders()
    {
        return $this->customHeaders;
    }

    /**
     * Enable the use of custom headers.
     *
     * @return static
     */
    public function enableCustomHeaders()
    {
        $this->useCustomHeaders = true;

        return $this;
    }

    /**
     * Disable the use of custom headers.
     *
     * @return static
     */
    public function disableCustomHeaders()
    {
        $this->useCustomHeaders = false;

        return $this;
    }

    /**
     * Designate the first row of the CSV as containing headers.
     *
     * @param bool $firstRowIsHeaders
     *
     * @return static
     */
    public function setFirstRowIsHeaders($firstRowIsHeaders = true)
    {
        $this->firstRowIsHeaders = $firstRowIsHeaders;

        return $this;
    }

    /**
     * True if first row of the CSV is headers.
     *
     * @return bool
     */
    public function isFirstRowHeaders()
    {
        return $this->firstRowIsHeaders;
    }

    /**
     * Make sure we have headers to use, populate this->headers with this->customHeaders if valid.
     */
    protected function calculateHeaders()
    {
        if ($this->useCustomHeaders) {
            if (count($this->customHeaders) === 0) {
                throw new RuntimeException('Use custom headers is true but no custom headers specified');
            }

            $this->headers = $this->customHeaders;
        } elseif(count($this->headers) === 0) {
            throw new RuntimeException('Cannot process, no headers in CSV and no custom headers specified');
        }
    }

    /**
     * Dispatches an event for a collection of CSV rows.
     *
     * @param array $rows
     */
    protected function dispatchEventForRows(array $rows)
    {
        $input = new EventInput();
        $input->setHeaders($this->headers);
        $input->setRows($rows);

        $event = new Event();
        $event->setInput($input);
        $event->setOutput(new EventOutput());

        $this->handler->handle($event);
    }

    /**
     * Process the CSV file.
     *
     * @return void
     */
    public abstract function process();

    /**
     * Is processing finished?
     *
     * @return bool
     */
    public abstract function isFinished();
}