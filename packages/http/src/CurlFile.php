<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 .
 * @license    MIT
 */

namespace Windwalker\Http;

/**
 * The CurlFile class.
 *
 * @since  3.5.13
 */
class CurlFile
{
    /**
     * Property file.
     *
     * @var string
     */
    protected $file;

    /**
     * Property mimetype.
     *
     * @var string
     */
    protected $mimetype;

    /**
     * Property filename.
     *
     * @var string
     */
    protected $filename;

    /**
     * CurlFile constructor.
     *
     * @param  string  $file
     * @param  string  $mimetype
     * @param  string  $filename
     */
    public function __construct(string $file, ?string $mimetype = null, ?string $filename = null)
    {
        $this->setFile($file, $mimetype, $filename);
    }

    /**
     * Method to get property File
     *
     * @return  string
     *
     * @since  3.5.13
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * Method to set property file
     *
     * @param  string       $file
     * @param  string|null  $mimetype
     * @param  string|null  $filename
     *
     * @return  static  Return self to support chaining.
     *
     * @since  3.5.13
     */
    public function setFile(string $file, ?string $mimetype = null, ?string $filename = null): self
    {
        $this->file     = $file;
        $this->mimetype = $mimetype ?: mime_content_type($file);
        $this->filename = $filename ?: basename($file);

        return $this;
    }

    /**
     * Method to get property Mimetype
     *
     * @return  string
     *
     * @since  3.5.13
     */
    public function getMimetype(): string
    {
        return $this->mimetype;
    }

    /**
     * Method to set property mimetype
     *
     * @param  string  $mimetype
     *
     * @return  static  Return self to support chaining.
     *
     * @since  3.5.13
     */
    public function setMimetype(string $mimetype): self
    {
        $this->mimetype = $mimetype;

        return $this;
    }

    /**
     * Method to get property Filename
     *
     * @return  string
     *
     * @since  3.5.13
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Method to set property filename
     *
     * @param  string  $filename
     *
     * @return  static  Return self to support chaining.
     *
     * @since  3.5.13
     */
    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * toCURLFile
     *
     * @return  \CURLFile
     *
     * @since  3.5.13
     */
    public function toCURLFile(): \CURLFile
    {
        return new \CURLFile($this->file, $this->mimetype, $this->filename);
    }
}
