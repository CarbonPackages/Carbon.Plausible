<?php

namespace Carbon\Plausible\EelHelper;

use JShrink\Minifier;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Helper\RequestInformationHelper;
use Psr\Http\Message\ServerRequestInterface;


/**
 * @Flow\Proxy(false)
 */
class PlausibleHelper implements ProtectedContextAwareInterface
{

    /**
     * Minimze JavaScript
     *
     * @param string $javascript
     * @return string
     */
    public function minifyJS(string $javascript): string
    {
        return Minifier::minify($javascript);
    }

    /**
     * Return domain without protocol and trailing slash
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    public function getDomain(ServerRequestInterface $request): string
    {
        $domain = (string)RequestInformationHelper::generateBaseUri($request);
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
     * Checks if the requested domain fits into the configured domain
     *
     * @param ServerRequestInterface $request
     * @param string $configuredDomain
     * @return bool
     */
    public function checkDomain(ServerRequestInterface $request, string $configuredDomain): bool
    {
        $domain = $this->getDomain($request);
        $slice = count(explode('.', $configuredDomain)) * -1;

        // Get domain in the same format as configured ($configuredDomain)
        $array = explode('.', $domain);
        $array = array_slice($array, $slice);
        return implode('.', $array) === $configuredDomain;
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
