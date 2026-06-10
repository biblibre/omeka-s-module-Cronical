<?php

namespace Cronical;

return [
    'api_adapters' => [
        'invokables' => [
            'cronical_scheduled_actions' => Api\Adapter\ScheduledActionAdapter::class,
            'cronical_scheduled_action_runs' => Api\Adapter\ScheduledActionRunAdapter::class,
        ],
    ],
    'browse_defaults' => [
        'admin' => [
            'cronical_scheduled_actions' => [
                'sort_by' => 'name',
                'sort_order' => 'asc',
            ],
            'cronical_scheduled_action_runs' => [
                'sort_by' => 'started',
                'sort_order' => 'desc',
            ],
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            'cronical' => Service\Mvc\Controller\Plugin\CronicalFactory::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
            'Cronical\Controller\Admin\ScheduledAction' => Controller\Admin\ScheduledActionController::class,
            'Cronical\Controller\Admin\ScheduledActionRun' => Controller\Admin\ScheduledActionRunController::class,
        ],
    ],
    'cronical_actions' => [
        'initializers' => [
            Action\Initializer\JobDispatch::class,
            Action\Initializer\Logger::class,
        ],
        'invokables' => [
            'Cronical\Action\IndexFulltextSearch' => Action\IndexFulltextSearch::class,
            'Cronical\Action\Heartbeat' => Action\Heartbeat::class,
        ],
    ],
    'entity_manager' => [
        'mapping_classes_paths' => [
            dirname(__DIR__) . '/src/Entity',
        ],
        'proxy_paths' => [
            dirname(__DIR__) . '/data/doctrine-proxies',
        ],
    ],
    'form_elements' => [
        'invokables' => [
            'Cronical\Form\ScheduledActionBatchUpdateForm' => Form\ScheduledActionBatchUpdateForm::class,
        ],
        'factories' => [
            'Cronical\Form\ScheduledActionAddForm' => Service\Form\ScheduledActionAddFormFactory::class,
            'Cronical\Form\ScheduledActionEditForm' => Service\Form\ScheduledActionEditFormFactory::class,
            'Cronical\Form\Element\ActionSelect' => Service\Form\Element\ActionSelectFactory::class,
        ],
    ],
    'navigation' => [
        'AdminModule' => [
            [
                'label' => 'Scheduled actions', // @translate
                'route' => 'admin/cronical/default',
                'controller' => 'scheduled-action',
                'resource' => 'Cronical\Controller\Admin\ScheduledAction',
                'privilege' => 'browse',
                'class' => 'cronical',
                'pages' => [
                    [
                        'route' => 'admin/cronical/default',
                        'controller' => 'scheduled-action',
                        'visible' => false,
                    ],
                    [
                        'route' => 'admin/cronical/id',
                        'controller' => 'scheduled-action',
                        'visible' => false,
                    ],
                    [
                        'label' => 'Scheduled actions runs', // @translate
                        'route' => 'admin/cronical/default',
                        'controller' => 'scheduled-action-run',
                        'resource' => 'Cronical\Controller\Admin\ScheduledActionRun',
                        'privilege' => 'browse',
                        'pages' => [
                            [
                                'route' => 'admin/cronical/default',
                                'controller' => 'scheduled-action-run',
                                'visible' => false,
                            ],
                            [
                                'route' => 'admin/cronical/id',
                                'controller' => 'scheduled-action-run',
                                'visible' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'cronical' => [
                        'type' => \Laminas\Router\Http\Literal::class,
                        'options' => [
                            'route' => '/cronical',
                            'defaults' => [
                                '__NAMESPACE__' => 'Cronical\Controller\Admin',
                            ],
                        ],
                        'child_routes' => [
                            'default' => [
                                'type' => \Laminas\Router\Http\Segment::class,
                                'options' => [
                                    'route' => '/:controller[/:action]',
                                    'constraints' => [
                                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ],
                                    'defaults' => [
                                        'action' => 'browse',
                                    ],
                                ],
                            ],
                            'id' => [
                                'type' => \Laminas\Router\Http\Segment::class,
                                'options' => [
                                    'route' => '/:controller/:id[/:action]',
                                    'constraints' => [
                                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                        'id' => '\d+',
                                    ],
                                    'defaults' => [
                                        'action' => 'show',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'Cronical\ActionManager' => Service\Action\ManagerFactory::class,
        ],
    ],
    'sort_defaults' => [
        'admin' => [
            'cronical_scheduled_actions' => [
                'name' => 'Name', // @translate
                'latest_run_started' => 'Latest run', // @translate
                'scheduled' => 'Next run', // @translate
            ],
            'cronical_scheduled_action_runs' => [
                'started' => 'Started', // @translate
                'ended' => 'Ended', // @translate
            ],
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => dirname(__DIR__) . '/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'cronical' => Service\View\Helper\CronicalFactory::class,
        ],
        'invokables' => [
            'cronicalScheduledActionSearchFilters' => View\Helper\ScheduledActionSearchFilters::class,
            'cronicalScheduledActionRunSearchFilters' => View\Helper\ScheduledActionRunSearchFilters::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
];
