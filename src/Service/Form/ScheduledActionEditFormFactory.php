<?php

namespace Cronical\Service\Form;

use Cronical\Form\ScheduledActionEditForm;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ScheduledActionEditFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $form = new ScheduledActionEditForm(null, $options ?? []);

        return $form;
    }
}
