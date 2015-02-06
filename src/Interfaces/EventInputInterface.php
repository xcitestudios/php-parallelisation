<?php
namespace com\xcitestudios\Parallelisation\Interfaces;

/**
 * Generic input for any event
 */
interface EventInputInterface
{
    /**
     * Convert a JSON representation of this event input in to an actual EventInputInterface object. Either
     * a generic "event input" type of a specific instance type.
     *
     * @param string $jsonString Representation of this input
     * @return void
     */
    public function deserialize($jsonString);
    
    /**
     * Convert this event input into JSON so it can be handled by anything that supports JSON.
     *
     * @return string A representation of this input.
     */
    public function serialize();
}