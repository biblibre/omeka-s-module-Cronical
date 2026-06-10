<?php

namespace Cronical\Action;

use Cronical\Api\Representation\ScheduledActionRepresentation;
use Cronical\Api\Representation\ScheduledActionRunRepresentation;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Log\LoggerAwareInterface;
use Laminas\Log\LoggerAwareTrait;
use Laminas\Log\Logger;
use Laminas\View\Renderer\PhpRenderer;

class Heartbeat extends AbstractAction implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const LOG_LEVELS = [
        Logger::DEBUG => 'Debug', // @translate
        Logger::INFO => 'Info', // @translate
        Logger::NOTICE => 'Notice', // @translate
        Logger::WARN => 'Warning', // @translate
        Logger::ERR => 'Error', // @translate
        Logger::ALERT => 'Alert', // @translate
        Logger::EMERG => 'Emergency', // @translate
    ];

    public function getLabel(): string
    {
        return 'Heartbeat'; // @translate
    }

    public function getGroupLabel(): string
    {
        return 'Cronical';
    }

    public function getDescription(): string
    {
        return 'Write a single line to logs to notify Cronical activity'; // @translate
    }

    public function formAddElements(Form $form, ScheduledActionRepresentation $scheduledAction): void
    {
        $form->get('o:settings')->add([
            'name' => 'log_level',
            'type' => 'Laminas\Form\Element\Select',
            'options' => [
                'label' => 'Log level', // @translate
                'value_options' => self::LOG_LEVELS,
            ],
        ]);
    }

    public function formAddInputFilters(InputFilterInterface $inputFilter, ScheduledActionRepresentation $scheduledAction): void
    {
        $inputFilter->get('o:settings')->add([
            'name' => 'log_level',
            'required' => false,
            'filters' => [
                ['name' => 'Laminas\Filter\ToInt'],
            ],
        ]);
    }

    public function perform(ScheduledActionRunRepresentation $scheduledActionRun): void
    {

        $this->logger->log(
            intval($scheduledActionRun->setting('log_level', Logger::DEBUG)),
            'Cronical: Heartbeat'
        );
    }

    public function onViewShow(PhpRenderer $view, ScheduledActionRepresentation $scheduledAction): void
    {
        echo $view->partial('cronical/common/action/heartbeat/show', ['scheduledAction' => $scheduledAction]);
    }

    public function onViewDetails(PhpRenderer $view, ScheduledActionRepresentation $scheduledAction): void
    {
        echo $view->partial('cronical/common/action/heartbeat/details', ['scheduledAction' => $scheduledAction]);
    }

    public function getLogLevelDescription(int $level): string
    {
        return self::LOG_LEVELS[$level];
    }
}
