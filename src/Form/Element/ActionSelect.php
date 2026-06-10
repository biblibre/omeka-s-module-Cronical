<?php

namespace Cronical\Form\Element;

use Cronical\Action\Manager as ActionManager;
use Laminas\Form\Element\Select;
use Laminas\I18n\Translator\TranslatorInterface;

class ActionSelect extends Select
{
    protected TranslatorInterface $translator;
    protected ActionManager $actionManager;

    public function getValueOptions(): array
    {
        $valueOptions = [];

        $actionNames = $this->actionManager->getRegisteredNames(true);
        foreach ($actionNames as $actionName) {
            $action = $this->actionManager->get($actionName);
            $groupLabel = $action->getGroupLabel();
            $label = $action->getLabel();
            $description = $action->getDescription();
            $valueOptions[$actionName] = sprintf(
                '%s: %s (%s)',
                $this->translator->translate($groupLabel),
                $this->translator->translate($label),
                $this->translator->translate($description)
            );
        }

        return $valueOptions;
    }

    public function getInputSpecification(): array
    {
        $disabled = $this->getAttribute('disabled') ?? false;

        return [
            'name' => $this->getName(),
            'required' => !$disabled,
            'allow_empty' => false,
        ];
    }

    public function setActionManager(ActionManager $actionManager): void
    {
        $this->actionManager = $actionManager;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }
}
