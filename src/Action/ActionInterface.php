<?php

namespace Cronical\Action;

use Cronical\Api\Representation\ScheduledActionRepresentation;
use Cronical\Api\Representation\ScheduledActionRunRepresentation;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\View\Renderer\PhpRenderer;

interface ActionInterface
{
    public function getLabel(): string;
    public function getGroupLabel(): string;
    public function getDescription(): string;

    public function formAddElements(Form $form, ScheduledActionRepresentation $scheduledAction): void;
    public function formAddInputFilters(InputFilterInterface $inputFilter, ScheduledActionRepresentation $scheduledAction): void;

    public function perform(ScheduledActionRunRepresentation $scheduledActionRun): void;

    public function onViewShow(PhpRenderer $view, ScheduledActionRepresentation $scheduledAction): void;
    public function onViewDetails(PhpRenderer $view, ScheduledActionRepresentation $scheduledAction): void;
}
