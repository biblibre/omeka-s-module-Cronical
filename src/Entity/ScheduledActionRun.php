<?php

namespace Cronical\Entity;

use DateTime;
use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Job;
use Omeka\Entity\User;

/**
 * @Entity
 * @Table(name="cronical_scheduled_action_run")
 */
class ScheduledActionRun extends AbstractEntity
{
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ERROR = 'error';

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Cronical\Entity\ScheduledAction")
     * @JoinColumn(onDelete="CASCADE", nullable=false)
     */
    protected ScheduledAction $scheduledAction;

    /**
     * @ManyToOne(targetEntity="Omeka\Entity\User")
     * @JoinColumn(onDelete="SET NULL")
     */
    protected ?User $owner = null;

    /**
     * @ManyToOne(targetEntity="Omeka\Entity\Job")
     * @JoinColumn
     */
    protected ?Job $job = null;

    /**
     * @Column
     */
    protected string $status;

    /**
     * @Column(type="datetime")
     */
    protected DateTime $started;

    /**
     * @Column(type="datetime", nullable=true)
     */
    protected ?DateTime $ended;

    /**
     * @Column(type="json_array")
     */
    protected array $settings;

    public function getId()
    {
        return $this->id;
    }

    public function setScheduledAction(ScheduledAction $scheduledAction): void
    {
        $this->scheduledAction = $scheduledAction;
    }

    public function getScheduledAction(): ScheduledAction
    {
        return $this->scheduledAction;
    }

    public function setOwner(?User $owner): void
    {
        $this->owner = $owner;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setJob(?Job $job): void
    {
        $this->job = $job;
    }

    public function getJob(): ?Job
    {
        return $this->job;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStarted(DateTime $started): void
    {
        $this->started = $started;
    }

    public function getStarted(): DateTime
    {
        return $this->started;
    }

    public function setEnded(?DateTime $ended): void
    {
        $this->ended = $ended;
    }

    public function getEnded(): ?DateTime
    {
        return $this->ended;
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getSetting(string $name, mixed $default = null): mixed
    {
        if (!array_key_exists($name, $this->settings)) {
            return $default;
        }

        return $this->settings[$name];
    }
}
