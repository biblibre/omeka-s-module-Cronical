<?php

namespace Cronical\Form;

use Laminas\Form\Form;

class ScheduledActionAddForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'o:action',
            'type' => 'Cronical\Form\Element\ActionSelect',
            'options' => [
                'label' => 'Action', // @translate
            ],
            'attributes' => [
                'class' => 'chosen-select',
                'id' => 'action',
                'required' => true,
            ],
        ]);
    }
}
