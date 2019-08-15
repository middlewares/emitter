<?php

namespace Middlewares\Tests;

use Middlewares\Emitter;
use Middlewares\Utils\Dispatcher;
use Middlewares\Utils\Factory;
use PHPUnit\Framework\TestCase;

class EmitterTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testEmitter()
    {
        $request = Factory::createServerRequest('GET', '/');

        ob_start();
        $response = Dispatcher::run([
            new Emitter(),
            function () {
                echo 'Hello world';
            },
        ], $request);
        $result = ob_get_clean();

        $this->assertSame('Hello world', $result);
    }
}
