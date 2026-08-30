<?php

namespace Carbon\Plausible\Service;

use Neos\Flow\Annotations as Flow;

#[Flow\Scope('singleton')]
class SplitTrackingCodeService
{
    /**
     * Resolve a tracking script tag or URL to the script and API endpoint URLs
     *
     * @param string $html Tracking script tag or URL
     * @return array{script:string,api:string}|null
     */
    public function split(?string $html = null): ?array
    {
        $src = (string) trim($html);

        if (empty($src)) {
            return null;
        }

        if (
            preg_match(
                '/<script\b[^>]*\bsrc=["\']([^"\']+)["\'][^>]*>.*?<\/script>/is',
                $html,
                $match
            )
        ) {
            $src = $match[1];
        }

        $url = parse_url($src);
        if ($url === false || !isset($url['host'])) {
            return null;
        }

        $scheme = $url['scheme'] ?? 'https';
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $domain = $scheme . '://' . $url['host'];

        if (isset($url['port'])) {
            $domain .= ':' . $url['port'];
        }

        $javascript = $url['path'] ?? '';

        if (isset($url['query'])) {
            $javascript .= '?' . $url['query'];
        }

        if (empty($domain) || empty($javascript)) {
            return null;
        }

        return [
            'script' => $domain . $javascript,
            'api' => $domain . '/api/event',
        ];
    }
}
