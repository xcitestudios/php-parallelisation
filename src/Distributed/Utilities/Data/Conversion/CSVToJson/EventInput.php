<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson;

use com\xcitestudios\Parallelisation\EventInput as EventInputAbstract;
use stdClass;

class EventInput extends EventInputAbstract
{
    /**
     * @var string[]
     */
    protected $headers;

    /**
     * @var array
     */
    protected $rows;

    /**
     * @return string[]
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string[] $headers
     *
     * @return static
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @return array
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param array $rows
     *
     * @return static
     */
    public function setRows(array $rows)
    {
        $this->rows = $rows;

        return $this;
    }

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

        if (property_exists($tempObj, 'headers') && is_array($tempObj->headers)) {
            $this->headers = $tempObj->headers;
        }

        if (property_exists($tempObj, 'rows') && is_array($tempObj->rows)) {
            $this->rows = $tempObj->rows;
        }
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

        $ret->headers = $this->headers;
        $ret->rows    = $this->rows;

        return $ret;
    }
}