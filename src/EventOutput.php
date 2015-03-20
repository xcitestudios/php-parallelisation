<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation;

use com\xcitestudios\Parallelisation\Interfaces\EventOutputInterface;
use JsonSerializable;
use stdClass;

/**
 * Generic output for any event
 *
 * @package com.xcitestudios.Parallelisation
 */
abstract class EventOutput implements EventOutputInterface, JsonSerializable
{
    /**
     * @var boolean
     */
    protected $successful = false;

    /**
     * @var string
     */
    protected $responseMessage;

    /**
     * Sets if the event handled correctly and can the data be trusted to be correct for the request.
     *
     * @param bool $success True if yes, false if no.
     *
     * @return static
     */
    public function setWasSuccessful($success)
    {
        $this->successful = $success;

        return $this;
    }

    /**
     * Did the event get handled correctly and can the data be trusted to be correct for the request.
     *
     * @return bool
     */
    public function wasSuccessful()
    {
        return $this->successful;
    }

    /**
     * Get a general human readable response, useful for providing an error message if WasSuccessful returns false.
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    /**
     * Set a general human readable response, useful for providing an error message if WasSuccessful returns false.
     *
     * @param string $message The message to set
     *
     * @return static
     */
    public function setResponseMessage($message)
    {
        $this->responseMessage = $message;

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
        $tempObj = json_decode($jsonString);

        if (property_exists($tempObj, 'successful') && is_bool($tempObj->successful)) {
            $this->successful = (bool)$tempObj->successful;
        }

        if (property_exists($tempObj, 'responseMessage') && is_string($tempObj->responseMessage)) {
            $this->responseMessage = $tempObj->responseMessage;
        }

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

        $ret->successful      = $this->wasSuccessful();
        $ret->responseMessage = $this->getResponseMessage();

        return $ret;
    }
}