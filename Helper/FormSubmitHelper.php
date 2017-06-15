<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle\Helper;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\FormBundle\Entity\Action;
use MauticPlugin\KompulseNeedBundle\Entity\ContactNeed;
use Mautic\LeadBundle\Entity\Lead;

/**
 * Class FormSubmitHelper.
 * // inspired from EmailBundle\Helper\FormSubmitHelper
 */
class FormSubmitHelper
{
    /**
     * @param               $tokens
     * @param Action        $action
     * @param MauticFactory $factory
     * @param               $feedback
     */
    public static function adjustContactNeed($tokens, Action $action, MauticFactory $factory, $feedback)
    {
        $properties = $action->getProperties();
        $needId     = (isset($properties['need']))?   (int) $properties['need']     : null;
        $points     = (isset($properties['points']))? (int) $properties['points']   : null;
        $operator   = (isset($properties['operator']))?     $properties['operator'] : null;

        $form       = $action->getForm();

        /** @var \Mautic\LeadBundle\Model\LeadModel $leadModel */
        $leadModel = $factory->getModel('lead');

        $currentLead = $leadModel->getCurrentLead();

        if ($currentLead instanceof Lead && $needId !== null && $points !== null) {
            $needModel        = $factory->getModel('kompulse.need');
            $contactNeedModel = $factory->getModel('kompulse.contactneed');

            $lead        = $currentLead;
            $need        = $needModel->getEntity($needId);
            $contactNeed = $contactNeedModel->createOrFindBy($lead, $need);
            $contactNeed->adjustPoints($points, $operator);

            $contactNeedModel->saveEntity($contactNeed);
        }
    }
}
