<?php

declare(strict_types=1);

namespace Carbon\Plausible\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;


/**
 * @Flow\Scope("singleton")
 */
class ReverseProxyController extends ActionController
{
    /**
     * @var VariableFrontend
     * @Flow\Inject
     */
    protected $cache;

    /**
     * Set URL of Plausible Analytics
     *
     * @var string
     */
    protected $backendUrl = "https://plausible.io";

    /**
     * Proxies the GET request to the Plausible Analytics scripts
     *
     * @param string $filename
     * @return void
     */
    public function fileAction(string $filename)
    {
        $part = $filename == 'default' ? '' : $filename;
        $cacheIdentifier = implode('_', array_filter(explode('.', $filename)));
        $url = $this->backendUrl . '/js/plausible' . $part . '.js';

        if ($this->cache->has($cacheIdentifier)) {
            $config = $this->cache->get($cacheIdentifier);
            $this->setHeader($config);
            return $config['output'];
        }

        $client = new Client();
        $request = new Request(
            "GET",
            $url,
            [
                "Content-Type" => "application/javascript; charset=utf-8"
            ]
        );
        $response = $client->send($request);
        $config = $this->getHeader($response);
        $this->setHeader($config);
        $config['output'] = $this->outputContent($response);

        if (\strlen($config['output'])) {
            // 60 * 60 * 6 = 21600 = 6 hours
            $this->cache->set($cacheIdentifier, $config, ['CarbonPlausible_Cache'], 21600);
        }
        return $config['output'];
    }

    /**
     * Proxies the POST request to the Plausible API
     *
     * @return string
     */
    public function apiEventAction(): string
    {
        $url = $this->backendUrl . '/api/event';
        $client = new Client();
        $request = new Request(
            "POST",
            $url,
            [
                "Content-Type" => "application/json; charset=utf-8"
            ],
            \file_get_contents('php://input')
        );

        $response = $client->send($request);
        $this->setHeader($this->getHeader($response));
        return $this->outputContent($response);
    }


    /**
     * Take the response, get status code and output content
     *
     * @param Response $response
     * @return string
     */
    private function outputContent(Response $response): string
    {
        $statusCode = $response->getStatusCode();
        $this->response->setStatusCode($statusCode);
        if ($statusCode >= 400) {
            return '';
        }
        $content = $response->getBody()->getContents();
        return $content;
    }

    /**
     * Get header from the request response and return the configuration
     *
     * @param Response $response
     * @return array
     */
    private function getHeader(Response $response): array
    {
        $contentType = $response->getHeader('Content-Type');
        $cacheControl = $response->getHeader('cache-control');
        $age = $response->getHeader('age');

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $config = [
            'header' => [
                'X-Forwarded-For' => $ip
            ]
        ];
        if (\count($contentType)) {
            $config['contentType'] = $contentType[0] . '; charset=utf-8;';
        }
        if (\count($age)) {
            $config['header']['age'] = $age[0];
        }
        if (\count($cacheControl)) {
            $config['header']['cache-control'] = $cacheControl[0];
        }
        return $config;
    }

    /**
     * Set the header to the response
     *
     * @param array $config
     * @return void
     */
    private function setHeader(array $config): void
    {
        if (isset($config['contentType'])) {
            $this->response->setContentType($config['contentType']);
        }
        foreach ($config['header'] as $key => $value) {
            $this->response->setHttpHeader($key, $value);
        }
    }
}
