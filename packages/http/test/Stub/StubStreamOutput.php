<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Test\Stub;

use Psr\Http\Message\ResponseInterface;
use Windwalker\Http\Output\StreamOutput;
use Windwalker\Http\Response\Response;

/**
 * The StubStreamOutput class.
 *
 * @since  3.0
 */
class StubStreamOutput extends StreamOutput
{
    /**
     * Property message.
     *
     * @var  Response
     */
    public $message;

    /**
     * Property status.
     *
     * @var  integer
     */
    public $status;

    /**
     * Property others.
     *
     * @var  array
     */
    public $others = [];

    /**
     * Property output.
     *
     * @var  string
     */
    public $output;

    /**
     * Property waiting.
     *
     * @var  int
     */
    public $waiting;

    /**
     * MockOutput constructor.
     */
    public function __construct()
    {
        $this->message = new Response();
    }

    /**
     * header
     *
     * @param string  $string
     * @param bool    $replace
     * @param integer $code
     *
     * @return  static
     */
    public function header($string, $replace = true, $code = null): static
    {
        if (strpos($string, ':') !== false) {
            [$header, $value] = explode(': ', $string, 2);

            if ($replace) {
                $this->message = $this->message->withHeader($header, $value);
            } else {
                $this->message = $this->message->withAddedHeader($header, $value);
            }
        } elseif (strpos($string, 'HTTP') === 0) {
            $this->status = $string;
        } else {
            $this->others[] = $string;
        }

        return $this;
    }

    /**
     * sendBody
     *
     * @param  ResponseInterface  $response
     *
     * @return  void
     */
    public function sendBody(ResponseInterface $response):void
    {
        ob_start();

        parent::sendBody($response);

        $this->output = ob_get_clean();
    }

    /**
     * checkHeaderSent
     *
     * @param string $filename
     * @param int    $linenum
     *
     * @return bool
     */
    public function headersSent(&$filename = null, &$linenum = null): bool
    {
        return false;
    }

    /**
     * delay
     *
     * @return  void
     */
    public function delay(): void
    {
        if ($this->delay === null) {
            return;
        }

        $this->waiting += $this->delay;
    }
}
