<?php

namespace Carbon\Plausible\EelHelper;

use Neos\ContentRepository\Domain\Model\NodeInterface;
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
     * Get the boolean value of a key from the site properties, the site settings or the default settings
     *
     * @param array $defaultSettings
     * @param array $siteSettings
     * @param string $key
     * @param Nodeinterface|null $siteNode
     * @return bool
     */
    public function getBooleanValue(?array $defaultSettings, ?array $siteSettings, string $key, ?NodeInterface $siteNode = null): bool
    {
        return !!$this->getValue($defaultSettings, $siteSettings, $key, $siteNode);
    }

    /**
     * Get the value of a key from the site properties, the site settings or the default settings
     *
     * @param array $defaultSettings
     * @param array $siteSettings
     * @param string $key
     * @param Nodeinterface|null $siteNode
     * @return mixed
     */
    public function getValue(?array $defaultSettings, ?array $siteSettings, string $key, ?NodeInterface $siteNode = null)
    {
        $propertyName = 'plausible' . ucfirst($key);
        if ($siteNode !== null && $siteNode->hasProperty($propertyName)) {
            return $siteNode->getProperty($propertyName);
        }

        if (isset($siteSettings[$key])) {
            return $siteSettings[$key];
        }
        if (isset($defaultSettings[$key])) {
            return $defaultSettings[$key];
        }
        return null;
    }

    /**
     * Iterate over the given array and return a string with the key and value
     *
     * @param $variable
     * @return array
     */
    public function pageviewProps($variable): array
    {
        if (!is_array($variable)) {
            return [];
        }

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
    private function getKeyForPageviewProps(string $string): string
    {
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
