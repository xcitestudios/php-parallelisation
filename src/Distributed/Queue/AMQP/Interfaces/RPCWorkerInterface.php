<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Queue\AMQP\Interfaces;

/**
 * RPC worker implementation for AMQP.
 *
 * @package    com.xcitestudios.Parallelisation
 * @subpackage Distributed.Queue.AMQP
 */
interface RPCWorkerInterface
{
    /**
     * Register a function that will be called at most every 50ms to allow other code to run while the
     * worker is running. For example you could use this function to call stop on the worker. One argument
     * will be passed to it, an instance of this.
     *
     * @param callable $function
     *
     * @return static
     */
    public function setLoopCallbackFunction(callable $function);

    /**
     * Kick off the worker, this will not return.
     */
    public function start();

    /**
     * Stop the worker.
     */
    public function stop();
}