<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion;

use com\xcitestudios\Generic\Data\KeyValueStorage\ArrayStore;
use com\xcitestudios\Generic\Data\KeyValueStorage\Interfaces\IterableStorageInterface;
use com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\Event;
use com\xcitestudios\Parallelisation\Interfaces\EventHandlerInterface;
use InvalidArgumentException;

/**
 * CSVToJson Handler for a file.
 *
 * @package    com.xcitestudios.Parallelisation
 * @subpackage Distributed.Utilities.Data.Conversion
 */
class CSVToJsonFile extends CSVToJsonAbstract
{
    /**
     * @var string
     */
    protected $filename = null;

    /**
     * @var IterableStorageInterface
     */
    protected $events;

    /**
     * Specify the handler used for each event and the limit on the number of rows per event.
     *
     * @param EventHandlerInterface    $handler
     * @param int                      $rowLimit
     * @param IterableStorageInterface $eventStorage          Handles local storage of events. Will use {@see
     *                                                        ArrayStore} by default.
     */
    public function __construct(EventHandlerInterface $handler, $rowLimit = 100, IterableStorageInterface $eventStorage = null)
    {
        parent::__construct($handler, $rowLimit);

        $this->events = $eventStorage ?: new ArrayStore();
    }

    /**
     * Set the filename to be parsed.
     *
     * @param $filename
     *
     * @return static
     * @throws InvalidArgumentException Problem with CSV file
     */
    public function setFilename($filename)
    {
        if (!file_exists($filename)) {
            throw new InvalidArgumentException(sprintf('CSV file %s does not exist.', $filename));
        }

        if (!is_readable($filename)) {
            throw new InvalidArgumentException(sprintf('CSV file %s is not readable.', $filename));
        }

        if (filesize($filename) === 0) {
            throw new InvalidArgumentException(sprintf('Cannot use zero length file %s', $filename));
        }

        $this->filename = realpath($filename);

        return $this;
    }

    /**
     * Process the CSV file, blocks until completion.
     *
     * @return string
     */
    public function process()
    {
        $csvHandle = $this->prepareFileAndHeaders();
        $this->handleRows($csvHandle);

        while (!$this->isFinished()) {
            usleep(50000);
        }

        $jsonObjects = '[';

        foreach ($this->events as $event) {
            /** @var Event $event */
            foreach ($event->getOutput()->getJsonObjectStrings() as $row) {
                $jsonObjects .= $row . ',';
            }
        }

        $jsonObjects = substr($jsonObjects, 0, -1) . ']';

        return $jsonObjects;
    }

    /**
     * Open the CSV file, and do some basic checks then calculate headers.
     *
     * @return resource
     */
    protected function prepareFileAndHeaders()
    {
        if ($this->filename === null) {
            throw new InvalidArgumentException('Cannot call process without setting a filename first');
        }

        $this->headers = [];

        $csv = fopen($this->filename, 'r');

        if (fread($csv, 3) != b"\xEF\xBB\xBF") {
            rewind($csv);
        }

        if ($this->firstRowIsHeaders) {
            $this->headers = fgetcsv($csv);
        }

        $this->calculateHeaders();

        return $csv;

    }

    /**
     * Handle the rows in the provided stream (resource) of a CSV File. Stream is closed
     * once the file is read.
     *
     * @param resource $csvHandle
     */
    protected function handleRows($csvHandle)
    {
        $chunk = [];
        while (!feof($csvHandle) && ($row = fgetcsv($csvHandle)) !== false) {

            $chunk[] = $row;

            if (count($chunk) === $this->rowLimit) {
                $this->dispatchEventForRows($chunk);
                $chunk = [];
            }
        }

        fclose($csvHandle);

        if (count($chunk) > 0) {
            $this->dispatchEventForRows($chunk);
        }
    }

    /**
     * Dispatches an event for a collection of CSV rows.
     *
     * @param array $rows
     */
    protected function dispatchEventForRows(array $rows)
    {
        $event = $this->createEventForRows($rows);
        
        while ($this->events->has($key = uniqid("", true))) {
		}

        $this->events->set($key, $event);

        $this->handler->handle($event);
    }

    /**
     * Is processing finished?
     *
     * @return bool
     */
    public function isFinished()
    {
        foreach ($this->events as $event) {
            /** @var Event $event */
            if ($event->getOutput() === null) {
                return false;
            }

            if (count($event->getOutput()->getJsonObjectStrings()) === 0) {
                return false;
            }
        }

        return true;
    }
}
