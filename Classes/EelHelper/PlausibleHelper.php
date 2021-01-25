<?php

namespace Carbon\Plausible\EelHelper;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use JShrink\Minifier;

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
     * Get main domain from domain string
     * Remove protocol and trailing slash, and return the domain without subdomain
     *
     * @param string $domain
     * @return string
     */
    public function mainDomain(string $domain): string
    {
        // Remove protocol and trailing slash
        $number = preg_match('/\/\/([^\/]*)/', (string)$domain, $matches);
        if ($number) {
            $domain = $matches[1];
        } else {
            // Remove trailing slash
            $number = preg_match('/([^\/]*)/', (string)$domain, $matches);
            $domain = $matches[1];
        }

        // Get domain without subdomain
        $array = explode('.', $domain);
        $array = array_slice($array, -2);
        return implode('.', $array);
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
