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
    protected $firstRowIsHeaders = false;

    /**
     * @var EventHandlerInterface
     */
    protected $handler;

    /**
     * @var array
     */
    protected $events = [];

    /**
     * @param EventHandlerInterface $handler
     * @param int                   $rowLimit
     */
    public function __construct(EventHandlerInterface $handler, $rowLimit = 100)
    {
        $this->handler  = $handler;
        $this->rowLimit = $rowLimit;
    }

    /**
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
     * @return int
     */
    public function getRowLimitPerWorker()
    {
        return $this->rowLimit;
    }

    /**
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
     * @return array
     */
    public function getCustomHeaders()
    {
        return $this->customHeaders;
    }

    /**
     * @return static
     */
    public function enableCustomHeaders()
    {
        $this->useCustomHeaders = true;

        return $this;
    }

    /**
     * @return static
     */
    public function disableCustomHeaders()
    {
        $this->useCustomHeaders = false;

        return $this;
    }

    /**
     * @param bool $firstRowIsHeaders
     *
     * @return static
     */
    public function setFirstRowIsHeaders($firstRowIsHeaders = false)
    {
        $this->firstRowIsHeaders = $firstRowIsHeaders;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFirstRowHeaders()
    {
        return $this->firstRowIsHeaders;
    }

    /**
     *
     */
    protected function calculateHeaders()
    {
        if ($this->useCustomHeaders) {
            if (count($this->customHeaders) === 0) {
                throw new RuntimeException('Use custom headers is true but no custom headers specified');
            }

            $this->headers = $this->customHeaders;
        } elseif(count($this->headers === 0)) {
            throw new RuntimeException('Cannot process, no headers in CSV and no custom headers specified');
        }
    }

    /**
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

        $this->events[] = $event;
    }

    /**
     */
    public abstract function process();
}