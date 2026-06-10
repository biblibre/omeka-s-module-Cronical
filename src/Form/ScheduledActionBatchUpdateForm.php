<?php

namespace Cronical\Form;

use Laminas\Form\Form;

class ScheduledActionBatchUpdateForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'o:minute',
            'type' => 'Laminas\Form\Element\Text',
            'options' => [
                'label' => 'Minute', // @translate
                'info' => 'The minute at which the action will be run (0-59)', // @translate
            ],
            'attributes' => [
                'id' => 'minute',
            ],
        ]);

        $this->add([
            'name' => 'o:hour',
            'type' => 'Laminas\Form\Element\Text',
            'options' => [
                'label' => 'Hour', // @translate
                'info' => 'The hour at which the action will be run (0-23)', // @translate
            ],
            'attributes' => [
                'id' => 'hour',
            ],
        ]);

        $this->add([
            'name' => 'o:day_of_month',
            'type' => 'Laminas\Form\Element\Text',
            'options' => [
                'label' => 'Day of month', // @translate
                'info' => 'The day of month at which the action will be run. Can be any valid cron expression, for instance: "1", "1-10", "*/5", "1,11,21".', // @translate
            ],
            'attributes' => [
                'id' => 'day-of-month',
            ],
        ]);

        $this->add([
            'name' => 'o:month',
            'type' => 'Laminas\Form\Element\Text',
            'options' => [
                'label' => 'Month', // @translate
                'info' => 'The month at which the action will be run. Can be any valid cron expression, for instance: "1", "2-6", "*/2", "3,6,9".', // @translate
            ],
            'attributes' => [
                'id' => 'month',
            ],
        ]);

        $this->add([
            'name' => 'o:day_of_week',
            'type' => 'Laminas\Form\Element\Text',
            'options' => [
                'label' => 'Day of week', // @translate
                'info' => 'The day of week at which the action will be run. Can be any valid cron expression, for instance: "0", "1-5", "*/2", "0,6". 0 is Sunday.', // @translate
            ],
            'attributes' => [
                'id' => 'day-of-week',
            ],
        ]);

        $this->add([
            'name' => 'o:is_active',
            'type' => 'Laminas\Form\Element\Radio',
            'options' => [
                'label' => 'Active', // @translate
                'value_options' => [
                    '1' => 'Active', // @translate
                    '0' => 'Not active', // @translate
                    '' => '[No change]', // @translate
                ],
            ],
        ]);

        $this->add([
            'name' => 'o:run_history_size',
            'type' => 'Laminas\Form\Element\Text',
            'options' => [
                'label' => 'Run history size', // @translate
            ],
            'attributes' => [
                'id' => 'run-history-size',
            ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'o:is_active',
            'required' => false,
        ]);
    }
}
