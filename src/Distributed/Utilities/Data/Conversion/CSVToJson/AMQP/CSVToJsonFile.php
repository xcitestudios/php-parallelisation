<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson\AMQP;

use com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJsonFile as CSVToJsonFileBase;

class CSVToJsonFile extends CSVToJsonFileBase
{
    /**
     * @var DispatchEventHandler
     */
    protected $handler;

    public function __construct(DispatchEventHandler $handler, $rowLimit = 100)
    {
        parent::__construct($handler, $rowLimit);
    }

    protected function dispatchEventForRows(array $rows)
    {
        parent::dispatchEventForRows($rows);
    }

    /**
     */
    public function process()
    {
        parent::process();

        $this->handler->waitForAllEvents();
    }
}