<?php

namespace Cronical;

use Omeka\Module\AbstractModule;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;

class Module extends AbstractModule
{
    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $serviceLocator = $this->getServiceLocator();
        $config = $serviceLocator->get('Config');
        $acl = $serviceLocator->get('Omeka\Acl');
        $em = $serviceLocator->get('Omeka\EntityManager');

        // Deny Cronical access to site admins
        $acl->deny('site_admin', [
            'Cronical\Controller\Admin\ScheduledAction',
            'Cronical\Controller\Admin\ScheduledActionRun',
            'Cronical\Api\Adapter\ScheduledActionAdapter',
            'Cronical\Api\Adapter\ScheduledActionRunAdapter',
            'Cronical\Entity\ScheduledAction',
            'Cronical\Entity\ScheduledActionRun',
        ]);

        // Scheduled action runs are only created and updated by bin/cronical,
        // which does not use the API
        // Roles must be listed explicitly: allow('global_admin') creates a
        // rule on every resource, which takes precedence over deny(null, ...)
        $acl->deny($acl->getRoles(), [
            'Cronical\Api\Adapter\ScheduledActionRunAdapter',
            'Cronical\Entity\ScheduledActionRun',
        ], ['create', 'update', 'batch_update']);

        // Forbid minute and hour ranges even for global administrators
        // This privilege is only given when using the script bin/schedule-add
        $acl->deny('global_admin', 'Cronical\Entity\ScheduledAction', 'schedule-anything');
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach('*', 'view.layout', [$this, 'onViewLayout']);

        $sharedEventManager->attach(
            'Omeka\Api\Adapter\JobAdapter',
            'api.search.query',
            [$this, 'onApiSearchQuery']
        );

        $sharedEventManager->attach(
            'Cronical\Controller\Admin\ScheduledAction',
            'view.show.after',
            [$this, 'onScheduledActionViewShow']
        );
        $sharedEventManager->attach(
            'Cronical\Controller\Admin\ScheduledAction',
            'view.details',
            [$this, 'onScheduledActionViewDetails']
        );
    }

    public function getConfigForm(PhpRenderer $renderer)
    {
    }

    public function handleConfigForm(AbstractController $controller)
    {
    }

    public function onViewLayout(Event $event)
    {
        $view = $event->getTarget();
        if ($view->status()->isAdminRequest()) {
            $view->headLink()->appendStylesheet($view->assetUrl('css/admin/cronical.css', 'Cronical'));
        }
    }

    public function onApiSearchQuery(Event $event)
    {
        $status = $this->getServiceLocator()->get('Omeka\Status');
        if (!$status->isAdminRequest()) {
            return;
        }

        $adapter = $event->getTarget();
        $qb = $event->getParam('queryBuilder');
        $request = $event->getParam('request');

        $ids = $request->getValue('cronical_scheduled_action_id', []);
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $ids = array_filter(array_map('intval', array_filter($ids, 'is_scalar')));
        if ($ids && $adapter->getEntityClass() === 'Omeka\Entity\Job') {
            $runAlias = $adapter->createAlias();
            $subQb = $adapter->getEntityManager()->createQueryBuilder();
            $subQb->select($runAlias)
                  ->from('Cronical\Entity\ScheduledActionRun', $runAlias)
                  ->where($subQb->expr()->in("$runAlias.scheduledAction", $adapter->createNamedParameter($qb, $ids)))
                  ->andWhere("$runAlias.job = omeka_root");
            $qb->andWhere($qb->expr()->exists($subQb->getDQL()));
        }
    }

    public function onScheduledActionViewShow(Event $event): void
    {
        $serviceLocator = $this->getServiceLocator();
        $actionManager = $serviceLocator->get('Cronical\ActionManager');

        $view = $event->getTarget();

        $scheduledAction = $view->get('scheduledAction');
        $action = $actionManager->get($scheduledAction->action());

        $action->onViewShow($view, $scheduledAction);
    }

    public function onScheduledActionViewDetails(Event $event): void
    {
        $serviceLocator = $this->getServiceLocator();
        $actionManager = $serviceLocator->get('Cronical\ActionManager');

        $view = $event->getTarget();

        $scheduledAction = $view->get('resource');
        $action = $actionManager->get($scheduledAction->action());

        $action->onViewDetails($view, $scheduledAction);
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');

        $connection->executeStatement(<<<'SQL'
            CREATE TABLE cronical_scheduled_action_run (
                id INT AUTO_INCREMENT NOT NULL,
                scheduled_action_id INT NOT NULL,
                owner_id INT DEFAULT NULL,
                job_id INT DEFAULT NULL,
                status VARCHAR(255) NOT NULL,
                started DATETIME NOT NULL,
                ended DATETIME DEFAULT NULL,
                settings LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)',
                INDEX IDX_54DF70748B2FEA32 (scheduled_action_id),
                INDEX IDX_54DF70747E3C61F9 (owner_id),
                INDEX IDX_54DF7074BE04EA9 (job_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $connection->executeStatement(<<<'SQL'
            CREATE TABLE cronical_scheduled_action (
                id INT AUTO_INCREMENT NOT NULL,
                owner_id INT NOT NULL,
                action VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                minute VARCHAR(255) NOT NULL,
                hour VARCHAR(255) NOT NULL,
                day_of_month VARCHAR(255) NOT NULL,
                month VARCHAR(255) NOT NULL,
                day_of_week VARCHAR(255) NOT NULL,
                is_active TINYINT(1) DEFAULT '0' NOT NULL,
                is_system TINYINT(1) DEFAULT '0' NOT NULL,
                run_history_size INT DEFAULT 1 NOT NULL,
                scheduled DATETIME NOT NULL,
                created DATETIME NOT NULL,
                modified DATETIME NOT NULL,
                settings LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)',
                INDEX IDX_C4DBA10A7E3C61F9 (owner_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $connection->executeStatement(<<<'SQL'
            ALTER TABLE cronical_scheduled_action_run
            ADD CONSTRAINT FK_54DF70748B2FEA32 FOREIGN KEY (scheduled_action_id) REFERENCES cronical_scheduled_action (id)
            ON DELETE CASCADE
        SQL);

        $connection->executeStatement(<<<'SQL'
            ALTER TABLE cronical_scheduled_action_run
            ADD CONSTRAINT FK_54DF70747E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)
            ON DELETE SET NULL
        SQL);

        $connection->executeStatement(<<<'SQL'
            ALTER TABLE cronical_scheduled_action_run
            ADD CONSTRAINT FK_54DF7074BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id)
        SQL);

        $connection->executeStatement(<<<'SQL'
            ALTER TABLE cronical_scheduled_action
            ADD CONSTRAINT FK_C4DBA10A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)
        SQL);
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('Omeka\Connection');

        $connection->executeStatement('DROP TABLE cronical_scheduled_action_run');
        $connection->executeStatement('DROP TABLE cronical_scheduled_action');
    }

    public function upgrade($oldVersion, $newVersion, ServiceLocatorInterface $serviceLocator)
    {
    }

    public function getConfig()
    {
        return require __DIR__ . '/config/module.config.php';
    }
}
