<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson;

use com\xcitestudios\Parallelisation\EventOutput as EventOutputAbstract;
use stdClass;

class EventOutput extends EventOutputAbstract
{
    /**
     * @var array
     */
    protected $jsonObjectStrings = [];

    /**
     * Updates the element implementing this interface using a JSON representation.
     *
     * This means updating the state of this object with that defined in the JSON
     * as opposed to returning a new instance of this object.
     *
     * @param string $jsonString Representation of the object.
     *
     * @return stdClass
     */
    public function deserializeJSON($jsonString)
    {
        $tempObj = parent::deserializeJSON($jsonString);

        if (property_exists($tempObj, 'jsonObjectStrings') && is_array($tempObj->jsonObjectStrings)) {
            $this->jsonObjectStrings = $tempObj->jsonObjectStrings;
        }

        return $tempObj;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *       which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        $ret = parent::jsonSerialize();

        $ret->jsonObjectStrings = $this->jsonObjectStrings;

        return $ret;
    }
}