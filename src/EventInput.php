<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation;

use com\xcitestudios\Parallelisation\Interfaces\EventInputInterface;
use JsonSerializable;
use stdClass;

/**
 * Generic input for any event
 *
 * @package com.xcitestudios.Parallelisation
 */
abstract class EventInput implements EventInputInterface, JsonSerializable
{
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
        $tempObj = json_decode($jsonString);

        return $tempObj;
    }

    /**
     * Convert this object into JSON so it can be handled by anything that supports JSON.
     *
     * @return string A JSON representation of this object.
     */
    public function serializeJSON()
    {
        return json_encode($this);
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
        $ret = new stdClass();

        return $ret;
    }
}