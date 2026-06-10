<?php

namespace Cronical\Action\Initializer;

use Cronical\Action\AbstractJobDispatchAction;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;

class JobDispatch implements InitializerInterface
{
    public function __invoke(ContainerInterface $container, $action)
    {
        if ($action instanceof AbstractJobDispatchAction) {
            $action->setJobDispatcher($container->get('Omeka\Job\Dispatcher'));
            $action->setEntityManager($container->get('Omeka\EntityManager'));
        }
    }
}
