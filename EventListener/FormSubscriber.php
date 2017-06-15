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
use Mautic\FormBundle\Event\FormBuilderEvent;
use Mautic\FormBundle\FormEvents;

/**
 * Class FormSubscriber.
 * inspired from EmailBundle/FormSubscriber
 */
class FormSubscriber extends CommonSubscriber
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::FORM_ON_BUILD => ['onFormBuilder', 0],
        ];
    }

    /**
     * Add a send email actions to available form submit actions.
     *
     * @param FormBuilderEvent $event
     */
    public function onFormBuilder(FormBuilderEvent $event)
    {
        // Add form submit actions
        // Send email to user
        $action = [
            'group'             => 'kompulse.contact_need.actions',
            'label'             => 'kompulse.contact_need.form.action.adjust_points',
            'description'       => 'kompulse.contact_need.form.action.descr',
            'formType'          => 'kompulse_need_submitaction_contact_need',
            'callback'          => '\MauticPlugin\KompulseNeedBundle\Helper\FormSubmitHelper::adjustContactNeed',
            'allowCampaignForm' => true,
        ];

        $event->addSubmitAction('kompulse.contact_need.adjust', $action);
    }
}
