<?php

namespace Cronical\Service\Mvc\Controller\Plugin;

use Cronical\Mvc\Controller\Plugin\Cronical;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class CronicalFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $actionManager = $services->get('Cronical\ActionManager');

        $plugin = new Cronical($actionManager);

        return $plugin;
    }
}
