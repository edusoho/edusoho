<?php

declare(strict_types=1);

namespace Http\Adapter\Guzzle6;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * HTTP Adapter for Guzzle 6.
 *
 * @author David de Boer <david@ddeboer.nl>
 */
final class Client implements HttpClient, HttpAsyncClient
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(?ClientInterface $client = null)
    {
        if (!$client) {
            $client = self::buildClient();
        }

        $this->client = $client;
    }

    /**
     * Factory method to create the Guzzle 6 adapter with custom Guzzle configuration.
     */
    public static function createWithConfig(array $config): Client
    {
        return new self(self::buildClient($config));
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $promise = $this->sendAsyncRequest($request);

        return $promise->wait();
    }

    /**
     * {@inheritdoc}
     */
    public function sendAsyncRequest(RequestInterface $request)
    {
        $promise = $this->client->sendAsync($request);

        return new Promise($promise, $request);
    }

    /**
     * Build the Guzzle client instance.
     */
    private static function buildClient(array $config = []): GuzzleClient
    {
        $handlerStack = new HandlerStack(\GuzzleHttp\choose_handler());
        $handlerStack->push(Middleware::prepareBody(), 'prepare_body');
        $config = array_merge(['handler' => $handlerStack], $config);

        return new GuzzleClient($config);
    }
}
