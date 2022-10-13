<?php

declare(strict_types=1);

namespace Carbon\Plausible\Controller;

use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Component\SetHeaderComponent;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Flow\Mvc\Controller\ActionController;
use Psr\Log\LoggerInterface;
use function file_get_contents;
use function strlen;

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
            if (isset($config['output'])) {
                $this->logger->debug(
                    sprintf('Use cache "%s" for the url %s', $cacheIdentifier, $url),
                    LogEnvironment::fromMethodName(__METHOD__)
                );

                $this->setHeader($config);
                return $config['output'];
            }
            $this->logger->warning(
                sprintf('%s with the cache identifier "%s" has no output', $url, $cacheIdentifier),
                LogEnvironment::fromMethodName(__METHOD__)
            );
        }

        $response = $this->curl($url);
        $code = $response['code'];
        $this->setHeader($response);

        if ($code >= 400) {
            $this->logger->error(
                sprintf('%s returned code %s', $url, $code),
                LogEnvironment::fromMethodName(__METHOD__)
            );
            return '';
        }

        if (strlen($response['output'])) {
            // 60 * 60 * 6 = 21600 = 6 hours
            $this->cache->set($cacheIdentifier, $response, ['CarbonPlausible_Cache'], 21600);
            $this->logger->debug(
                sprintf('Cached %s as "%s" with code %s', $url, $cacheIdentifier, $code),
                LogEnvironment::fromMethodName(__METHOD__)
            );
        }

        return $response['output'];
    }

    /**
     * Proxies the POST request to the Plausible API
     *
     * @return string
     */
    public function apiEventAction(): string
    {
        $url = $this->backendUrl . '/api/event';
        $response = $this->curl($url, true);
        $code = $response['code'];
        $this->setHeader($response);

        if ($code >= 400) {
            $this->logger->error(
                sprintf('POST %s returned code %s', $url, $code),
                LogEnvironment::fromMethodName(__METHOD__)
            );
            return '';
        }
        return $response['output'];
    }


    /**
     * Get all headers from request
     *
     * @return array
     */
    private function getAllHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        } else {
            if (!is_array($_SERVER)) {
                return [];
            }
            $headers = [];

            $copy_server = [
                'CONTENT_TYPE'   => 'Content-Type',
                'CONTENT_LENGTH' => 'Content-Length',
                'CONTENT_MD5'    => 'Content-Md5',
            ];

            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) === 'HTTP_') {
                    $key = substr($key, 5);
                    if (!isset($copy_server[$key]) || !isset($_SERVER[$key])) {
                        $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                        $headers[$key] = $value;
                    }
                } elseif (isset($copy_server[$key])) {
                    $headers[$copy_server[$key]] = $value;
                }
            }

            return $headers;
        }
    }

    /**
     * Call Plausible
     *
     * @param string $url
     * @param boolean $apiCall
     * @return array
     */
    private function curl(string $url, bool $apiCall = false): array
    {
        $code = 400;
        $ch = curl_init($url);
        $headers = [];
        foreach ($this->getAllHeaders() as $key => $value) {
            if (!in_array($key, ['Host', 'Accept-Encoding', 'X-Forwarded-For', 'Client-IP'])) {
                $headers[$key] = $key . ': ' . $value;
            }
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $headers['X-Forwarded-For'] = 'X-Forwarded-For: ' . $ip;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($apiCall) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents('php://input'));
        }
        $output = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        curl_close($ch);

        return [
            'header' => [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Credentials' => 'true'
            ],
            'output' => $output,
            'contentType' => $contentType,
            'code' => $code
        ];
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
