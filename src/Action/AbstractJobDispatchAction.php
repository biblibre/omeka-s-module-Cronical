<?php

namespace Cronical\Action;

use Cronical\Api\Representation\ScheduledActionRunRepresentation;
use Doctrine\ORM\EntityManager;
use Omeka\Job\Dispatcher as JobDispatcher;
use Laminas\Log\LoggerAwareInterface;
use Laminas\Log\LoggerAwareTrait;

abstract class AbstractJobDispatchAction extends AbstractAction implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected EntityManager $entityManager;
    protected JobDispatcher $jobDispatcher;

    abstract protected function getJobClass(ScheduledActionRunRepresentation $scheduledActionRun): string;

    protected function getJobArgs(ScheduledActionRunRepresentation $scheduledActionRun): array
    {
        return [];
    }

    public function perform(ScheduledActionRunRepresentation $scheduledActionRun): void
    {
        $jobClass = $this->getJobClass($scheduledActionRun);
        $jobArgs = $this->getJobArgs($scheduledActionRun);

        $job = $this->getJobDispatcher()->dispatch($jobClass, $jobArgs);

        $em = $this->getEntityManager();
        $scheduledActionRunEntity = $em->find('Cronical\Entity\ScheduledActionRun', $scheduledActionRun->id());
        $scheduledActionRunEntity->setJob($job);
        $em->flush();
    }

    public function setEntityManager(EntityManager $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    public function setJobDispatcher(JobDispatcher $jobDispatcher): void
    {
        $this->jobDispatcher = $jobDispatcher;
    }

    public function getJobDispatcher(): JobDispatcher
    {
        return $this->jobDispatcher;
    }
}
