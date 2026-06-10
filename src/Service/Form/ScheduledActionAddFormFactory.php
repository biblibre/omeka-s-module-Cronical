<?php

namespace Cronical\Service\Form;

use Cronical\Form\ScheduledActionAddForm;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ScheduledActionAddFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $form = new ScheduledActionAddForm(null, $options ?? []);

        return $form;
    }
}
