<?php

namespace Cronical\Mvc\Controller\Plugin;

use Cronical\Action\Manager as ActionManager;
use Cronical\Action\ActionInterface;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

class Cronical extends AbstractPlugin
{
    public function __construct(protected ActionManager $actionManager)
    {
    }

    public function getAction(string $action): ActionInterface
    {
        return $this->actionManager->get($action);
    }
}
