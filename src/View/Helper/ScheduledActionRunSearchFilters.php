<?php

namespace Cronical\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class ScheduledActionRunSearchFilters extends AbstractHelper
{
    /**
     * The default partial view script.
     */
    const PARTIAL_NAME = 'common/search-filters';

    public function __invoke($partialName = null)
    {
        $partialName = $partialName ?: self::PARTIAL_NAME;

        $view = $this->getView();

        $filters = [];
        $query = $view->params()->fromQuery();

        foreach ($query as $key => $value) {
            if (!strlen($value)) {
                continue;
            }

            switch ($key) {
                case 'status':
                    $filterLabel = $view->translate('Status');
                    $filters[$filterLabel][] = match ($value) {
                        'in_progress' => $view->translate('In Progress'),
                        'completed' => $view->translate('Completed'),
                        'error' => $view->translate('Error'),
                        default => $view->translate('Unknown'),
                    };
                    break;

                case 'scheduled_action_id':
                    $filterLabel = $view->translate('Scheduled action');
                    try {
                        $scheduledAction = $view->api()->read('cronical_scheduled_actions', $value)->getContent();
                        $filters[$filterLabel][] = $scheduledAction->name();
                    } catch (\Exception $e) {
                        $filters[$filterLabel][] = $value;
                    }
                    break;

                case 'started_after':
                    $filterLabel = $view->translate('Started after');
                    $filters[$filterLabel][] = $value;
                    break;

                case 'started_before':
                    $filterLabel = $view->translate('Started before');
                    $filters[$filterLabel][] = $value;
                    break;

                case 'ended_after':
                    $filterLabel = $view->translate('Ended after');
                    $filters[$filterLabel][] = $value;
                    break;

                case 'ended_before':
                    $filterLabel = $view->translate('Ended before');
                    $filters[$filterLabel][] = $value;
                    break;
            }
        }

        $result = $view->trigger(
            'view.search.filters',
            ['filters' => $filters, 'query' => $query],
            true
        );
        $filters = $result['filters'];

        return $view->partial(
            $partialName,
            [
                'filters' => $filters,
            ]
        );
    }
}
