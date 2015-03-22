<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson;

use com\xcitestudios\Parallelisation\Event as EventAbstract;

class Event extends EventAbstract
{
    public function getType()
    {
        return 'xcitestudios.Data.Conversion.CSVToJson';
    }
}