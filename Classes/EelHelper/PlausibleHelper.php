<?php

namespace Carbon\Plausible\EelHelper;

use Carbon\Plausible\Service\ProxyService;
use Carbon\Plausible\Service\SplitTrackingCodeService;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Helper\RequestInformationHelper;
use Psr\Http\Message\ServerRequestInterface;

class PlausibleHelper implements ProtectedContextAwareInterface
{
    #[Flow\Inject]
    protected ProxyService $proxyService;

    #[Flow\Inject]
    protected SplitTrackingCodeService $splitTrackingCodeService;

    public function proxyUrl(string $url, ?ServerRequestInterface $request = null): string
    {
        return $this->proxyService->proxy($url, $request);
    }

    /**
     * Resolve a tracking script tag or URL to the script and API endpoint URLs
     *
     * @param string $html Tracking script tag or URL
     * @return array{script:string,api:string}|null
     */
    public function splitTrackingCode(string $html): ?array
    {
        return $this->splitTrackingCodeService->split($html);
    }

    /**
     * Return domain without protocol and trailing slash
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    public function getDomain(ServerRequestInterface $request): string
    {
        $domain = (string) RequestInformationHelper::generateBaseUri($request);
        // Remove protocol and trailing slash
        $number = preg_match('/\/\/([^\/]*)/', $domain, $matches);
        if ($number) {
            return $matches[1];
        }
        // Remove trailing slash
        $number = preg_match('/([^\/]*)/', $domain, $matches);
        return $matches[1];
    }

    /**
     * All methods are considered safe
     *
     * @param string $methodName The name of the method
     *
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
