<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion\Interfaces;

/**
 * Interface for converting CSV to JSON.
 *
 * @package    com.xcitestudios.Parallelisation
 * @subpackage Distributed.Utilities.Data.Conversion.Interfaces
 */
interface CSVToJsonInterface
{
    /**
     * Set the row limit for each event.
     *
     * @param int $limit
     *
     * @return void
     */
    public function setRowLimitPerWorker($limit = 100);

    /**
     * Get the row limit for each event.
     *
     * @return int
     */
    public function getRowLimitPerWorker();

    /**
     * Set the custom headers to use.
     *
     * @param array $headers
     *
     * @return void
     */
    public function setCustomHeaders(array $headers);

    /**
     * Get the custom headers specified.
     *
     * @return array
     */
    public function getCustomHeaders();

    /**
     * Enable the use of custom headers.
     *
     * @return void
     */
    public function enableCustomHeaders();

    /**
     * Disable the use of custom headers.
     *
     * @return void
     */
    public function disableCustomHeaders();

    /**
     * Designate the first row of the CSV as containing headers.
     *
     * @param bool $firstRowIsHeaders
     *
     * @return void
     */
    public function setFirstRowIsHeaders($firstRowIsHeaders = true);

    /**
     * True if first row of the CSV is headers.
     *
     * @return bool
     */
    public function isFirstRowHeaders();

    /**
     * Process the CSV file.
     *
     * @return void
     */
    public function process();

    /**
     * Is processing finished?
     *
     * @return bool
     */
    public function isFinished();
}