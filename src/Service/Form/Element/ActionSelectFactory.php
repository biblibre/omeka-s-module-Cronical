<?php

namespace Cronical\Service\Form\Element;

use Cronical\Form\Element\ActionSelect;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ActionSelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $actionManager = $services->get('Cronical\ActionManager');
        $translator = $services->get('MvcTranslator');

        $element = new ActionSelect;
        $element->setActionManager($actionManager);
        $element->setTranslator($translator);

        return $element;
    }
}
