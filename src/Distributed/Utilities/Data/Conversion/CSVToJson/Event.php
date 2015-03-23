<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson;

use com\xcitestudios\Parallelisation\Event as EventAbstract;

/**
 * Event used for converting CSV to JSON.
 *
 * @package com.xcitestudios.Parallelisation
 * @subpackage Distributed.Utilities.Data.Conversion.CSVToJson
 */
class Event extends EventAbstract
{
    /**
     * Instantiate empty input and output classes.
     */
    public function __construct()
    {
        $this->input  = new EventInput();
        $this->output = new EventOutput();
    }

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
    public function getType()
    {
        return 'com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\CSVToJson';
    }

    /**
     * Wrapper to strongly type the input in IDE's.
     *
     * @return EventInput
     */
    public function getInput()
    {
        return parent::getInput();
    }

    /**
     * Wrapper to strongly type the output in IDE's.
     *
     * @return EventOutput
     */
    public function getOutput()
    {
        return parent::getOutput();
    }
}