<?php

namespace Cronical\Action;

use Cronical\Api\Representation\ScheduledActionRunRepresentation;

class IndexFulltextSearch extends AbstractJobDispatchAction
{
    public function getLabel(): string
    {
        return 'Index full-text'; // @translate
    }

    public function getGroupLabel(): string
    {
        return 'Omeka';
    }

    public function getDescription(): string
    {
        return 'Rebuild Omeka full-text search index'; // @translate
    }

    public function getJobClass(ScheduledActionRunRepresentation $scheduledActionRun): string
    {
        return \Omeka\Job\IndexFulltextSearch::class;
    }
}
