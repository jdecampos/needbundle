<?php

namespace MauticPlugin\KompulseNeedBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\FormBundle\Event as Events;
use Mautic\FormBundle\FormEvents;

/**
 * Class FormSubscriber
 */
class CampaignEventFormSubscriber extends CommonSubscriber
{

    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return array(
            FormEvents::FORM_ON_BUILD => array('onFormBuilder', 0)
        );
    }

    /**
     * Add a simple email form
     *
     * @param FormBuilderEvent $event
     */
    public function onFormBuilder(Events\FormBuilderEvent $event)
    {
        // Register a custom form field
        $event->addFormField(
            'kompulse.need.select', array(
                'label'    => 'kompulse.need.select_label',
                'formType' => 'need_list',
                'template' => 'KompulseNeedBundle:Field:needlist.html.php',
            )
        );
    }
}
