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
use com\xcitestudios\Parallelisation\Interfaces\EventInterface;
use com\xcitestudios\Parallelisation\Interfaces\EventOutputInterface;
use JsonSerializable;
use stdClass;

/**
 * Generic event
 *
 * @package com.xcitestudios.Parallelisation
 */
abstract class Event implements EventInterface, JsonSerializable
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
     * @var EventInputInterface
     */
    protected $input;

    /**
     * @var EventOutputInterface
     */
    protected $output;

    /**
     * Return the type of this event, this is an identifier to determine how to react to it.
     *
     * For example you could use a function name, e.g. CalculateFibonacci.
     *
     * It is recommended to namespace your types so they will not conflict with others, e.g.
     * "MyCompany.Math.CalculateFibonacci"
     *
     * @return string
     */
    public abstract function getType();

    /**
     * Convert a JSON representation of this event in to an actual IEvent object. Either
     * a generic "event" type of a specific instance type.
     *
     * @param EventInputInterface $eventInput Input to run the event
     *
     * @return static
     */
    public function setInput(EventInputInterface $eventInput)
    {
        $this->input = $eventInput;

        return $this;
    }

    /**
     * Gets the input for this event that can be passed along with the event to handle it correctly.
     *
     * @return EventInputInterface An instance of the EventInputInterface either loosely or strongly typed.
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Sets the output for this event after it has been handled. This should remain null until an output has been
     * decided.
     *
     * @param EventOutputInterface $eventOutput Output of handling the event
     *
     * @return static
     */
    public function setOutput(EventOutputInterface $eventOutput)
    {
        $this->output = $eventOutput;

        return $this;
    }

    /**
     * Gets the output for this event after it has been handled. This should remain null until an output has been
     * decided.
     *
     * @return EventOutputInterface An instance of the EventOutputInterface either loosely or strongly typed.
     */
    public function getOutput()
    {
        return $this->output;
    }

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

        if (property_exists($tempObj, 'input') && $this->input instanceof EventInputInterface) {
            $this->input->deserializeJSON(json_encode($tempObj->input));
        }

        if (property_exists($tempObj, 'output') && $this->output instanceof EventOutputInterface) {
            $this->output->deserializeJSON(json_encode($tempObj->output));
        }

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

        $ret->type = $this->getType();

        if ($this->input instanceof EventInputInterface) {
            $ret->input = json_decode(json_encode($this->getInput()));
        }

        if ($this->output instanceof EventOutputInterface) {
            $ret->output = json_decode(json_encode($this->getOutput()));
        }

        $ret->successful      = $this->wasSuccessful();
        $ret->responseMessage = $this->getResponseMessage();

        return $ret;
    }
}
