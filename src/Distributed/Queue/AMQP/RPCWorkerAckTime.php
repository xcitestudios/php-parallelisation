<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Queue\AMQP;

/**
 * When to send ACK when a worker.
 */
abstract class RPCWorkerAckTime
{
    /**
     * Send ACK before working on the job.
     */
    const ACK_BEFORE = 1;

    /**
     * Send ACK after working on the job.
     */
    const ACK_AFTER = 2;
}