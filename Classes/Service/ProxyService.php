<?php

namespace Carbon\Plausible\Service;

use InvalidArgumentException;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

#[Flow\Scope('singleton')]
class ProxyService
{
    public function proxy(string $url, ?ActionRequest $request = null): string
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('The cURL PHP extension is required to proxy URLs.');
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException('Only HTTP and HTTPS URLs can be proxied.');
        }

        $handle = curl_init($url);
        if ($handle === false) {
            throw new RuntimeException(sprintf('Unable to initialize proxy request for "%s".', $url));
        }

        $options = [
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ];

        if ($request !== null) {
            $httpRequest = $request->getHttpRequest();
            $options[CURLOPT_CUSTOMREQUEST] = $httpRequest->getMethod();
            $options[CURLOPT_HTTPHEADER] = $this->buildRequestHeaders($request);
            $options[CURLOPT_POSTFIELDS] = (string) $httpRequest->getBody();
        }

        curl_setopt_array($handle, $options);

        try {
            $response = curl_exec($handle);
            if ($response === false) {
                throw new RuntimeException(
                    sprintf('Proxy request to "%s" failed: %s', $url, curl_error($handle))
                );
            }

            $statusCode = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
            if ($statusCode >= 400) {
                throw new RuntimeException(
                    sprintf('Proxy request to "%s" returned HTTP status %d.', $url, $statusCode)
                );
            }

            return $response;
        } finally {
        }
    }

    /**
     * Forward only headers required by the Plausible event API.
     *
     * @return list<string>
     */
    private function buildRequestHeaders(ActionRequest $request): array
    {
        $httpRequest = $request->getHttpRequest();
        $headers = [];

        foreach (['Content-Type', 'Content-Encoding', 'User-Agent'] as $headerName) {
            $value = $httpRequest->getHeaderLine($headerName);
            if ($value !== '') {
                $headers[] = $headerName . ': ' . $value;
            }
        }

        $ipAdress = $this->userIP($httpRequest);
        if (!empty($ipAdress)) {
            $headers[] = 'X-Forwarded-For: ' . $ipAdress;
        }

        return $headers;
    }

    private function userIP(ServerRequestInterface $request): ?string
    {
        if ($request->hasHeader('X-Forwarded-For')) {
            $userIp = $request->getHeaderLine('X-Forwarded-For');
            // This can be a comma-separated list of IP addresses (e.g., “Client IP, Proxy1 IP”)
            return explode(',', $userIp)[0];
        }

        if ($request->hasHeader('CF-Connecting-IP')) {
            // Cloudflare
            return (string) $request->getHeaderLine('CF-Connecting-IP');
        }

        $serverParams = $request->getServerParams();
        return isset($serverParams['REMOTE_ADDR']) ? (string) $serverParams['REMOTE_ADDR'] : null;
    }
}
