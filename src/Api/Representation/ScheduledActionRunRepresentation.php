<?php

namespace Cronical\Api\Representation;

use Cronical\Entity\ScheduledActionRun;
use DateTime;
use Omeka\Api\Representation\AbstractEntityRepresentation;
use Omeka\Api\Representation\JobRepresentation;
use Omeka\Api\Representation\UserRepresentation;

class ScheduledActionRunRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLd()
    {
        $job = $this->job();

        return [
            'o:scheduled_action' => $this->scheduledAction()->getReference(),
            'o:owner' => $this->owner()->getReference(),
            'o:job' => $job ? $job->getReference() : null,
            'o:status' => $this->status(),
            'o:started' => [
                '@value' => $this->getDateTime($this->started()),
                '@type' => 'http://www.w3.org/2001/XMLSchema#dateTime',
            ],
            'o:ended' => [
                '@value' => $this->getDateTime($this->ended()),
                '@type' => 'http://www.w3.org/2001/XMLSchema#dateTime',
            ],
            'o:settings' => $this->settings(),
        ];
    }

    public function getJsonLdType()
    {
        return 'o:CronicalScheduledActionRun';
    }

    public function adminUrl($action = null, $canonical = false)
    {
        $url = $this->getViewHelper('Url');

        return $url(
            'admin/cronical/id',
            [
                'controller' => 'scheduled-action-run',
                'action' => $action,
                'id' => $this->id(),
            ],
            ['force_canonical' => $canonical]
        );
    }

    public function scheduledAction(): ScheduledActionRepresentation
    {
        return $this->getAdapter('cronical_scheduled_actions')->getRepresentation($this->resource->getScheduledAction());
    }

    public function owner(): ?UserRepresentation
    {
        return $this->getAdapter('users')->getRepresentation($this->resource->getOwner());
    }

    public function job(): ?JobRepresentation
    {
        return $this->getAdapter('jobs')->getRepresentation($this->resource->getJob());
    }

    public function status(): string
    {
        return $this->resource->getStatus();
    }

    public function statusLabel(): string
    {
        $statusLabel = match ($this->status()) {
            ScheduledActionRun::STATUS_IN_PROGRESS => 'In Progress', // @translate
            ScheduledActionRun::STATUS_COMPLETED => 'Completed', // @translate
            ScheduledActionRun::STATUS_ERROR => 'Error', // @translate
            default => 'Unknown', // @translate
        };

        return $statusLabel;
    }

    public function started(): DateTime
    {
        return $this->resource->getStarted();
    }

    public function ended(): ?DateTime
    {
        return $this->resource->getEnded();
    }

    public function settings(): array
    {
        return $this->resource->getSettings();
    }

    public function setting(string $name, mixed $default = null): mixed
    {
        return $this->resource->getSetting($name, $default);
    }
}
