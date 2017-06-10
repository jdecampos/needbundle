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
use Mautic\CoreBundle\Helper\IpLookupHelper;
use Mautic\CoreBundle\Model\AuditLogModel;
use MauticPlugin\KompulseNeedBundle\Event as Events;
use MauticPlugin\KompulseNeedBundle\Model\TriggerModel;
use MauticPlugin\KompulseNeedBundle\KompulseNeedEvents;

/**
 * Class ContactNeedSubscriber.
 */
class ContactNeedSubscriber extends CommonSubscriber
{
    /**
     * @var TriggerModel
     */
    protected $triggerModel;

    /**
     * @var AuditLogModel
     */
    protected $auditLogModel;

    /**
     * @var IpLookupHelper
     */
    protected $ipLookupHelper;

    /**
     * LeadSubscriber constructor.
     *
     * @param TriggerModel $triggerModel
     */
    public function __construct(
        TriggerModel $triggerModel,
        IpLookupHelper $ipLookupHelper,
        AuditLogModel $auditLogModel
    )
    {
        $this->triggerModel = $triggerModel;
        $this->ipLookupHelper = $ipLookupHelper;
        $this->auditLogModel  = $auditLogModel;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KompulseNeedEvents::CONTACT_NEED_POINTS_CHANGE   => ['onContactNeedPointsChange', 0],
            KompulseNeedEvents::CONTACT_NEED_POST_SAVE       => ['onContactNeedPostSave', 0],
        ];
    }

    /**
     * based on LeadBundle\EventListener\LeadSubscriber:OnLeadPostSave
     */
    public function onContactNeedPostSave(Events\ContactNeedEvent $event)
    {
        //Because there is an event within an event, there is a risk that something will trigger a loop which
        //needs to be prevented
        static $preventLoop = [];

        $contactNeed = $event->getContactNeed();

        if ($details = $event->getChanges()) {
            // Unset dateLastActive to prevent un-necessary audit log entries
            unset($details['dateLastActive']);
            if (empty($details)) {
                return;
            }

            $check = base64_encode($contactNeed->getId().serialize($details));
            if (!in_array($check, $preventLoop)) {
                $preventLoop[] = $check;

                $log = [
                    'bundle'    => 'kompulse',
                    'object'    => 'contactNeed',
                    'objectId'  => $contactNeed->getId(),
                    'action'    => ($event->isNew()) ? 'create' : 'update',
                    'details'   => $details,
                    'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
                ];
                $this->auditLogModel->writeToLog($log);


                //add if an ip was added
                if (isset($details['ipAddresses']) && !empty($details['ipAddresses'][1])) {
                    $log = [
                        'bundle'    => 'lead',
                        'object'    => 'lead',
                        'objectId'  => $lead->getId(),
                        'action'    => 'ipadded',
                        'details'   => $details['ipAddresses'],
                        'ipAddress' => $this->request->server->get('REMOTE_ADDR'),
                    ];
                    $this->auditLogModel->writeToLog($log);
                }

                //trigger the points change event
                if (isset($details['points']) && (int) $details['points'][1] > 0) {
                    if ($this->dispatcher->hasListeners(KompulseNeedEvents::CONTACT_NEED_POINTS_CHANGE)) {
                        $pointsEvent = new Events\NeedPointsChangeEvent($contactNeed, $details['points'][0], $details['points'][1]);
                        $this->dispatcher->dispatch(KompulseNeedEvents::CONTACT_NEED_POINTS_CHANGE, $pointsEvent);
                    }
                }
            }
        }
    }

    public function onContactNeedPointsChange(Events\NeedPointsChangeEvent $event)
    {
        $this->triggerModel->triggerEvents($event->getContactNeed());
    }
}
