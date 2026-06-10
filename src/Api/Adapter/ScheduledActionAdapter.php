<?php

namespace Cronical\Api\Adapter;

use Cronical\Entity\ScheduledActionRun;
use Cronical\Stdlib\CronExpression;
use DateTimeZone;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class ScheduledActionAdapter extends AbstractEntityAdapter
{
    protected $sortFields = [
        'id' => 'id',
        'name' => 'name',
        'scheduled' => 'scheduled',
        'created' => 'created',
        'modified' => 'modified',
    ];

    protected $scalarFields = [
        'id' => 'id',
        'owner' => 'owner',
        'action' => 'action',
        'name' => 'name',
        'minute' => 'minute',
        'hour' => 'hour',
        'day_of_month' => 'dayOfMonth',
        'month' => 'month',
        'day_of_week' => 'dayOfWeek',
        'is_active' => 'isActive',
        'is_system' => 'isSystem',
        'scheduled' => 'scheduled',
        'created' => 'created',
        'modified' => 'modified',
    ];

    public function getResourceName()
    {
        return 'cronical_scheduled_actions';
    }

    public function getRepresentationClass()
    {
        return \Cronical\Api\Representation\ScheduledActionRepresentation::class;
    }

    public function getEntityClass()
    {
        return \Cronical\Entity\ScheduledAction::class;
    }

    public function hydrate(Request $request, EntityInterface $entity, ErrorStore $errorStore)
    {
        // System actions cannot be updated
        if ($request->getOperation() !== Request::CREATE && $entity->getIsSystem()) {
            $errorStore->addError('error', 'Cannot update system action');
            return;
        }

        if ($request->getOperation() === Request::CREATE) {
            $owner = $this->getServiceLocator()->get('Omeka\AuthenticationService')->getIdentity();
            $entity->setOwner($owner);
        }
        $this->hydrateOwner($request, $entity);

        if ($request->getOperation() === Request::CREATE) {
            $entity->setAction($request->getValue('o:action'));
        }

        if ($this->shouldHydrate($request, 'o:name')) {
            $entity->setName($request->getValue('o:name', ''));
        }

        $shouldHydrateNextDate = false;

        if ($this->shouldHydrate($request, 'o:minute')) {
            $minute = trim($request->getValue('o:minute'));

            try {
                CronExpression::validateMinuteExpression($minute);

                if ($request->getOperation() === Request::UPDATE
                    && $entity->getMinute() !== $minute
                    && !preg_match('/^[0-5]?[0-9]$/', $minute)
                ) {
                    $this->authorize($entity, 'schedule-anything');
                }

                $entity->setMinute($minute);
                $shouldHydrateNextDate = true;
            } catch (Exception $e) {
                $errorStore->addError('o:minute', $e->getMessage());
            }
        }

        if ($this->shouldHydrate($request, 'o:hour')) {
            $hour = trim($request->getValue('o:hour'));

            try {
                CronExpression::validateHourExpression($hour);

                if ($request->getOperation() === Request::UPDATE
                    && $entity->getHour() !== $hour
                    && !preg_match('/^[0-1]?[0-9]|2[0-3]$/', $hour)
                ) {
                    $this->authorize($entity, 'schedule-anything');
                }

                $entity->setHour($hour);
                $shouldHydrateNextDate = true;
            } catch (Exception $e) {
                $errorStore->addError('o:hour', $e->getMessage());
            }
        }

        if ($this->shouldHydrate($request, 'o:day_of_month')) {
            $day_of_month = trim($request->getValue('o:day_of_month'));
            try {
                CronExpression::validateDayOfMonthExpression($day_of_month);
                $entity->setDayOfMonth($day_of_month);
                $shouldHydrateNextDate = true;
            } catch (Exception $e) {
                $errorStore->addError('o:day_of_month', $e->getMessage());
            }
        }

        if ($this->shouldHydrate($request, 'o:month')) {
            $month = trim($request->getValue('o:month'));
            try {
                CronExpression::validateMonthExpression($month);
                $entity->setMonth($month);
                $shouldHydrateNextDate = true;
            } catch (Exception $e) {
                $errorStore->addError('o:month', $e->getMessage());
            }
        }

        if ($this->shouldHydrate($request, 'o:day_of_week')) {
            $day_of_week = trim($request->getValue('o:day_of_week'));
            try {
                CronExpression::validateDayOfWeekExpression($day_of_week);
                $entity->setDayOfWeek($day_of_week);
                $shouldHydrateNextDate = true;
            } catch (Exception $e) {
                $errorStore->addError('o:day_of_week', $e->getMessage());
            }
        }

        if ($errorStore->hasErrors()) {
            return;
        }

        if ($shouldHydrateNextDate) {
            try {
                $settings = $this->getServiceLocator()->get('Omeka\Settings');
                $timezone = new DateTimeZone($settings->get('time_zone', 'UTC'));
                $cronExpression = new CronExpression($entity->getCronExpression(), $timezone);
                $entity->setScheduled($cronExpression->getNextDate());
            } catch (Exception $e) {
                $errorStore->addError('cron_expression', $e->getMessage());
            }
        }

        if ($this->shouldHydrate($request, 'o:is_active')) {
            $entity->setIsActive((bool) $request->getValue('o:is_active', false));
        }

        if ($this->shouldHydrate($request, 'o:is_system')) {
            $isSystem = (bool) $request->getValue('o:is_system', false);
            if ($isSystem || $entity->getIsSystem()) {
                $this->authorize($entity, 'schedule-anything');
            }
            $entity->setIsSystem($isSystem);
        }

        if ($this->shouldHydrate($request, 'o:run_history_size')) {
            $entity->setRunHistorySize($request->getValue('o:run_history_size', 1));
        }

        if ($this->shouldHydrate($request, 'o:settings')) {
            $entity->setSettings($request->getValue('o:settings', []));
        }

        $this->updateTimestamps($request, $entity);
    }

    public function validateRequest(Request $request, ErrorStore $errorStore)
    {
    }

    public function validateEntity(EntityInterface $entity, ErrorStore $errorStore)
    {
        /* @var \Cronical\Entity\ScheduledAction $entity */
        if (false == $entity->getAction()) {
            $errorStore->addError('o:action', 'The action cannot be empty.'); // @translate
        }
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
        if (isset($query['owner_id']) && is_numeric($query['owner_id'])) {
            $qb->andWhere($qb->expr()->eq(
                "omeka_root.owner",
                $qb->createNamedParameter($query['owner_id']))
            );
        }

        if (!empty($query['action'])) {
            $qb->andWhere($qb->expr()->eq(
                "omeka_root.action",
                $qb->createNamedParameter($query['action']))
            );
        }

        if (isset($query['is_active']) && trim($query['is_active']) !== '') {
            $qb->andWhere($qb->expr()->eq(
                "omeka_root.isActive",
                $qb->createNamedParameter((bool) $query['is_active']))
            );
        }

        if (isset($query['is_system']) && trim($query['is_system']) !== '') {
            $qb->andWhere($qb->expr()->eq(
                "omeka_root.isSystem",
                $qb->createNamedParameter((bool) $query['is_system']))
            );
        }

        $latest_run_started_before = trim($query['latest_run_started_before'] ?? '');
        $latest_run_started_after = trim($query['latest_run_started_after'] ?? '');
        if ($latest_run_started_before !== '' || $latest_run_started_after !== '') {
            $latestRunAlias = $qb->createAlias();
            $this->joinLatestRun($qb, $latestRunAlias);

            if ($latest_run_started_before !== '') {
                $qb->andWhere($qb->expr()->lte(
                    "$latestRunAlias.started",
                    $qb->createNamedParameter($latest_run_started_before)
                ));
            }
            if ($latest_run_started_after !== '') {
                $qb->andWhere($qb->expr()->gte(
                    "$latestRunAlias.started",
                    $qb->createNamedParameter($latest_run_started_after)
                ));
            }
        }

        $scheduled_before = trim($query['scheduled_before'] ?? '');
        if ($scheduled_before !== '') {
            $qb->andWhere($qb->expr()->lte(
                'omeka_root.scheduled',
                $qb->createNamedParameter($scheduled_before)
            ));
        }

        $scheduled_after = trim($query['scheduled_after'] ?? '');
        if ($scheduled_after !== '') {
            $qb->andWhere($qb->expr()->gte(
                'omeka_root.scheduled',
                $qb->createNamedParameter($scheduled_after)
            ));
        }
    }

    public function sortQuery(QueryBuilder $qb, array $query)
    {
        if (isset($query['sort_by']) && is_string($query['sort_by'])) {
            if ($query['sort_by'] === 'latest_run_started') {
                $latestRunAlias = $qb->createAlias();
                $this->joinLatestRun($qb, $latestRunAlias);
                $qb->addOrderBy("$latestRunAlias.started", $query['sort_order']);
            }
        }
    }

    public function preprocessBatchUpdate(array $data, Request $request)
    {
        $rawData = $request->getContent();

        if (array_key_exists('o:minute', $rawData)) {
            $minute = trim($rawData['o:minute']);
            if ($minute !== '') {
                $data['o:minute'] = $minute;
            }
        }

        if (array_key_exists('o:hour', $rawData)) {
            $hour = trim($rawData['o:hour']);
            if ($hour !== '') {
                $data['o:hour'] = $hour;
            }
        }

        if (array_key_exists('o:day_of_month', $rawData)) {
            $day_of_month = trim($rawData['o:day_of_month']);
            if ($day_of_month !== '') {
                $data['o:day_of_month'] = $day_of_month;
            }
        }

        if (array_key_exists('o:month', $rawData)) {
            $month = trim($rawData['o:month']);
            if ($month !== '') {
                $data['o:month'] = $month;
            }
        }

        if (array_key_exists('o:day_of_week', $rawData)) {
            $day_of_week = trim($rawData['o:day_of_week']);
            if ($day_of_week !== '') {
                $data['o:day_of_week'] = $day_of_week;
            }
        }

        if (array_key_exists('o:is_active', $rawData) && in_array($rawData['o:is_active'], ['0', '1'])) {
            $data['o:is_active'] = $rawData['o:is_active'];
        }

        if (array_key_exists('o:run_history_size', $rawData) && is_numeric($rawData['o:run_history_size'])) {
            $data['o:run_history_size'] = $rawData['o:run_history_size'];
        }

        return $data;
    }

    public function delete(Request $request)
    {
        $entity = $this->findEntity($request->getId(), $request);
        if ($entity->getIsSystem()) {
            throw new Exception('System scheduled actions cannot be deleted');
        }

        return parent::delete($request);
    }

    protected function joinLatestRun(QueryBuilder $qb, string $alias): void
    {
        $subQb = $this->createQueryBuilder();
        $runAlias = $qb->createAlias();
        $subQb
            ->select("MAX($runAlias.id)")
            ->from(ScheduledActionRun::class, $runAlias)
            ->where("$runAlias.scheduledAction = omeka_root.id");

        $qb->leftJoin(
            'omeka_root.runs', $alias,
            'WITH', sprintf("$alias.id = (%s)", $subQb->getDQL()),
        );
    }
}
