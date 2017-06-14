<?php

return [
    'name'        => 'Kompulse',
    'description' => 'Need scoring.',
    'version'     => '0.1',
    'author'      => 'Kgtech',
    'parameters' => array(
        'licence_key' => '',
    ),
    'routes'      => [
        'main' => array(
            'plugin_kompulse_index' => [
                'path'       => '/need/{page}',
                'controller' => 'KompulseNeedBundle:Need:index',
            ],
            'kompulse_need_index' => [
                'path'       => '/need/{page}',
                'controller' => 'KompulseNeedBundle:Need:index',
            ],
            'kompulse_need_action' => [
                'path'       => '/need/{objectAction}/{objectId}',
                'controller' => 'KompulseNeedBundle:Need:execute',
            ],
            'mautic_contactneed_action' => [
                'path'         => '/contacts/kompulse/{leadId}/{objectAction}/{objectId}',
                'controller'   => 'KompulseNeedBundle:ContactNeed:executeNeed',
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            'plugin_kompulse_list'  => array(
                'path'       => '/kompulse/{page}',
                'controller' => 'KompulseNeedBundle:Default:index'
             ),
            'plugin_kompulse_admin' => array(
                'path'       => '/kompulse/admin',
                'controller' => 'KompulseNeedBundle:Default:admin'
            ),

            'kompulse_pointtriggerevent_action' => [
                'path'       => '/kompulse/needpoints/triggers/events/{objectAction}/{objectId}',
                'controller' => 'KompulseNeedBundle:TriggerEvent:execute',
            ],
            'kompulse_pointtrigger_index' => [
                'path'       => '/kompulse/needpoints/triggers/{page}',
                'controller' => 'KompulseNeedBundle:Trigger:index',
            ],
            'kompulse_pointtrigger_action' => [
                'path'       => '/kompulse/needpoints/triggers/{objectAction}/{objectId}',
                'controller' => 'KompulseNeedBundle:Trigger:execute',
            ],
            'kompulse_point_action_index' => [
                'path'       => '/kompulse/needpoints/actions/{page}',
                'controller' => 'KompulseNeedBundle:NeedPointAction:index',
            ],
            'kompulse_point_action_action' => [
                'path'       => '/kompulse/needpoints/actions/{objectAction}/{objectId}',
                'controller' => 'KompulseNeedBundle:NeedPointAction:execute',
            ],
        ),
    ],
    'menu' => [
        'main' => [
            'kompulse.need_points.menu.root' => [
                'id'        => 'kompulse_need_points_root',
                'iconClass' => 'fa-calculator',
                'access'    => ['point:points:view', 'point:triggers:view'],
                'priority'  => 30,
                'children'  => [
                    'mautic.point.menu.index' => [
                        'route'  => 'kompulse_point_action_index',
                        'access' => 'point:points:view',
                    ],
                    'mautic.point.trigger.menu.index' => [
                        'route'  => 'kompulse_pointtrigger_index',
                        'access' => 'point:triggers:view',
                    ],
                ],
            ],
        ],
        'admin' => [
            'kompulse.admin_menu.index' => [
                'route'     => 'plugin_kompulse_index',
                'iconClass' => 'fa-folder',
                'id'        => 'plugin_kompulse_need_index',
            ],
        ],
    ],
    'services' => [
        'events' => [
            'kompulse.contact_need.subscriber' => [
                'class'     => 'MauticPlugin\KompulseNeedBundle\EventListener\ContactNeedSubscriber',
                'arguments' => [
                    'mautic.kompulse.model.trigger',
                    'mautic.helper.ip_lookup',
                    'mautic.core.model.auditlog',
                ],
            ],
            // licence key parameter related
            // 'mautic.kompulse.configbundle.subscriber' => [
            //     'class' => 'MauticPlugin\KompulseNeedBundle\EventListener\ConfigSubscriber',
            // ],
            'mautic.kompulse.lead_subscriber' => [
                'class' => 'MauticPlugin\KompulseNeedBundle\EventListener\LeadSubscriber',
                'arguments' => ['mautic.kompulse.model.need', 'translator'],
            ],
            'mautic.kompulse.lead_content_subscriber' => [
                'class' => 'MauticPlugin\KompulseNeedBundle\EventListener\CustomContentSubscriber',
                'arguments' => [ 'doctrine.orm.default_entity_manager', 'mautic.security' ]
            ],
            'mautic.kompulse.campaign.subscriber' => [
                'class'     => 'MauticPlugin\KompulseNeedBundle\EventListener\CampaignSubscriber',
                'arguments' => [
                    'mautic.helper.ip_lookup',
                    'mautic.kompulse.model.contactneed',
                    'mautic.kompulse.model.need',
                ],
            ],
            'mautic.kompulse.campaign.event_form.subscriber' => [
                'class' => 'MauticPlugin\KompulseNeedBundle\EventListener\CampaignEventFormSubscriber',
            ],
            'mautic.kompulse.reportbundle.subscriber' => [
                'class'     => 'MauticPlugin\KompulseNeedBundle\EventListener\ReportSubscriber',
                'arguments' => [
                    'mautic.lead.model.list',
                    'mautic.lead.model.field',
                ],
            ],
            'kompulse.needpoint.subscriber' => [
                'class' => 'MauticPlugin\KompulseNeedBundle\EventListener\NeedPointTriggerSubscriber',
                'arguments' => ['mautic.kompulse.model.trigger'],
            ],
            'kompulse.needpoint_action.subscriber' => [
                'class'  => 'MauticPlugin\KompulseNeedBundle\EventListener\NeedPointActionSubscriber',
                'arguments' => ['mautic.kompulse.model.need_point']
            ],
        ],
        'forms' => [
            'mautic.kompulse.config' => [
                'class'     => 'MauticPlugin\KompulseNeedBundle\Form\Type\ConfigType',
                'alias'     => 'kompulse_config',
            ],
            // bad direction
            // 'mautic.kompulse.form.fieldslist.needlist' => [
            //     'class' => 'MauticPlugin\KompulseNeedBundle\Form\Type\NeedListType',
            //     'alias' => 'need_list',
            // ],
            'mautic.kompulse.form.contactneed' => [
                'class' => 'MauticPlugin\KompulseNeedBundle\Form\Type\ContactNeedType',
                'alias' => 'contactneed',
            ],
            'mautic.kompulse.form.need' => [
                'class' => 'MauticPlugin\KompulseNeedBundle\Form\Type\NeedType',
                'alias' => 'need',
            ],
            'mautic.kompulse.form.contactneed_points_action' => [
                'class' => 'MauticPlugin\KompulseNeedBundle\Form\Type\PointActionType',
                'alias' => 'contactneed_points_action',
                'arguments' => [ 'mautic.kompulse.model.need']

            ],
            // 'mautic.kompulse.form.campaign_event.extension' => [
            //     'class' => 'MauticPlugin\KompulseNeedBundle\Form\Extension\CampaignEventTypeExtension',
            //     'tags' => [
            //         'name' => 'form.type_extension',
            //         'extended_type' => 'Mautic\CampaignBundle\Form\Type\EventType',
            //         'alias' => 'Mautic\CampaignBundle\Form\Type\EventType',
            //     ]
            // ],
            'mautic.kompulse.form.campaignevent_contact_need_field_value' => [
                'class' => 'MauticPlugin\KompulseNeedBundle\Form\Type\CampaignEventContactNeedFieldValueType',
                'alias' => 'campaignevent_contact_need_field_value',
                'arguments' => ['mautic.factory'],

            ],
            'kompulse.need_point.type.form' => [
                'class'     => 'MauticPlugin\KompulseNeedBundle\Form\Type\NeedPointType',
                'arguments' => 'mautic.factory',
                'alias'     => 'need_point',
            ],
            'kompulse.trigger.type.form' => [
                'class'     => 'MauticPlugin\KompulseNeedBundle\Form\Type\TriggerType',
                'arguments' => [ 'mautic.factory', 'mautic.kompulse.model.need'],
                'alias'     => 'kompulsetrigger',
            ],
        ],
        'models' => [
            'mautic.kompulse.model.need' => [
                'class'     => 'MauticPlugin\KompulseNeedBundle\Model\NeedModel',
                'arguments' => [
                    'request_stack',
                ],
            ],
            'mautic.kompulse.model.contactneed' => [
                'class'     => 'MauticPlugin\KompulseNeedBundle\Model\ContactNeedModel',
                'arguments' => [
                    'request_stack',
                ],
            ],
            'mautic.kompulse.model.need_point' => [
                'class'     => 'MauticPlugin\KompulseNeedBundle\Model\NeedPointModel',
                'arguments' => [
                    'session',
                    'mautic.helper.ip_lookup',
                    'mautic.lead.model.lead',
                    'mautic.kompulse.model.contactneed',
                ],
            ],
            'mautic.kompulse.model.triggerevent' => [
                'class' => 'MauticPlugin\KompulseNeedBundle\Model\TriggerEventModel',
            ],
            'mautic.kompulse.model.trigger' => [
                'class'     => 'MauticPlugin\KompulseNeedBundle\Model\TriggerModel',
                'arguments' => [
                    'mautic.helper.ip_lookup',
                    'mautic.kompulse.model.contactneed',
                    'mautic.kompulse.model.triggerevent'
                ],
            ],
        ],
    ],
];
