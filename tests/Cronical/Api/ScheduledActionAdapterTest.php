<?php

namespace Cronical\Test\Api;

use Cronical\Entity\ScheduledActionRun;
use Cronical\Test\TestCase;
use DateTime;
use Laminas\Router\RouteMatch;
use Omeka\Api\Exception\PermissionDeniedException;
use Omeka\Api\Exception\ValidationException;
use Omeka\Entity\Job;

class ScheduledActionAdapterTest extends TestCase
{
    protected $scheduledActionIds = [];

    public function tearDown(): void
    {
        foreach ($this->scheduledActionIds as $id) {
            $this->api()->delete('cronical_scheduled_actions', $id);
        }
        $this->scheduledActionIds = [];

        parent::tearDown();
    }

    public function testSiteAdminIsDeniedEverything()
    {
        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $resources = [
            'Cronical\Controller\Admin\ScheduledAction',
            'Cronical\Controller\Admin\ScheduledActionRun',
            'Cronical\Api\Adapter\ScheduledActionAdapter',
            'Cronical\Api\Adapter\ScheduledActionRunAdapter',
            'Cronical\Entity\ScheduledAction',
            'Cronical\Entity\ScheduledActionRun',
        ];
        foreach ($resources as $resource) {
            foreach (['browse', 'read', 'create', 'update', 'delete'] as $privilege) {
                $this->assertFalse($acl->isAllowed('site_admin', $resource, $privilege), "site_admin: $resource $privilege");
            }
        }
    }

    public function testRunsCannotBeCreatedOrUpdatedThroughApi()
    {
        $scheduledAction = $this->createScheduledAction();

        $this->expectException(PermissionDeniedException::class);
        $this->api()->create('cronical_scheduled_action_runs', [
            'o:scheduled_action' => ['o:id' => $scheduledAction->id()],
        ]);
    }

    public function testCreateWithMinuteRangeIsDenied()
    {
        $this->expectException(ValidationException::class);
        $this->createScheduledAction(['o:minute' => '*']);
    }

    public function testCreateWithHourRangeIsDenied()
    {
        $this->expectException(ValidationException::class);
        $this->createScheduledAction(['o:hour' => '0-23']);
    }

    public function testUpdateWithHourListIsDenied()
    {
        $scheduledAction = $this->createScheduledAction();

        $this->expectException(ValidationException::class);
        $this->api()->update('cronical_scheduled_actions', $scheduledAction->id(), ['o:hour' => '1,2,3,4'], [], ['isPartial' => true]);
    }

    public function testUpdateWithSingleHourIsAllowed()
    {
        $scheduledAction = $this->createScheduledAction();

        foreach (['5', '23'] as $hour) {
            $response = $this->api()->update('cronical_scheduled_actions', $scheduledAction->id(), ['o:hour' => $hour], [], ['isPartial' => true]);
            $this->assertEquals($hour, $response->getContent()->hour());
        }
    }

    public function testImpossibleDateIsRejected()
    {
        $this->expectException(ValidationException::class);
        $this->createScheduledAction(['o:day_of_month' => '31', 'o:month' => '2']);
    }

    public function testJobSearchFilteredByScheduledAction()
    {
        // The filter only applies to admin requests
        $this->getApplication()->getMvcEvent()->setRouteMatch(new RouteMatch(['__ADMIN__' => true]));

        $scheduledAction = $this->createScheduledAction();
        $job = $this->createJob();
        $otherJob = $this->createJob();

        $run = new ScheduledActionRun();
        $run->setScheduledAction($this->getEntityManager()->find('Cronical\Entity\ScheduledAction', $scheduledAction->id()));
        $run->setStatus(ScheduledActionRun::STATUS_COMPLETED);
        $run->setStarted(new DateTime());
        $run->setSettings([]);
        $run->setJob($job);
        $this->getEntityManager()->persist($run);
        $this->getEntityManager()->flush();

        $jobIds = $this->api()->search('jobs', ['cronical_scheduled_action_id' => $scheduledAction->id()], ['returnScalar' => 'id'])->getContent();
        $this->assertEquals([$job->getId()], array_values($jobIds));

        // Values are cast to integers and bound as parameters, so this only
        // matches scheduled action #999999, which does not exist
        $jobIds = $this->api()->search('jobs', ['cronical_scheduled_action_id' => ['999999) OR 1=1 OR (1']], ['returnScalar' => 'id'])->getContent();
        $this->assertEquals([], array_values($jobIds));

        // Nested arrays are ignored instead of causing an error
        $jobIds = $this->api()->search('jobs', ['cronical_scheduled_action_id' => [[$scheduledAction->id()]]], ['returnScalar' => 'id'])->getContent();
        $this->assertContains($otherJob->getId(), $jobIds);
    }

    protected function createJob(): Job
    {
        $job = new Job();
        $job->setStatus(Job::STATUS_COMPLETED);
        $job->setClass('Omeka\Job\IndexFulltextSearch');
        $job->setStarted(new DateTime());
        $this->getEntityManager()->persist($job);
        $this->getEntityManager()->flush();

        return $job;
    }

    protected function createScheduledAction(array $data = [])
    {
        $data += [
            'o:action' => 'Cronical\Action\Heartbeat',
            'o:name' => 'Test',
            'o:minute' => '0',
            'o:hour' => '0',
            'o:day_of_month' => '*',
            'o:month' => '*',
            'o:day_of_week' => '*',
            'o:settings' => [],
        ];
        $scheduledAction = $this->api()->create('cronical_scheduled_actions', $data)->getContent();
        $this->scheduledActionIds[] = $scheduledAction->id();

        return $scheduledAction;
    }
}
