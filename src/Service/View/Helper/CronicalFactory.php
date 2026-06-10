<?php

namespace Cronical\Service\View\Helper;

use Cronical\View\Helper\Cronical;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class CronicalFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $actionManager = $services->get('Cronical\ActionManager');
        $formElementManager = $services->get('FormElementManager');

        $helper = new Cronical($actionManager, $formElementManager);

        return $helper;
    }
}
