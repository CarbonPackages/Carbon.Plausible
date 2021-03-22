<?php

declare(strict_types=1);

namespace Carbon\Plausible\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Context;
use Neos\Fusion\View\FusionView;
use Neos\Neos\Controller\Module\AbstractModuleController;


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
    }

    /**
     * Renders the statistic view
     */
    public function statsAction(): void
    {
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
