<?php

namespace Carbon\Plausible\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;

#[Flow\Scope('singleton')]
class SiteService
{
    #[Flow\Inject]
    protected ObjectManagerInterface $objectManager;

    /**
     * @param mixed $node Neos 8 NodeInterface or Neos 9 Node (or null)
     */
    public function getName(mixed $node): ?string
    {
        if (!\is_object($node)) {
            return null;
        }

        // Neos 9: event-sourced Node has no getPath(); derive via the subgraph.
        if (\class_exists(\Neos\ContentRepository\Core\Projection\ContentGraph\Node::class)) {
            return $this->resolveFromContentGraph($node);
        }

        // Neos 8: NodeInterface::getPath() -> "/sites/<brand>/...", segment [2].
        if (\method_exists($node, 'getPath')) {
            try {
                $segments = \explode('/', (string) $node->getPath());
                return $segments[2] ?? null;
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    private function resolveFromContentGraph(object $node): ?string
    {
        try {
            $registry = $this->objectManager->get(\Neos\ContentRepositoryRegistry\ContentRepositoryRegistry::class);
            $subgraph = $registry->subgraphForNode($node);
            // getParts() excludes the Sites root; [0] is the site node name (= brand segment).
            $parts = $subgraph->retrieveNodePath($node->aggregateId)->getParts();
            return isset($parts[0]) ? $parts[0]->value : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
