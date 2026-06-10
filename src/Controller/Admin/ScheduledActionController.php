<?php

namespace Cronical\Controller\Admin;

use Cronical\Form\ScheduledActionAddForm;
use Cronical\Form\ScheduledActionEditForm;
use Cronical\Form\ScheduledActionBatchUpdateForm;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Form\ConfirmForm;

class ScheduledActionController extends AbstractActionController
{
    public function searchAction()
    {
        $view = new ViewModel;
        $view->setVariable('query', $this->params()->fromQuery());

        return $view;
    }

    public function browseAction()
    {
        $this->browse()->setDefaults('cronical_scheduled_actions');
        $response = $this->api()->search('cronical_scheduled_actions', $this->params()->fromQuery());
        $this->paginator($response->getTotalResults());

        $formDeleteSelected = $this->getForm(ConfirmForm::class);
        $formDeleteSelected->setAttribute('action', $this->url()->fromRoute(null, ['action' => 'batch-delete'], true));
        $formDeleteSelected->setButtonLabel('Confirm Delete'); // @translate
        $formDeleteSelected->setAttribute('id', 'confirm-delete-selected');

        $formDeleteAll = $this->getForm(ConfirmForm::class);
        $formDeleteAll->setAttribute('action', $this->url()->fromRoute(null, ['action' => 'batch-delete-all'], true));
        $formDeleteAll->setButtonLabel('Confirm Delete'); // @translate
        $formDeleteAll->setAttribute('id', 'confirm-delete-all');
        $formDeleteAll->get('submit')->setAttribute('disabled', true);

        $view = new ViewModel;
        $view->setVariable('scheduledActions', $response->getContent());
        $view->setVariable('formDeleteSelected', $formDeleteSelected);
        $view->setVariable('formDeleteAll', $formDeleteAll);

        return $view;
    }

    public function showAction()
    {
        $response = $this->api()->read('cronical_scheduled_actions', $this->params('id'));

        $view = new ViewModel;
        $view->setVariable('scheduledAction', $response->getContent());

        return $view;
    }

    public function showDetailsAction()
    {
        $response = $this->api()->read('cronical_scheduled_actions', $this->params('id'));

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('resource', $response->getContent());

        return $view;
    }

    public function addAction()
    {
        $form = $this->getForm(ScheduledActionAddForm::class);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());
            if ($form->isValid()) {
                $formData = $form->getData();

                $action = $this->cronical()->getAction($formData['o:action']);

                $formData['o:name'] = sprintf(
                    '%s: %s',
                    $this->translate($action->getGroupLabel()),
                    $this->translate($action->getLabel())
                );
                $formData['o:minute'] = '0';
                $formData['o:hour'] = '0';
                $formData['o:day_of_month'] = '*';
                $formData['o:month'] = '*';
                $formData['o:day_of_week'] = '*';
                $formData['o:is_active'] = false;

                $response = $this->api($form)->create('cronical_scheduled_actions', $formData);
                if ($response) {
                    $this->messenger()->addSuccess('Scheduled action successfully created.'); // @translate

                    $scheduledAction = $response->getContent();

                    return $this->redirect()->toRoute('admin/cronical/id', ['controller' => 'scheduled-action', 'action' => 'edit', 'id' => $scheduledAction->id()]);
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);

        return $view;
    }

