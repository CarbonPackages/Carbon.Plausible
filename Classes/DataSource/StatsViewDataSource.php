<?php

namespace Carbon\Plausible\DataSource;

use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Service\DataSource\AbstractDataSource;

class StatsViewDataSource extends AbstractDataSource
{
    protected static $identifier = 'carbon-plausible-statsview';

    #[Flow\InjectConfiguration('default')]
    protected array|null $defaultConfig;

    #[Flow\InjectConfiguration('sites')]
    protected array|null $sitesConfig;

    /**
     * @param mixed $node The node that is currently edited
     * @param array $arguments Additional arguments (key / value)
     * @return array{uri:string}
     */
    public function getData(mixed $node = null, array $arguments = [])
    {
        if (empty($node)) {
            return [
                'uri' => null
            ];
        }
        $flowQuery = new FlowQuery([$node]);
        $siteNode = $flowQuery->closest('[instanceof Carbon.Plausible:Mixin.PlausibleProperties]')->get(0);
        $link = $siteNode?->getProperty('plausibleTrackingCode') ? $siteNode->getProperty('plausibleSharedLink') : null;

        if (!empty($link)) {
            return [
                'uri' => $link
            ];
        }

        $siteName = $this->getSiteName($node);
        return [
            'uri' => $this->getSharedLinkForSite($siteName)
        ];
    }

    private function getSiteName(mixed $node): ?string
    {
        if (!\is_object($node)) {
            return null;
        }

        // Neos 9
        if (\class_exists(\Neos\ContentRepository\Core\Projection\ContentGraph\Node::class)) {
            return (string) $node->name ?: null;
        }

        // Neos 8
        if (\method_exists($node, 'getName')) {
            return (string) $node->getName() ?: null;
        }

        return null;
    }

    private function getSharedLinkForSite(?string $siteName = null): ?string
    {
        if (!empty($siteName) && !empty($this->sitesConfig[$siteName]['sharedLink'])) {
            return $this->sitesConfig[$siteName]['sharedLink'];
        }

        return $this->defaultConfig['sharedLink'] ?? null;
    }
}
