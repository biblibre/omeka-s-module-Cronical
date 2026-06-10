<?php

namespace Cronical\Api\Representation;

use DateTime;
use Omeka\Api\Representation\AbstractEntityRepresentation;
use Omeka\Api\Representation\UserRepresentation;

class ScheduledActionRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLd()
    {
        return [
            'o:owner' => $this->owner()->getReference(),
            'o:action' => $this->action(),
            'o:name' => $this->name(),
            'o:minute' => $this->minute(),
            'o:hour' => $this->hour(),
            'o:day_of_month' => $this->dayOfMonth(),
            'o:month' => $this->month(),
            'o:day_of_week' => $this->dayOfWeek(),
            'o:cron_expression' => $this->cronExpression(),
            'o:is_active' => $this->isActive(),
            'o:is_system' => $this->isSystem(),
            'o:run_history_size' => $this->runHistorySize(),
            'o:settings' => $this->settings(),
            'o:scheduled' => [
                '@value' => $this->getDateTime($this->scheduled()),
                '@type' => 'http://www.w3.org/2001/XMLSchema#dateTime',
            ],
            'o:created' => [
                '@value' => $this->getDateTime($this->created()),
                '@type' => 'http://www.w3.org/2001/XMLSchema#dateTime',
            ],
            'o:modified' => [
                '@value' => $this->getDateTime($this->modified()),
                '@type' => 'http://www.w3.org/2001/XMLSchema#dateTime',
            ],
        ];
    }

    public function getJsonLdType()
    {
        return 'o:CronicalScheduledAction';
    }

    public function adminUrl($action = null, $canonical = false)
    {
        $url = $this->getViewHelper('Url');

        return $url(
            'admin/cronical/id',
            [
                'controller' => 'scheduled-action',
                'action' => $action,
                'id' => $this->id(),
            ],
            ['force_canonical' => $canonical]
        );
    }

    public function owner(): UserRepresentation
    {
        return $this->getAdapter('users')->getRepresentation($this->resource->getOwner());
    }

    public function action(): string
    {
        return $this->resource->getAction();
    }

    public function actionLabel(): string
    {
        $actionManager = $this->getServiceLocator()->get('Cronical\ActionManager');
        $action = $actionManager->get($this->action());

        return $action->getLabel();
    }

    public function actionGroupLabel(): string
    {
        $actionManager = $this->getServiceLocator()->get('Cronical\ActionManager');
        $action = $actionManager->get($this->action());

        return $action->getGroupLabel();
    }

    public function actionDescription(): string
    {
        $actionManager = $this->getServiceLocator()->get('Cronical\ActionManager');
        $action = $actionManager->get($this->action());

        return $action->getDescription();
    }

    public function name(): string
    {
        return $this->resource->getName();
    }

    public function minute(): string
    {
        return $this->resource->getMinute();
    }

    public function hour(): string
    {
        return $this->resource->getHour();
    }

    public function dayOfMonth(): string
    {
        return $this->resource->getDayOfMonth();
    }

    public function month(): string
    {
        return $this->resource->getMonth();
    }

    public function dayOfWeek(): string
    {
        return $this->resource->getDayOfWeek();
    }

    public function cronExpression(): string
    {
        return $this->resource->getCronExpression();
    }

    public function isActive(): bool
    {
        return $this->resource->getIsActive();
    }

    public function isSystem(): bool
    {
        return $this->resource->getIsSystem();
    }

    public function runHistorySize(): int
    {
        return $this->resource->getRunHistorySize();
    }

    public function scheduled(): DateTime
    {
        return $this->resource->getScheduled();
    }

    public function created(): DateTime
    {
        return $this->resource->getCreated();
    }

    public function modified(): DateTime
    {
        return $this->resource->getModified();
    }

    public function settings(): array
    {
        return $this->resource->getSettings();
    }

    public function setting(string $name, mixed $default = null): mixed
    {
        return $this->resource->getSetting($name, $default);
    }

    public function latestRun(): ?ScheduledActionRunRepresentation
    {
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $response = $api->search('cronical_scheduled_action_runs', [
            'scheduled_action_id' => $this->id(),
            'limit' => 1,
            'sort_by' => 'started',
            'sort_order' => 'desc',
        ]);

        $runs = $response->getContent();
        $run = $runs ? reset($runs) : null;

        return $run;
    }
}
