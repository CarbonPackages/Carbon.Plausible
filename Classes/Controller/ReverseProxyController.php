<?php

declare(strict_types=1);

namespace Carbon\Plausible\Controller;

use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Client\Browser;
use Neos\Flow\Http\Client\CurlEngine;
use Neos\Flow\Http\Component\SetHeaderComponent;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Flow\Mvc\Controller\ActionController;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

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
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct()
    {
        $this->get = new Browser();
        $this->get->setRequestEngine(new CurlEngine());
        $this->get->addAutomaticRequestHeader(
            'Content-Type',
            'application/javascript; charset=utf-8;'
        );

        $this->post = new Browser();
        $this->post->setRequestEngine(new CurlEngine());
        $this->post->addAutomaticRequestHeader(
            'Content-Type',
            'application/json;  charset=utf-8;'
        );
        $this->post->addAutomaticRequestHeader(
            'X-Forwarded-For',
            $this->getClientIP()
        );
    }

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
     * @return string
     */
    public function fileAction(string $filename): string
    {
        $part = $filename == 'default' ? '' : $filename;
        $cacheIdentifier = implode('_', array_filter(explode('.', $filename)));
        $url = $this->backendUrl . '/js/plausible' . $part . '.js';

        if ($this->cache->has($cacheIdentifier)) {
            $config = $this->cache->get($cacheIdentifier);
            $this->setHeader($config);
            return $config['output'];
        }

        $response = $this->get->request($url);
        $config = $this->getHeader($response);
        $this->setHeader($config);
        $config['output'] = $this->outputContent($response);

        if (\strlen($config['output'])) {
            // 60 * 60 * 6 = 21600 = 6 hours
            $this->cache->set($cacheIdentifier, $config, ['CarbonPlausible_Cache'], 21600);
            $this->logger->debug(
                "Cached $url as \"$cacheIdentifier\"",
                LogEnvironment::fromMethodName(__METHOD__)
            );
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
        $response = $this->post->request(
            $url,
            'POST',
            [],
            [],
            [],
            \file_get_contents('php://input')
        );
        $this->setHeader($this->getHeader($response));
        return $this->outputContent($response);
    }

    /**
     * Get IP address of the client
     *
     * @return string
     */
    private function getClientIP(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Take the response, get status code and output content
     *
     * @param ResponseInterface $response
     * @return string
     */
    private function outputContent(ResponseInterface $response): string
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
     * @param ResponseInterface $response
     * @return array
     */
    private function getHeader(ResponseInterface $response): array
    {
        $contentType = $response->getHeader('Content-Type');
        $cacheControl = $response->getHeader('cache-control');
        $age = $response->getHeader('age');

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
            // If Neos 5.3 is no more supported, use the following line instead
            // $this->response->setHttpHeader($key, $value);
            $this->response->setComponentParameter(SetHeaderComponent::class, $key, $value);
        }
    }
}
