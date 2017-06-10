<?php

/*
 * @author      Kgtech
 *
 * @link        https://www.kgtech.fi
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle\EventListener;

use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Event\CampaignBuilderEvent;
use Mautic\CampaignBundle\Event\CampaignExecutionEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Helper\IpLookupHelper;
use Mautic\LeadBundle\Entity\PointsChangeLog;
use Mautic\LeadBundle\LeadEvents;
use Mautic\LeadBundle\Model\FieldModel;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\KompulseNeedBundle\KompulseNeedEvents;
use MauticPlugin\KompulseNeedBundle\Model\NeedModel;
use MauticPlugin\KompulseNeedBundle\Model\ContactNeedModel;

/**
 * Class CampaignSubscriber.
 */
class CampaignSubscriber extends CommonSubscriber
{
    /**
     * @var IpLookupHelper
     */
    protected $ipLookupHelper;

    /**
     * @var ContactNeedModel
     */
    protected $contactNeedModel;

    /**
     * @var NeedModel
     */
    protected $needModel;

    /**
     * CampaignSubscriber constructor.
     *
     * @param IpLookupHelper $ipLookupHelper
     * @param LeadModel      $leadModel
     * @param FieldModel     $leadFieldModel
     */
    public function __construct(IpLookupHelper $ipLookupHelper, ContactNeedModel $contactNeedModel, NeedModel $needModel)
    {
        $this->ipLookupHelper   = $ipLookupHelper;
        $this->contactNeedModel = $contactNeedModel;
        $this->needModel        = $needModel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD      => ['onCampaignBuild', 0],
            LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION => [
                ['onCampaignTriggerActionChangeContactNeedPoints', 0],
            ],
            KompulseNeedEvents::ON_CAMPAIGN_TRIGGER_CONDITION => ['onCampaignTriggerCondition', 0],
        ];
    }

    /**
     * Add event triggers and actions.
     *
     * @param CampaignBuilderEvent $event
     */
    public function onCampaignBuild(CampaignBuilderEvent $event)
    {
        //Add actions
        $action = [
            'label'       => 'kompulse.contact_need.events.changepoints',
            'description' => 'kompulse.contact_need.events.changepoints_descr',
            'formType'    => 'contactneed_points_action',
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
        ];
        $event->addAction('contactneed.changepoints', $action);

        $trigger = [
            'label'       => 'kompulse.contact_need.events.field_value',
            'description' => 'kompulse.contact_need.events.field_value_descr',
            'formType'    => 'campaignevent_contact_need_field_value',
            'formTheme'   => 'MauticLeadBundle:FormTheme\FieldValueCondition',
            'eventName'   => KompulseNeedEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
        ];
        $event->addCondition('contact_need.field_value', $trigger);
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerActionChangeContactNeedPoints(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('contactneed.changepoints')) {
            return;
        }

        $lead   = $event->getLead();
        $points = $event->getConfig()['points'];
        $need   = $this->needModel->getEntity($event->getConfig()['need']);

        $somethingHappened = false;

        if ($lead !== null && !empty($points)) {
            $contactNeed = $this->contactNeedModel->createOrFindBy($lead, $need);
            $contactNeed->adjustPoints($points);
            $this->contactNeedModel->saveEntity($contactNeed);
            $somethingHappened = true;
        }

        return $event->setResult($somethingHappened);
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerCondition(CampaignExecutionEvent $event)
    {
        $lead = $event->getLead();

        if (!$lead || !$lead->getId()) {
            return $event->setResult(false);
        }

        $operators = $this->needModel->getFilterExpressionFunctions();

        $result = $this->contactNeedModel
            ->getRepository()
            ->findByConditions(
                $lead,
                $event->getConfig()['field'],
                $event->getConfig()['value'],
                $operators[$event->getConfig()['operator']]['expr']
            );

        return $event->setResult($result);
    }
}
