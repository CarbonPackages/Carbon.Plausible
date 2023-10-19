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
     * Get the value of a key from the site settings or the default settings
     *
     * @param array $defaultSettings
     * @param array $siteSettings
     * @param string $key
     * @return bool
     */
    public function getBooleanValue(array $defaultSettings, array $siteSettings, string $key): bool
    {
        return !!$this->getValue($defaultSettings, $siteSettings, $key);
    }

    /**
     * Get the value of a key from the site settings or the default settings
     *
     * @param array $defaultSettings
     * @param array $siteSettings
     * @param string $key
     * @return mixed
     */
    public function getValue(array $defaultSettings, array $siteSettings, string $key)
    {
        if (isset($siteSettings[$key])) {
            return $siteSettings[$key];
        }
        if (isset($defaultSettings[$key])) {
            return $defaultSettings[$key];
        }
        return false;
    }

    /**
     * Iterate over the given array and return a string with the key and value
     *
     * @param array $variable
     * @return array
     */
    public function pageviewProps(array $variable): array {
        $result = [];
        foreach ($variable as $key => $value) {
            $key = $this->getKeyForPageviewProps($key);
            $value = $this->toString($value);
            $result[$key] = $value;
        }
        return array_unique($result);
    }

    /**
     * Convert the given key to a string
     *
     * @param string $string
     * @return string
     */
    private function getKeyForPageviewProps(string $string): string {
        $separator = '_';
        $string = strtolower(
            preg_replace(
                '/([a-zA-Z])(?=[A-Z])/',
                '$1' . $separator,
                $string
            )
        );
        return 'event-' . $string;
    }

    /**
     * Convert the given value to a string
     *
     * @param mixed $value The value to convert (must be convertible to string)
     * @return string The string value
     */
    private function toString($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_array($value)) {
            return implode(',', $value);
        }
        return (string)$value;
    }


    //  reverseProxy = ${Type.isBoolean(this.siteSettings.reverseProxy) ? this.siteSettings.reverseProxy : this.defaultSettings.reverseProxy}

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
