<?php

namespace Cronical\Action;

use Cronical\Api\Representation\ScheduledActionRepresentation;
use Cronical\Api\Representation\ScheduledActionRunRepresentation;
use Exception;
use Laminas\Form\Form;
use Laminas\Form\Fieldset;

class Unknown extends AbstractAction
{
    public function __construct(protected string $name)
    {
    }

    public function getLabel(): string
    {
        return sprintf('[Unknown: %s]', $this->name); // @translate
    }

    public function getGroupLabel(): string
    {
        return '[Unknown]'; // @translate
    }

    public function getDescription(): string
    {
        return '[Unknown]'; // @translate
    }

    public function formAddElements(Form $form, ScheduledActionRepresentation $scheduledAction): void
    {
        $this->formAddSettings($form->get('o:settings'), $scheduledAction->settings());
    }

    protected function formAddSettings(Fieldset $fieldset, array $settings): void
    {
        foreach ($settings as $key => $value) {
            $key = (string) $key;
            if (is_array($value)) {
                $fieldset->add([
                    'name' => $key,
                    'type' => '\Laminas\Form\Fieldset',
                ]);
                $this->formAddSettings($fieldset->get($key), $value);
            } else {
                $fieldset->add([
                    'name' => $key,
                    'type' => 'Laminas\Form\Element\Hidden',
                    'attributes' => [
                        'value' => (string) $value,
                    ],
                ]);
            }
        }
    }

    public function perform(ScheduledActionRunRepresentation $scheduledActionRun): void
    {
        throw new Exception(sprintf('Tried to execute unknown action: %s', $this->name));
    }
}
