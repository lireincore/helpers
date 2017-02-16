<?php

namespace LireinCore\Helpers\Seo;

class Robots
{
    /**
     * @var string path to the file to be written
     */
    private $filePath;

    /**
     * @var bool whether to gzip the resulting files or not
     */
    private $useGzip = false;

    /**
     * @var array
     */
    private $elements = [];

    /**
     * @param string $filePath path of the file to write to
     * @throws \InvalidArgumentException
     */
    public function __construct($filePath)
    {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(
                "Please specify valid file path. Directory not exists. You have specified: {$dir}."
            );
        }

        if (file_exists($filePath)) {
            $filePath = realpath($filePath);
            if (is_writable($filePath)) {
                unlink($filePath);
            } else {
                throw new \RuntimeException("File \"$filePath\" is not writable.");
            }
        }

        $this->filePath = $filePath;
    }

    /**
     * Adds a new item to robots
     *
     * @param string $name
     * @param string $value
     *
     * @throws \InvalidArgumentException
     */
    public function addItem($name, $value)
    {
        $this->elements[] = ['name' => $name, 'value' => $value];
    }

    /**
     * Finishes writing
     */
    public function write()
    {
        $filePath = $this->filePath;
        if ($this->useGzip) {
            $filePath = 'compress.zlib://' . $filePath;
        }
        file_put_contents($filePath, $this->flush(), FILE_APPEND);
    }

    /**
     * Flushes buffer into file
     *
     * @return string
     */
    public function flush()
    {
        $content = '';
        foreach ($this->elements as $element) {
            $content .= "{$element['name']}: {$element['value']}" . PHP_EOL;
        }

        return $content;
    }

    /**
     * Sets whether the resulting files will be gzipped or not.
     * @param bool $value
     * @throws \RuntimeException when trying to enable gzip while zlib is not available or when trying to change
     * setting when some items are already written
     */
    public function setUseGzip($value)
    {
        if ($value && !extension_loaded('zlib')) {
            throw new \RuntimeException('Zlib extension must be enabled to gzip the sitemap.');
        }

        $this->useGzip = $value;
    }
}