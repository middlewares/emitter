<?php
declare(strict_types = 1);

namespace Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class Emitter implements MiddlewareInterface
{
    private $maxBufferLength = 8192;

    public function maxBufferLength(int $maxBufferLength): self
    {
        $this->maxBufferLength = $maxBufferLength;

        return $this;
    }

    /**
     * Process a request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (headers_sent()) {
            throw new RuntimeException('Unable to emit response; headers already sent');
        }

        $this->sendStatus($response);
        $this->sendHeaders($response);

        $range = self::parseContentRange($response->getHeaderLine('Content-Range'));

        if (is_array($range) && $range[0] === 'bytes') {
            $this->sendStreamRange($range, $response);
        } else {
            $this->sendStream($response->getBody());
        }

        return $response;
    }

    /**
     * Sends the Response status line.
     */
    private function sendStatus(ResponseInterface $response)
    {
        $version = $response->getProtocolVersion();
        $statusCode = $response->getStatusCode();
        $reasonPhrase = $response->getReasonPhrase();

        header(sprintf('HTTP/%s %d%s', $version, $statusCode, $reasonPhrase), true, $statusCode);
    }

    /**
     * Sends all Response headers.
     */
    private function sendHeaders(ResponseInterface $response)
    {
        foreach ($response->getHeaders() as $name => $values) {
            $this->sendHeader($name, $values);
        }
    }

    /**
     * Sends one Response header.
     */
    private function sendHeader(string $name, array $values)
    {
        $name = str_replace('-', ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '-', $name);

        foreach ($values as $value) {
            header("{$name}: {$value}", false);
        }
    }

    /**
     * Streams the Response body 8192 bytes at a time via `echo`.
     */
    private function sendStream(StreamInterface $stream)
    {
        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        if (!$stream->isReadable()) {
            echo $stream;
            return;
        }

        while (!$stream->eof()) {
            echo $stream->read($this->maxBufferLength);
        }
    }

    /**
     * Emit a range of the message body.
     */
    private function sendStreamRange(array $range, StreamInterface $stream): void
    {
        list($unit, $first, $last, $length) = $range;
        $length = $last - $first + 1;

        if ($stream->isSeekable()) {
            $stream->seek($first);
            $first = 0;
        }

        if (!$stream->isReadable()) {
            echo substr($stream->getContents(), $first, $length);
            return;
        }

        $remaining = $length;

        while ($remaining >= $this->maxBufferLength && !$stream->eof()) {
            $contents = $stream->read($this->maxBufferLength);
            $remaining -= strlen($contents);
            echo $contents;
        }

        if ($remaining > 0 && !$stream->eof()) {
            echo $stream->read($remaining);
        }
    }

    /**
     * Parse content-range header
     * http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.16
     *
     * @return null|array [unit, first, last, length];
     */
    private static function parseContentRange(string $header): ?array
    {
        if (!preg_match('/(?P<unit>[\w]+)\s+(?P<first>\d+)-(?P<last>\d+)\/(?P<length>\d+|\*)/', $header, $matches)) {
            return null;
        }

        return [
            $matches['unit'],
            (int) $matches['first'],
            (int) $matches['last'],
            $matches['length'] === '*' ? '*' : (int) $matches['length'],
        ];
    }
}
