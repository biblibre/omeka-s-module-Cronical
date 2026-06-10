<?php

namespace Cronical\Action\Initializer;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;
use Laminas\Log\LoggerAwareInterface;

class Logger implements InitializerInterface
{
    public function __invoke(ContainerInterface $container, $action)
    {
        if ($action instanceof LoggerAwareInterface) {
            $action->setLogger($container->get('Omeka\Logger'));
        }
    }
}