    public function editAction()
    {
        $id = $this->params('id');

        $scheduledAction = $this->api()->read('cronical_scheduled_actions', $id)->getContent();

        $form = $this->getForm(ScheduledActionEditForm::class, ['scheduledAction' => $scheduledAction]);
        $action = $this->cronical()->getAction($scheduledAction->action());
        $action->formAddElements($form, $scheduledAction);

        $inputFilter = $form->getInputFilter();
        $action->formAddInputFilters($inputFilter, $scheduledAction);

        $form->populateValues([
            'o:action' => $scheduledAction->action(),
            'o:name' => $scheduledAction->name(),
            'o:minute' => $scheduledAction->minute(),
            'o:hour' => $scheduledAction->hour(),
            'o:day_of_month' => $scheduledAction->dayOfMonth(),
            'o:month' => $scheduledAction->month(),
            'o:day_of_week' => $scheduledAction->dayOfWeek(),
            'o:is_active' => $scheduledAction->isActive(),
            'o:run_history_size' => $scheduledAction->runHistorySize(),
            'o:settings' => $scheduledAction->settings(),
        ]);

        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();
            $form->setData($postData);
            if ($form->isValid()) {
                $formData = $form->getData();
                $formData['o:action'] = $scheduledAction->action();

                $response = $this->api($form)->update('cronical_scheduled_actions', $id, $formData);
                if ($response) {
                    $this->messenger()->addSuccess('Scheduled action successfully updated'); // @translate

                    return $this->redirect()->toRoute('admin/cronical/default', ['controller' => 'scheduled-action']);
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setVariable('scheduledAction', $scheduledAction);
        $view->setVariable('form', $form);

        return $view;
    }

    public function deleteConfirmAction()
    {
        $resource = $this->api()->read('cronical_scheduled_actions', $this->params('id'))->getContent();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('common/delete-confirm-details');
        $view->setVariable('resource', $resource);
        $view->setVariable('resourceLabel', 'scheduled action'); // @translate
        $view->setVariable('partialPath', 'cronical/admin/scheduled-action/show-details');

        return $view;
    }

    public function deleteAction()
    {
        if ($this->getRequest()->isPost()) {
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $response = $this->api($form)->delete('cronical_scheduled_actions', $this->params('id'));
                if ($response) {
                    $this->messenger()->addSuccess('Scheduled action successfully deleted'); // @translate
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        return $this->redirect()->toRoute('admin/cronical/default', ['controller' => 'scheduled-action']);
    }

    public function batchDeleteAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('admin/cronical/default', ['controller' => 'scheduled-action']);
        }

        $resourceIds = $this->params()->fromPost('resource_ids', []);
        $resourceIds = array_filter(array_unique(array_map('intval', $resourceIds)));
        if (!$resourceIds) {
            $this->messenger()->addError('You must select at least one scheduled action to batch delete.'); // @translate

            return $this->redirect()->toRoute('admin/cronical/default', ['controller' => 'scheduled-action']);
        }

        $form = $this->getForm(ConfirmForm::class);
        $form->setData($this->getRequest()->getPost());
        if ($form->isValid()) {
            $response = $this->api($form)->batchDelete('cronical_scheduled_actions', $resourceIds, [], ['continueOnError' => true]);
            if ($response) {
                $this->messenger()->addSuccess('Scheduled actions successfully deleted'); // @translate
            }
        } else {
            $this->messenger()->addFormErrors($form);
        }

        return $this->redirect()->toRoute('admin/cronical/default', ['controller' => 'scheduled-action']);
    }

    public function batchDeleteAllAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('admin/cronical/default', ['controller' => 'scheduled-action']);
        }

        // Derive the query, removing limiting and sorting params.
        $query = json_decode($this->params()->fromPost('query', []), true);
        unset($query['submit'], $query['page'], $query['per_page'], $query['limit'],
            $query['offset'], $query['sort_by'], $query['sort_order']);

        $form = $this->getForm(ConfirmForm::class);
        $form->setData($this->getRequest()->getPost());
        if ($form->isValid()) {
            $job = $this->jobDispatcher()->dispatch('Omeka\Job\BatchDelete', [
                'resource' => 'cronical_scheduled_actions',
                'query' => $query,
            ]);
            $this->messenger()->addSuccess('Deleting scheduled actions. This may take a while.'); // @translate
        } else {
            $this->messenger()->addFormErrors($form);
        }

        return $this->redirect()->toRoute('admin/cronical/default', ['controller' => 'scheduled-action']);
    }

    public function batchEditAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('admin/cronical/default', ['controller' => 'scheduled-action']);
        }

        $resourceIds = $this->params()->fromPost('resource_ids', []);
        $resourceIds = array_filter(array_unique(array_map('intval', $resourceIds)));
        if (!$resourceIds) {
            $this->messenger()->addError('You must select at least one scheduled action to batch edit.'); // @translate

            return $this->redirect()->toRoute('admin/cronical/default', ['controller' => 'scheduled-action']);
        }

        $form = $this->getForm(ScheduledActionBatchUpdateForm::class);
        if ($this->params()->fromPost('batch_update')) {
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $data = $form->getData();

                $response = $this->api($form)->batchUpdate('cronical_scheduled_actions', $resourceIds, $data);
                if ($response) {
                    $this->messenger()->addSuccess('Scheduled actions successfully edited'); // @translate

                    return $this->redirect()->toRoute('admin/cronical/default', ['controller' => 'scheduled-action']);
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $resources = [];
        foreach ($resourceIds as $resourceId) {
            $resources[] = $this->api()->read('cronical_scheduled_actions', $resourceId)->getContent();
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('resources', $resources);
        $view->setVariable('query', []);
        $view->setVariable('count', null);

        return $view;
    }

    public function batchEditAllAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('admin/cronical/default', ['controller' => 'scheduled-action']);
        }

        // Derive the query, removing limiting and sorting params.
        $query = json_decode($this->params()->fromPost('query', []), true);
        unset($query['submit'], $query['page'], $query['per_page'], $query['limit'],
            $query['offset'], $query['sort_by'], $query['sort_order']);
        $count = $this->api()->search('cronical_scheduled_actions', ['limit' => 0] + $query)->getTotalResults();

        $form = $this->getForm(ScheduledActionBatchUpdateForm::class);
        if ($this->params()->fromPost('batch_update')) {
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $data = $form->getData();

                $job = $this->jobDispatcher()->dispatch('Omeka\Job\BatchUpdate', [
                    'resource' => 'cronical_scheduled_actions',
                    'query' => $query,
                    'data' => $data ?? [],
                ]);

                $this->messenger()->addSuccess('Editing scheduled actions. This may take a while.'); // @translate

                return $this->redirect()->toRoute('admin/cronical/default', ['controller' => 'scheduled-action']);
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setTemplate('cronical/admin/scheduled-action/batch-edit');
        $view->setVariable('form', $form);
        $view->setVariable('resources', []);
        $view->setVariable('query', $query);
        $view->setVariable('count', $count);

        return $view;
    }
}
