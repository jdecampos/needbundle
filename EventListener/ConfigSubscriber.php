<?php

/*
 * @copyright   2017 Kgtech. All rights reserved
 * @author      Kgtech
 *
 * @link        https://www.kgtech.fi
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle\EventListener;

use Mautic\ConfigBundle\ConfigEvents;
use Mautic\ConfigBundle\Event\ConfigBuilderEvent;
use Mautic\ConfigBundle\Event\ConfigEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;

/**
 * Class ConfigSubscriber.
 */
class ConfigSubscriber extends CommonSubscriber
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ConfigEvents::CONFIG_ON_GENERATE => ['onConfigGenerate', 0],
            ConfigEvents::CONFIG_PRE_SAVE    => ['onConfigSave', 0],
        ];
    }

    /**
     * @param ConfigBuilderEvent $event
     */
    public function onConfigGenerate(ConfigBuilderEvent $event)
    {
        $event->addForm(
            [
                'formAlias'  => 'kompulse_config',
                'formTheme'  => 'kompulseBundle:FormTheme\Config',
                'parameters' => $event->getParametersFromConfig('KompulseNeedBundle'),
            ]
        );
    }

    /**
     * @param ConfigEvent $event
     */
    public function onConfigSave(ConfigEvent $event)
    {
        /** @var array $values */
        $values = $event->getConfig();

        // Manipulate the values
        if (!empty($values['kompulse_config']['licence_key'])) {
            $values['kompulse_config']['licence_key'] = htmlspecialchars($values['kompulse_config']['licence_key']);
        }

        // Set updated values
        $event->setConfig($values);
    }
}
