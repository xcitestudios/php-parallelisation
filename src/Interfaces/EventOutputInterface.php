<?php
namespace com\xcitestudios\Parallelisation\Interfaces;

/**
 * Generic output for any event
 */
interface EventOutputInterface
{
	/**
	 * Sets if the event handled correctly and can the data be trusted to be correct for the request.
	 *
	 * @param bool $success True if yes, false if no.
	 * @return void
	 */
	public function setWasSuccessful($success);
	
	/**
	 * Did the event get handled correctly and can the data be trusted to be correct for the request.
	 *
	 * @return bool
	 */
	public function wasSuccessful();
	
	/**
	 * Get a general human readable response, useful for providing an error message if WasSuccessful returns false.
	 */
	public function getResponseMessage($message);
	
	/**
	 * Set a general human readable response, useful for providing an error message if WasSuccessful returns false.
	 *
	 * @param string $message The message to set
	 * @return void
	 */
	public function setResponseMessage($message);
	
	/**
     * Convert a JSON representation of this event output in to an actual EventOutputInterface object. Either
     * a generic "event input" type of a specific instance type.
	 *
	 * @param string $jsonString Representation of this input
	 * @return void
	 */
	public function deserialize($jsonString);
	
	/**
	 * Convert this event output into JSON so it can be handled by anything that supports JSON.
	 *
	 * @return string A representation of this input.
	 */
	public function serialize();
}