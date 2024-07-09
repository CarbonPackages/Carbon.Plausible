<?php

declare(strict_types=1);

namespace Carbon\Plausible\Controller;

use Neos\ContentRepository\Domain\Factory\NodeFactory;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\ContentRepository\Domain\Repository\WorkspaceRepository;
use Neos\ContentRepository\Domain\Utility\NodePaths;
use Neos\ContentRepository\Security\Authorization\Privilege\Node\NodePrivilegeSubject;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Authorization\PrivilegeManagerInterface;
use Neos\Flow\Security\Context;
use Neos\Fusion\View\FusionView;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Neos\Domain\Service\SiteService;
use Neos\Neos\Security\Authorization\Privilege\NodeTreePrivilege;

/**
 * @Flow\Scope("singleton")
 */
class PlausibleController extends AbstractModuleController
{
    /**
     * @var FusionView
     */
    protected $view;

    /**
     * @Flow\Inject
     * @var SiteRepository
     */
    protected $siteRepository;

    /**
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * @Flow\Inject
     * @var WorkspaceRepository
     */
    protected $workspaceRepository;

    /**
     * @Flow\Inject
     * @var NodeFactory
     */
    protected $nodeFactory;

    /**
     * @var PrivilegeManagerInterface
     * @Flow\Inject
     */
    protected $privilegeManager;

    /**
     * @var array
     * @Flow\InjectConfiguration()
     */
    protected $configuration;


    /**
     * @var string
     */
    protected $defaultViewObjectName = FusionView::class;

    /**
     * @Flow\Inject
     * @var Context
     */
    protected $securityContext;

    /**
     * @var array
     */
    protected $viewFormatToObjectNameMap = [
        'html' => FusionView::class,
    ];

    /**
     * Renders the view
     */
    public function indexAction(): void
    {
        $this->view->assign('settings', $this->getSettings());
    }

    /**
     * Renders the statistic view
     */
    public function statsAction(): void
    {
        $this->view->assign('settings', $this->getSettings());
    }

    private function getSettings(): array {
        $configuration = $this->configuration;
        $siteConfiguration = $this->configuration['sites'] ?? [];
        if (isset($configuration['sites'])) {
            $configuration['sites'] = [];
        }

        $liveWorkspace = $this->workspaceRepository->findByIdentifier('live');
        $propertiesToGet = ['domain', 'sharedLink', 'outboundLinks', 'fileDownloads'];

        foreach ($this->siteRepository->findOnline() as $site) {
            $granted = false;
            $properties = [];

            $siteNodePath = NodePaths::addNodePathSegment(SiteService::SITES_ROOT_PATH, $site->getNodeName());
            $siteNodesInAllDimensions = $this->nodeDataRepository->findByPathWithoutReduce($siteNodePath, $liveWorkspace);

            foreach ($siteNodesInAllDimensions as $siteNodeData) {
                $siteNodeContext = $this->nodeFactory->createContextMatchingNodeData($siteNodeData);
                $siteNode = $this->nodeFactory->createFromNodeData($siteNodeData, $siteNodeContext);

                // if the node exists, check if the user is granted access to this node
                if ($this->privilegeManager->isGranted(NodeTreePrivilege::class, new NodePrivilegeSubject($siteNode))) {
                    foreach ($propertiesToGet as $key) {
                        $propertyName = 'plausible' . ucfirst($key);

                        if ($siteNode->hasProperty($propertyName)) {
                            $properties[$key] = $siteNode->getProperty($propertyName);
                        }
                    }

                    $granted = true;
                    break;
                }
            }

             // if no siteNode is accessible ignore this site
             if (!$granted) {
                continue;
            }

            $nodeName = $site->getNodeName();
            $configuration['sites'][$nodeName] = $siteConfiguration[$nodeName] ?? [];
            $configuration['sites'][$nodeName]['name'] = $site->getName();
            foreach ($properties as $key => $value) {
                $configuration['sites'][$nodeName][$key] = $value;
            }
        }
        return $configuration;
    }

    /**
     * Renders the screenshot view
     */
    public function screenshotAction(): void
    {
        if (!$this->securityContext->hasRole('Neos.Neos:Administrator')) {
            $this->redirect('index');
        }
    }
}
