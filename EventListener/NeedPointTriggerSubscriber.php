<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use MauticPlugin\KompulseNeedBundle\Event as Events;
use MauticPlugin\KompulseNeedBundle\KompulseNeedEvents;
use MauticPlugin\KompulseNeedBundle\Model\TriggerModel;

/**
 * Class NeedPointTriggerSubscriber.
 */
class NeedPointTriggerSubscriber extends CommonSubscriber
{

    /**
     * @var TriggerModel
     */
    protected $triggerModel;

    /**
     * LeadSubscriber constructor.
     *
     * @param TriggerModel $triggerModel
     */
    public function __construct(TriggerModel $triggerModel)
    {
        $this->triggerModel = $triggerModel;
    }


    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KompulseNeedEvents::TRIGGER_ON_BUILD => ['onTriggerBuild', 0],
            KompulseNeedEvents::CONTACT_NEED_POINTS_CHANGE => ['onContactNeedPointsChange', 0],
        ];
    }

    /**
     * @param TriggerBuilderEvent $event
     */
    public function onTriggerBuild(Events\TriggerBuilderEvent $event)
    {
        $changeLists = [
            'group'    => 'mautic.lead.point.trigger',
            'label'    => 'mautic.lead.point.trigger.changelists',
            'callback' => ['\\Mautic\\LeadBundle\\Helper\\PointEventHelper', 'changeLists'],
            'formType' => 'leadlist_action',
        ];

        $event->addEvent('lead.changelists', $changeLists);

        // modify tags
        $action = [
            'group'    => 'mautic.lead.point.trigger',
            'label'    => 'mautic.lead.lead.events.changetags',
            'formType' => 'modify_lead_tags',
            'callback' => '\Mautic\LeadBundle\Helper\EventHelper::updateTags',
        ];
        $event->addEvent('lead.changetags', $action);

        // from CampaignBundle/EL/PointSubscriber
        $changeLists = [
            'group'    => 'mautic.campaign.point.trigger',
            'label'    => 'mautic.campaign.point.trigger.changecampaigns',
            'callback' => ['\\Mautic\\CampaignBundle\\Helper\\CampaignEventHelper', 'addRemoveLead'],
            'formType' => 'campaignevent_addremovelead',
        ];

        $event->addEvent('campaign.changecampaign', $changeLists);


        // from EmailBundle/EL/PointSubscriber
        $sendEvent = [
            'group'           => 'mautic.email.point.trigger',
            'label'           => 'mautic.email.point.trigger.sendemail',
            'callback'        => ['\\Mautic\\EmailBundle\\Helper\\PointEventHelper', 'sendEmail'],
            'formType'        => 'emailsend_list',
            'formTypeOptions' => ['update_select' => 'pointtriggerevent_properties_email'],
            'formTheme'       => 'MauticEmailBundle:FormTheme\EmailSendList',
        ];

        $event->addEvent('email.send', $sendEvent);
    }

    /**
     *
     */
    public function onContactNeedPointsChange(Events\NeedPointsChangeEvent $event)
    {
        $this->triggerModel->triggerEvents($event->getContactNeed());
    }
}
