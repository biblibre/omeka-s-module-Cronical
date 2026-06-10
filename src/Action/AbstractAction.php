<?php

namespace Cronical\Action;

use Cronical\Api\Representation\ScheduledActionRepresentation;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\ServiceManager\SortableInterface;

abstract class AbstractAction implements ActionInterface, SortableInterface
{
    public function getSortableString(): string
    {
        return $this->getLabel();
    }

    public function formAddElements(Form $form, ScheduledActionRepresentation $scheduledAction): void
    {
    }

    public function formAddInputFilters(InputFilterInterface $inputFilter, ScheduledActionRepresentation $scheduledAction): void
    {
    }

    public function onViewShow(PhpRenderer $view, ScheduledActionRepresentation $scheduledAction): void
    {
    }

    public function onViewDetails(PhpRenderer $view, ScheduledActionRepresentation $scheduledAction): void
    {
    }
}
