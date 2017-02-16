<?php

namespace LireinCore\Helpers\Seo;

use XMLWriter;

class Sitemap
{
    /**
     * @var array path parts to the file to be written
     */
    private $pathInfo;

    /**
     * @var integer Maximum allowed number of bytes per single file.
     */
    private $maxFileSize = 10485760;

    /**
     * @var integer Maximum allowed number of URLs in a single file.
     */
    private $maxUrls = 50000;

    /**
     * @var integer number of URLs to be kept in memory before writing it to file
     */
    private $bufferSize = 1000;

    /**
     * @var bool if XML should be indented
     */
    private $useIndent = true;

    /**
     * @var bool whether to gzip the resulting files or not
     */
    private $useGzip = false;

    /**
     * @var integer Current file size written
     */
    private $fileSize = 0;

    /**
     * @var integer number of URLs added
     */
    private $urlsCount = 0;

    /**
     * @var integer number of files written
     */
    private $fileCount = 0;

    /**
     * @var array path of files written
     */
    private $writtenFilePaths = [];

    /**
     * @var XMLWriter
     */
    private $writer;

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

        $parts = pathinfo($filePath);
        if ($parts['extension'] === 'gz') {
            $filenameParts = pathinfo($parts['filename']);
            if (!empty($filenameParts['extension'])) {
                $parts['filename'] = $filenameParts['filename'];
                $parts['extension'] = $filenameParts['extension'] . '.gz';
            }
        }

        $this->pathInfo = [
            'dir' => $parts['dirname'],
            'name' => $parts['filename'],
            'ext' => $parts['extension']
        ];
    }

    /**
     * Sets maximum allowed number of bytes per single file.
     * Default is 10485760 bytes which equals to 10 megabytes.
     *
     * @param integer $bytes
     */
    public function setMaxFileSize($bytes)
    {
        $this->maxFileSize = (int)$bytes;
    }

    /**
     * Sets maximum number of URLs to write in a single file.
     * Default is 50000.
     * @param integer $number
     */
    public function setMaxUrls($number)
    {
        $this->maxUrls = (int)$number;
    }

    /**
     * Sets number of URLs to be kept in memory before writing it to file.
     * Default is 1000.
     *
     * @param integer $number
     */
    public function setBufferSize($number)
    {
        $this->bufferSize = (int)$number;
    }

    /**
     * Sets if XML should be indented.
     * Default is true.
     *
     * @param bool $value
     */
    public function setUseIndent($value)
    {
        $this->useIndent = (bool)$value;
    }

    /**
     * Sets whether the resulting files will be gzipped or not.
     *
     * @param bool $value
     * @throws \RuntimeException when trying to enable gzip while zlib is not available or when trying to change
     * setting when some items are already written
     */
    public function setUseGzip($value)
    {
        if ($value && !extension_loaded('zlib')) {
            throw new \RuntimeException('Zlib extension must be enabled to gzip the sitemap.');
        }
        if ($this->urlsCount && $value != $this->useGzip) {
            throw new \RuntimeException('Cannot change the gzip value once items have been added to the sitemap.');
        }
        $this->useGzip = $value;
    }

    /**
     * Adds an URL to sitemap
     *
     * @param Url $url
     */
    public function addItem(Url $url)
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent($this->useIndent);
        $writer->startElement('url');
        $writer->writeElement('loc', $url->getLocation());
        if ($url->getLastModified() !== null) {
            $writer->writeElement('lastmod', date('c', $url->getLastModified()));
        }
        if ($url->getChangeFrequency() !== null) {
            $writer->writeElement('changefreq', $url->getChangeFrequency());
        }
        if ($url->getPriority() !== null) {
            $writer->writeElement('priority', number_format($url->getPriority(), 1, '.', ','));
        }
        $writer->endElement();
        $urlElement = $writer->flush();

        if ($this->urlsCount === 0) {
            $this->createNewFile();
        } elseif ($this->urlsCount % $this->maxUrls === 0 || !$this->canWriteString($urlElement)) {
            $this->finishFile();
            $this->createNewFile();
        }

        if ($this->urlsCount % $this->bufferSize === 0) {
            $this->flush();
        }

        $this->writer->writeRaw($urlElement);
        $this->urlsCount++;
    }

    /**
     * Finish writing
     */
    public function finish()
    {
        $this->finishFile();

        if ($this->fileCount > 1) {
            $fullName = $this->pathInfo['dir'] . DIRECTORY_SEPARATOR . $this->pathInfo['name'];
            $filepath = "{$fullName}.{$this->pathInfo['ext']}";
            $newname = "{$fullName}_1.{$this->pathInfo['ext']}";
            rename($filepath, $newname);
            $this->writtenFilePaths[0] = $newname;
        }
    }

    /**
     * Returns an array of URLs written
     *
     * @param string $baseUrl base URL of all the sitemaps written
     * @return array URLs of sitemaps written
     */
    public function getSitemapUrls($baseUrl)
    {
        $urls = [];
        foreach ($this->writtenFilePaths as $file) {
            $urls[] = $baseUrl . pathinfo($file, PATHINFO_BASENAME);
        }
        return $urls;
    }

    /**
     * Creates new file
     *
     * @throws \RuntimeException if file is not writeable
     */
    private function createNewFile()
    {
        $this->fileCount++;
        $filePath = $this->getFilePath();

        if (file_exists($filePath)) {
            $filePath = realpath($filePath);
            if (is_writable($filePath)) {
                unlink($filePath);
            } else {
                throw new \RuntimeException("File \"$filePath\" is not writable.");
            }
        }

        $this->fileSize = 0;
        $this->writtenFilePaths[] = $filePath;

        $this->writer = new XMLWriter();
        $this->writer->openMemory();
        $this->writer->startDocument('1.0', 'UTF-8');
        $this->writer->setIndent($this->useIndent);
        $this->writer->startElement('urlset');
        $this->writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    }

    /**
     * Flushes buffer into file
     */
    private function flush()
    {
        $buffer = $this->writer->flush(true);
        $this->fileSize += mb_strlen($buffer, '8bit');
        $filePath = $this->getFilePath();
        if ($this->useGzip) {
            $filePath = 'compress.zlib://' . $filePath;
        }
        file_put_contents($filePath, $buffer, FILE_APPEND);
    }

    /**
     * Writes closing tags to current file
     */
    private function finishFile()
    {
        if ($this->writer !== null) {
            $this->writer->endElement();
            $this->writer->endDocument();
            $this->flush();
        }
    }

    /**
     * @return string path of current file
     */
    private function getFilePath()
    {
        $fullName = $this->pathInfo['dir'] . DIRECTORY_SEPARATOR . $this->pathInfo['name'];
        if ($this->fileCount > 1) {
            $fullName = "{$fullName}_{$this->fileCount}";
        }

        return "{$fullName}.{$this->pathInfo['ext']}";
    }

    /**
     * @param string $string
     * @return bool
     */
    private function canWriteString($string)
    {
        $extraForClosingTags = 20;

        return $this->fileSize + mb_strlen($string, '8bit') + $extraForClosingTags < $this->maxFileSize;
    }
}