<?php

namespace Cronical\View\Helper;

use Cronical\Action\Manager as ActionManager;
use Cronical\Action\ActionInterface;
use Cronical\Form\Element\ActionSelect;
use Laminas\Form\Factory;
use Laminas\Form\FormElementManager;
use Laminas\View\Helper\AbstractHelper;

class Cronical extends AbstractHelper
{
    public function __construct(protected ActionManager $actionManager, protected FormElementManager $formElementManager)
    {
    }

    public function getAction(string $action): ActionInterface
    {
        return $this->actionManager->get($action);
    }

    public function getActionSelect(array $spec = []): ActionSelect
    {
        $spec['type'] = ActionSelect::class;
        $factory = new Factory($this->formElementManager);
        $element = $factory->createElement($spec);

        return $element;
    }
}
