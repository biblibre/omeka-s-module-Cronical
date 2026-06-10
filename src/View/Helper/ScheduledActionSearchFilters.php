<?php

namespace Cronical\View\Helper;

use Exception;
use Laminas\View\Helper\AbstractHelper;

class ScheduledActionSearchFilters extends AbstractHelper
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
                case 'owner_id':
                    try {
                        $owner = $view->api()->read('users', $value)->getContent();
                        $filterLabel = $view->translate('Owner');
                        $filters[$filterLabel][] = $owner->name();
                    } catch (Exception $e) {
                        $filterLabel = $view->translate('Owner ID');
                        $filters[$filterLabel][] = $value;
                    }
                    break;

                case 'action':
                    $filterLabel = $view->translate('Action');
                    $action = $view->cronical()->getAction($value);
                    $filters[$filterLabel][] = sprintf('%s: %s', $action->getGroupLabel(), $action->getLabel());
                    break;

                case 'is_active':
                    $filterLabel = $view->translate('Is active');
                    $filters[$filterLabel][] = $value ? $view->translate('Yes') : $view->translate('No');
                    break;

                case 'is_system':
                    $filterLabel = $view->translate('Is system');
                    $filters[$filterLabel][] = $value ? $view->translate('Yes') : $view->translate('No');
                    break;

                case 'latest_run_started_after':
                    $filterLabel = $view->translate('Latest run after');
                    $filters[$filterLabel][] = $value;
                    break;

                case 'latest_run_started_before':
                    $filterLabel = $view->translate('Latest run before');
                    $filters[$filterLabel][] = $value;
                    break;

                case 'scheduled_after':
                    $filterLabel = $view->translate('Next run scheduled after');
                    $filters[$filterLabel][] = $value;
                    break;

                case 'scheduled_before':
                    $filterLabel = $view->translate('Next run scheduled before');
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
