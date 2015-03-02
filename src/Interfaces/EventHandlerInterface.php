<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Interfaces;

/**
 * Handler for an event instance. This should either be generic for the event type
 * or each type of event should have its own handler and implement this interface 
 * only for the type of event it can handle.
 */
interface EventHandlerInterface
{
    /**
     * Take the event and either check the type to handle it appropriately or strongly
     * type the event and read the input to create output.
     *
     * It is recommended output on the event should be presumed null and set here; however
     * if the event is to be handled by multiple objects then it could have output set in those cases.
     * 
     * @param EventInterface $event The IEvent instance to handle.
     * @return void
     */
    public function handle(EventInterface $event);
}