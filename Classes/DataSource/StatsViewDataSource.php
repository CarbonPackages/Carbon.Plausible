<?php

namespace Carbon\Plausible\DataSource;

use Carbon\Plausible\Service\SiteService;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Service\DataSource\AbstractDataSource;

class StatsViewDataSource extends AbstractDataSource
{
    protected static $identifier = 'carbon-plausible-statsview';

    #[Flow\Inject]
    protected SiteService $siteService;

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
        $link = $siteNode ? $siteNode->getProperty('plausibleSharedLink') : null;

        if (!empty($link)) {
            return [
                'uri' => $link
            ];
        }

        $siteName = $this->siteService->getName($node);
        return [
            'uri' => $this->sitesConfig[$siteName]['sharedLink'] ?? $this->defaultConfig['sharedLink'] ?? null
        ];
    }
}
