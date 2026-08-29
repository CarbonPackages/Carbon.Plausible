<?php

namespace Carbon\Plausible\Service;

use InvalidArgumentException;
use Neos\Flow\Annotations as Flow;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

#[Flow\Scope('singleton')]
class ProxyService
{
    public function proxy(string $url, ?ServerRequestInterface $request = null): string
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
            $options[CURLOPT_CUSTOMREQUEST] = $request->getMethod();
            $options[CURLOPT_HTTPHEADER] = $this->buildRequestHeaders($request);
            $options[CURLOPT_POSTFIELDS] = (string) $request->getBody();
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
    private function buildRequestHeaders(ServerRequestInterface $request): array
    {
        $headers = [];

        foreach (['Content-Type', 'Content-Encoding', 'User-Agent'] as $headerName) {
            $value = $request->getHeaderLine($headerName);
            if ($value !== '') {
                $headers[] = $headerName . ': ' . $value;
            }
        }

        $forwardedFor = $request->getHeaderLine('X-Forwarded-For');
        $remoteAddress = $request->getServerParams()['REMOTE_ADDR'] ?? null;
        if (is_string($remoteAddress) && $remoteAddress !== '') {
            $forwardedFor = $forwardedFor === '' ? $remoteAddress : $forwardedFor . ', ' . $remoteAddress;
        }
        if ($forwardedFor !== '') {
            $headers[] = 'X-Forwarded-For: ' . $forwardedFor;
        }

        return $headers;
    }
}
