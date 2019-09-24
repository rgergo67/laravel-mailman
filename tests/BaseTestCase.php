<?php

namespace rgergo67\LaravelMailman\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use rgergo67\LaravelMailman\Mailman;
use rgergo67\LaravelMailman\Facades\Mailman as MailmanFacade;
use rgergo67\LaravelMailman\MailmanServiceProvider;

abstract class BaseTestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [MailmanServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return ['Mailman' => MailmanFacade::class];
    }

    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getMailman($responses)
    {
        $output = [];
        foreach ($responses as $r) {
            $output[] = new Response($r[0], [], $r[1]);
        }
        $mock = new MockHandler($output);
        $handler = HandlerStack::create($mock);
        $client = new Client([
            'handler'=>$handler, 'base_uri' => 'http://mock.mailman.org', ]);
        return new Mailman($client);
    }
}
