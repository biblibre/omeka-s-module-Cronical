<?php

namespace Cronical\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Form\ConfirmForm;

class ScheduledActionRunController extends AbstractActionController
{
    public function searchAction()
    {
        $view = new ViewModel;
        $view->setVariable('query', $this->params()->fromQuery());

        return $view;
    }

    public function browseAction()
    {
        $this->browse()->setDefaults('cronical_scheduled_action_runs');
        $response = $this->api()->search('cronical_scheduled_action_runs', $this->params()->fromQuery());
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
        $view->setVariable('scheduledActionRuns', $response->getContent());
        $view->setVariable('formDeleteSelected', $formDeleteSelected);
        $view->setVariable('formDeleteAll', $formDeleteAll);

        return $view;
    }

    public function showAction()
    {
        $response = $this->api()->read('cronical_scheduled_action_runs', $this->params('id'));

        $view = new ViewModel;
        $view->setVariable('scheduledActionRun', $response->getContent());

        return $view;
    }

    public function showDetailsAction()
    {
        $response = $this->api()->read('cronical_scheduled_action_runs', $this->params('id'));

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('resource', $response->getContent());

        return $view;
    }

    public function deleteConfirmAction()
    {
        $resource = $this->api()->read('cronical_scheduled_action_runs', $this->params('id'))->getContent();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('common/delete-confirm-details');
        $view->setVariable('resource', $resource);
        $view->setVariable('resourceLabel', 'scheduled action run'); // @translate
        $view->setVariable('partialPath', 'cronical/admin/scheduled-action-run/show-details');

        return $view;
    }

    public function deleteAction()
    {
        if ($this->getRequest()->isPost()) {
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $response = $this->api($form)->delete('cronical_scheduled_action_runs', $this->params('id'));
                if ($response) {
                    $this->messenger()->addSuccess('Scheduled action run successfully deleted'); // @translate
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        return $this->redirect()->toRoute('admin/cronical/default', ['controller' => 'scheduled-action-run']);
    }

    public function batchDeleteAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('admin/cronical/default', ['controller' => 'scheduled-action-run']);
        }

        $resourceIds = $this->params()->fromPost('resource_ids', []);
        $resourceIds = array_filter(array_unique(array_map('intval', $resourceIds)));
        if (!$resourceIds) {
            $this->messenger()->addError('You must select at least one scheduled action run to batch delete.'); // @translate

            return $this->redirect()->toRoute('admin/cronical/default', ['controller' => 'scheduled-action-run']);
        }

        $form = $this->getForm(ConfirmForm::class);
        $form->setData($this->getRequest()->getPost());
        if ($form->isValid()) {
            $response = $this->api($form)->batchDelete('cronical_scheduled_action_runs', $resourceIds, [], ['continueOnError' => true]);
            if ($response) {
                $this->messenger()->addSuccess('Scheduled action runs successfully deleted'); // @translate
            }
        } else {
            $this->messenger()->addFormErrors($form);
        }

        return $this->redirect()->toRoute('admin/cronical/default', ['controller' => 'scheduled-action-run']);
    }

    public function batchDeleteAllAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('admin/cronical/default', ['controller' => 'scheduled-action-run']);
        }

        // Derive the query, removing limiting and sorting params.
        $query = json_decode($this->params()->fromPost('query', []), true);
        unset($query['submit'], $query['page'], $query['per_page'], $query['limit'],
            $query['offset'], $query['sort_by'], $query['sort_order']);

        $form = $this->getForm(ConfirmForm::class);
        $form->setData($this->getRequest()->getPost());
        if ($form->isValid()) {
            $job = $this->jobDispatcher()->dispatch('Omeka\Job\BatchDelete', [
                'resource' => 'cronical_scheduled_action_runs',
                'query' => $query,
            ]);
            $this->messenger()->addSuccess('Deleting scheduled action runs. This may take a while.'); // @translate
        } else {
            $this->messenger()->addFormErrors($form);
        }

        return $this->redirect()->toRoute('admin/cronical/default', ['controller' => 'scheduled-action-run']);
    }
}
