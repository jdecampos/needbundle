<?php

/*
 * @author      Kgtech
 *
 * @link        https://www.kgtech.fi
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\CustomContentEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use MauticPlugin\KompulseNeedBundle\Entity\ContactNeed;

/**
 * Class LeadSubscriber.
 */
class CustomContentSubscriber extends CommonSubscriber
{
    /** @var EntityManager */
    protected $em;

    /** @var CorePermissions */
    protected $security;

    public function __construct(EntityManager $em, CorePermissions $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_CONTENT   => ['injectCustomContent'],
        ];
    }

    public function injectCustomContent(CustomContentEvent $event)
    {
        $parameters = $event->getVars();

        if ($event->checkContext('MauticLeadBundle:Lead:lead.html.php', 'tabs')) {
            if (isset($parameters['lead'])) {
                $lead = $parameters['lead'];
                $contactNeeds = $this->em->getRepository('KompulseNeedBundle:ContactNeed')->findByLead($lead);
                $event->addTemplate('KompulseNeedBundle:ContactNeed:tab.html.php', [
                    'countContactNeeds' => count($contactNeeds),
                ]);
            }
        }

        if ($event->checkContext('MauticLeadBundle:Lead:lead.html.php', 'tabs.content')) {
            if (isset($parameters['lead'])) {
                $lead = $parameters['lead'];
                $permissions = [
                    'edit'   => $this->security->hasEntityAccess('lead:leads:editown', 'lead:leads:editother', $lead->getPermissionUser()),
                    'delete' => $this->security->hasEntityAccess('lead:leads:deleteown', 'lead:leads:deleteown', $lead->getPermissionUser()),
                ];
                $contactNeeds = $this->em->getRepository('KompulseNeedBundle:ContactNeed')->findByLead($lead);

                $event->addTemplate('KompulseNeedBundle:ContactNeed:tab.content.html.php', [
                    'contactNeeds'      => $contactNeeds,
                    'tmpl'              => 'index',
                    'permissions'       => $permissions,
                ]);
            }
        }
    }

}
