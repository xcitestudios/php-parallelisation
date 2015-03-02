<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Interfaces;

use com\xcitestudios\Generic\Data\Manipulation\Interfaces\SerializationInterface;

/**
 * Generic output for any event
 */
interface EventOutputInterface
    extends SerializationInterface
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
}