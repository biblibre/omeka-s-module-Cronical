<?php

namespace Cronical\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Omeka\Entity\AbstractEntity;
use Omeka\Entity\User;

/**
 * @Entity
 * @Table(name="cronical_scheduled_action")
 */
class ScheduledAction extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Omeka\Entity\User")
     * @JoinColumn(nullable=false)
     */
    protected User $owner;

    /**
     * @Column
     */
    protected string $action;

    /**
     * @Column
     */
    protected string $name;

    /**
     * @Column
     */
    protected string $minute;

    /**
     * @Column
     */
    protected string $hour;

    /**
     * @Column
     */
    protected string $dayOfMonth;

    /**
     * @Column
     */
    protected string $month;

    /**
     * @Column
     */
    protected string $dayOfWeek;

    /**
     * @Column(type="boolean", options={"default": 0})
     */
    protected bool $isActive = false;

    /**
     * @Column(type="boolean", options={"default": 0})
     */
    protected bool $isSystem = false;

    /**
     * @Column(type="integer", options={"default": 1})
     */
    protected int $runHistorySize = 1;

    /**
     * @Column(type="datetime")
     */
    protected DateTime $scheduled;

    /**
     * @Column(type="datetime")
     */
    protected DateTime $created;

    /**
     * @Column(type="datetime")
     */
    protected DateTime $modified;

    /**
     * @Column(type="json_array")
     */
    protected array $settings;

    /**
     * @OneToMany(
     *     targetEntity="Cronical\Entity\ScheduledActionRun",
     *     mappedBy="scheduledAction",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove", "detach"},
     *     fetch="EXTRA_LAZY",
     *     indexBy="id"
     * )
     * @OrderBy({"started" = "DESC"})
     */
    protected Collection $runs;

    public function __construct()
    {
        $this->runs = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setMinute(string $minute): void
    {
        $this->minute = $minute;
    }

    public function getMinute(): string
    {
        return $this->minute;
    }

    public function setHour(string $hour): void
    {
        $this->hour = $hour;
    }

    public function getHour(): string
    {
        return $this->hour;
    }

    public function setDayOfMonth(string $dayOfMonth): void
    {
        $this->dayOfMonth = $dayOfMonth;
    }

    public function getDayOfMonth(): string
    {
        return $this->dayOfMonth;
    }

    public function setMonth(string $month): void
    {
        $this->month = $month;
    }

    public function getMonth(): string
    {
        return $this->month;
    }

    public function setDayOfWeek(string $dayOfWeek): void
    {
        $this->dayOfWeek = $dayOfWeek;
    }

    public function getDayOfWeek(): string
    {
        return $this->dayOfWeek;
    }

    public function getCronExpression(): string
    {
        $cronExpression = sprintf(
            '%s %s %s %s %s',
            $this->getMinute(),
            $this->getHour(),
            $this->getDayOfMonth(),
            $this->getMonth(),
            $this->getDayOfWeek()
        );

        return $cronExpression;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsSystem(bool $isSystem): void
    {
        $this->isSystem = $isSystem;
    }

    public function getIsSystem(): bool
    {
        return $this->isSystem;
    }

    public function setRunHistorySize(int $runHistorySize): void
    {
        $this->runHistorySize = $runHistorySize;
    }

    public function getRunHistorySize(): int
    {
        return $this->runHistorySize;
    }

    public function setScheduled(DateTime $scheduled): void
    {
        $this->scheduled = $scheduled;
    }

    public function getScheduled(): DateTime
    {
        return $this->scheduled;
    }

    public function setCreated(DateTime $created): void
    {
        $this->created = $created;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setModified(DateTime $modified): void
    {
        $this->modified = $modified;
    }

    public function getModified(): DateTime
    {
        return $this->modified;
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

    public function getRuns(): Collection
    {
        return $this->runs;
    }
}
