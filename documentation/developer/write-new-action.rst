Write a new action
==================

If you are a module developer and you want to provide schedulable actions for
your module, follow this guide.

Module config
-------------

In ``config/module.config.php``, add the following section::

    'cronical_actions' => [
        // You can declare factories too
        'invokables' => [
            'MyModule\Action\MyAction' => \MyModule\Action\MyAction::class,
        ],
    ],


The action class
----------------

If the action's only task is to start a background job, create
``src/Action/MyAction.php`` like this:

.. code-block:: php

    namespace MyModule\Action;

    use Cronical\Api\Representation\ScheduledActionRunRepresentation;
    use Cronical\Action\AbstractJobDispatchAction;

    class MyAction extends AbstractJobDispatchAction
    {
        public function getLabel(): string
        {
            return 'My action'; // @translate
        }

        public function getGroupLabel(): string
        {
            return 'My module'; // @translate
        }

        public function getDescription(): string
        {
            return 'A short description of what the action does'; // @translate
        }

        public function getJobClass(ScheduledActionRunRepresentation $scheduledActionRun): string
        {
            return \MyModule\Job\MyJob::class;
        }

        // Optional if the job does not need args
        protected function getJobArgs(ScheduledActionRunRepresentation $scheduledActionRun): array
        {
            return [
                'arg1' => 'value1',
            ];
        }
    }

Otherwise, create ``src/Action/MyAction.php`` like this:

.. code-block:: php

    namespace MyModule\Action;

    use Cronical\Api\Representation\ScheduledActionRunRepresentation;

    class MyAction extends AbstractAction
    {
        public function getLabel(): string
        {
            return 'My action'; // @translate
        }

        public function getGroupLabel(): string
        {
            return 'My module'; // @translate
        }

        public function getDescription(): string
        {
            return 'A short description of what the action does'; // @translate
        }


        public function perform(ScheduledActionRunRepresentation $scheduledActionRun): void
        {
            // Perform the actual action
        }

    }

If the action needs additional parameters you should also implement the following methods:

.. code-block:: php

    public function formAddElements(
        \Laminas\Form\Form $form,
        \Cronical\Api\Representation\ScheduledActionRepresentation $scheduledAction
    ): void
    {
        $form->get('o:settings')->add([
            'name' => 'setting1',
            'type' => 'Laminas\Form\Element\Text',
            'options' => [
                'label' => 'Setting 1', // @translate
            ],
        ]);
    }

    public function formAddInputFilters(
        \Laminas\InputFilter\InputFilterInterface $inputFilter,
        \Cronical\Api\Representation\ScheduledActionRepresentation $scheduledAction
    ): void
    {
        $inputFilter->get('o:settings')->add([
            'name' => 'setting1',
            'required' => false,
        ]);
    }

    // Optional but recommended to show the action parameters on the "show" page
    public function onViewShow(
        \Laminas\View\Renderer\PhpRenderer $view,
        \Cronical\Api\Representation\ScheduledActionRepresentation $scheduledAction
    ): void
    {
        echo $view->partial('my-module/common/action/my-action/show', ['scheduledAction' => $scheduledAction]);
    }

    // Optional but recommended to show the action parameters on the "show-details" sidebar
    public function onViewDetails(
        \Laminas\View\Renderer\PhpRenderer $view,
        \Cronical\Api\Representation\ScheduledActionRepresentation $scheduledAction
    ): void
    {
        echo $view->partial('my-module/common/action/my-action/details', ['scheduledAction' => $scheduledAction]);
    }

Additionally, if the class implements ``Laminas\Log\LoggerAwareInterface``,
the logger is automatically injected.
