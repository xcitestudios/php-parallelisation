<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\AMQP;

use com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\Event;
use com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\EventInput;
use com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\EventOutput;
use com\xcitestudios\Parallelisation\Interfaces\EventHandlerInterface;
use com\xcitestudios\Parallelisation\Interfaces\EventInterface;
use stdClass;
use InvalidArgumentException;

/**
 * The worker class for converting CSV to JSON.
 *
 * @package com.xcitestudios.Parallelisation
 * @subpackage Distributed.Utilities.Data.Conversion.CSVToJson.AMQP
 */
class CSVToJsonWorker implements EventHandlerInterface
{
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
     * @throws InvalidArgumentException
     */
    public function handle(EventInterface $event)
    {
        $allowedType = (new Event())->getType();

        if ($event->getType() !== $allowedType) {
            throw new InvalidArgumentException(
                sprintf('Class %s only works with events of type %s', __CLASS__, $allowedType)
            );
        }

        $input = $event->getInput(); /** @var $input EventInput */

        $headers = $input->getHeaders();
        $rows    = $input->getRows();
        $output  = new EventOutput();

        foreach ($rows as $row) {
            $jsonObject = $this->parseRow($headers, $row);

            $output->addJsonObjectString(json_encode($jsonObject));
        }

        $output->setWasSuccessful(true);

        $event->setOutput($output);
    }

    /**
     * @param array $headers
     * @param array $row
     *
     * @return stdClass
     */
    protected function parseRow(array $headers, array $row)
    {
        $ret = new stdClass();

        for ($i = 0; $i < count($headers); $i++) {
            $ret->{$headers[$i]} = array_key_exists($i, $row) ? $row[$i] : "";
        }

        return $ret;
    }
}