<?php
/**
 * com.xcitestudios.Parallelisation
 *
 * @copyright Wade Womersley (xcitestudios)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://xcitestudios.com/
 */

namespace com\xcitestudios\Parallelisation\Distributed\Utilities\Data\Conversion;

use InvalidArgumentException;

class CSVToJsonFile extends CSVToJson
{
    /**
     * @var string
     */
    protected $filename = null;

    /**
     * @param $filename
     *
     * @return static
     */
    public function setFilename($filename)
    {
        if (!file_exists($filename)) {
            throw new InvalidArgumentException(sprintf('CSV file %s does not exist.', $filename));
        }

        if (!is_readable($filename)) {
            throw new InvalidArgumentException(sprintf('CSV file %s is not readable.', $filename));
        }

        if(filesize($filename) === 0) {
            throw new InvalidArgumentException(sprintf('Cannot use zero length file %s', $filename));
        }

        $this->filename = realpath($filename);

        return $this;
    }

    /**
     */
    public function process()
    {
        if ($this->filename === null) {
            throw new InvalidArgumentException('Cannot call process without setting a filename first');
        }

        $this->headers = [];

        $csv = fopen($this->filename, 'r');

        if ($this->firstRowIsHeaders) {
            $this->headers = fgetcsv($csv);
        }

        $this->calculateHeaders();

        $chunk = [];
        while (!feof($csv)) {
            $chunk[] = fgetcsv($csv);

            if (count($chunk) === $this->rowLimit) {
                $this->dispatchEventForRows($chunk);
                $chunk = [];
            }
        }

        fclose($csv);

        if (count($chunk) > 0) {
            $this->dispatchEventForRows($chunk);
        }
    }
}