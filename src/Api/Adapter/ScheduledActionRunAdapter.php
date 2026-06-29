<?php

namespace Cronical\Api\Adapter;

use DateTime;
use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class ScheduledActionRunAdapter extends AbstractEntityAdapter
{
    protected $sortFields = [
        'id' => 'id',
        'started' => 'started',
        'ended' => 'ended',
    ];

    protected $scalarFields = [
        'id' => 'id',
        'status' => 'status',
        'started' => 'started',
        'ended' => 'ended',
    ];

    public function getResourceName()
    {
        return 'cronical_scheduled_action_runs';
    }

    public function getRepresentationClass()
    {
        return \Cronical\Api\Representation\ScheduledActionRunRepresentation::class;
    }

    public function getEntityClass()
    {
        return \Cronical\Entity\ScheduledActionRun::class;
    }

    public function hydrate(Request $request, EntityInterface $entity, ErrorStore $errorStore)
    {
        $data = $request->getContent();

        if ($request->getOperation() === Request::CREATE && isset($data['o:scheduled_action']['o:id'])) {
            $scheduledAction = $this->getAdapter('cronical_scheduled_actions')
                ->findEntity($data['o:scheduled_action']['o:id']);
            $entity->setScheduledAction($scheduledAction);

            $entity->setOwner($scheduledAction->getOwner());
            $entity->setSettings($scheduledAction->getSettings());
        }

        if (isset($data['o:job']['o:id'])) {
            $job = $this->getAdapter('jobs')
                ->findEntity($data['o:job']['o:id']);
            $entity->setJob($job);
        }

        if ($this->shouldHydrate($request, 'o:status')) {
            $entity->setStatus($request->getValue('o:status', 'in_progress'));
        }

        if ($request->getOperation() === Request::CREATE) {
            $entity->setStarted(new Datetime());
        }

        if ($this->shouldHydrate($request, 'o:ended')) {
            $ended = $request->getValue('o:ended');
            $entity->setEnded($ended ? new DateTime($ended) : null);
        }
    }

    public function validateRequest(Request $request, ErrorStore $errorStore)
    {
        $data = $request->getContent();

        if ($request->getOperation() === Request::CREATE) {
            if (!isset($data['o:scheduled_action']['o:id'])) {
                $errorStore->addError('o:scheduled_action', 'Scheduled action id is missing'); // @translate
            }
        }
    }

    public function validateEntity(EntityInterface $entity, ErrorStore $errorStore)
    {
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
        if (!empty($query['scheduled_action_id'])) {
            $qb->andWhere($qb->expr()->eq(
                "omeka_root.scheduledAction",
                $this->createNamedParameter($qb, $query['scheduled_action_id']))
            );
        }

        if (!empty($query['status'])) {
            $qb->andWhere($qb->expr()->eq(
                "omeka_root.status",
                $this->createNamedParameter($qb, $query['status']))
            );
        }

        $started_before = trim($query['started_before'] ?? '');
        if ($started_before !== '') {
            $qb->andWhere($qb->expr()->lte(
                'omeka_root.started',
                $this->createNamedParameter($qb, $started_before)
            ));
        }

        $started_after = trim($query['started_after'] ?? '');
        if ($started_after !== '') {
            $qb->andWhere($qb->expr()->gte(
                'omeka_root.started',
                $this->createNamedParameter($qb, $started_after)
            ));
        }

        $ended_before = trim($query['ended_before'] ?? '');
        if ($ended_before !== '') {
            $qb->andWhere($qb->expr()->lte(
                'omeka_root.ended',
                $this->createNamedParameter($qb, $ended_before)
            ));
        }

        $ended_after = trim($query['ended_after'] ?? '');
        if ($ended_after !== '') {
            $qb->andWhere($qb->expr()->gte(
                'omeka_root.ended',
                $this->createNamedParameter($qb, $ended_after)
            ));
        }
    }
}
