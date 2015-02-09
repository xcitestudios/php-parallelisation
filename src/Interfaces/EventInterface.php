<?php
namespace com\xcitestudios\Parallelisation\Interfaces;

use com\xcitestudios\Generic\Data\Manipulation\Interfaces\SerializationInterface;

/**
 * An event which determines the type of event and the input and output data storage for that event.
 */
interface EventInterface
    extends SerializationInterface
{
    /**
     * Return the type of this event, this is an identifier to determine how to react to it.
     *
     * For example you could use a function name, e.g. CalculateFibonacci. 
     * 
     * It is recommended to namespace your types so they will not conflict with others, e.g. "MyCompany.Math.CalculateFibonacci"
     * @return string
     */
    public function getType();

    /**
     * Convert a JSON representation of this event in to an actual IEvent object. Either
     * a generic "event" type of a specific instance type.
     *
     * @param EventInputInterface $eventInput Input to run the event
     * @return void
     */
    public function setInput(EventInputInterface $eventInput);
    
    /**
     * Gets the input for this event that can be passed along with the event to handle it correctly.
     *
     * @return EventInputInterface An instance of the EventInputInterface either loosely or strongly typed.
     */
    public function getInput();
    
    /**
     * Sets the output for this event after it has been handled. This should remain null until an output has been decided.
     *
     * @param EventOutputInterface $eventOutput Output of handling the event
     * @return void
     */
    public function setOutput(EventOutputInterface $eventOutput);
    
    /**
     * Gets the output for this event after it has been handled. This should remain null until an output has been decided.
     *
     * @return EventOutputInterface An instance of the EventOutputInterface either loosely or strongly typed.
     */
    public function getOutput();
}
