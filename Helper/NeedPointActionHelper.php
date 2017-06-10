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

/**
 * Class NeedPointActionHelper.
 */
class NeedPointActionHelper
{
    /**
     * @param $eventDetails
     * @param $action
     *
     * @return bool
     */
    public static function validateAssetDownload($eventDetails, $action)
    {
        $assetId       = $eventDetails->getId();
        $limitToAssets = $action['properties']['assets'];

        if (!empty($limitToAssets) && !in_array($assetId, $limitToAssets)) {
            //no points change
            return false;
        }

        return true;
    }

    /**
     * @param $eventDetails
     * @param $action
     *
     * @return int
     */
    public static function validateFormSubmit($eventDetails, $action)
    {
        $form         = $eventDetails->getForm();
        $formId       = $form->getId();
        $limitToForms = $action['properties']['forms'];

        if (!empty($limitToForms) && !in_array($formId, $limitToForms)) {
            //no points change
            return false;
        }

        return true;
    }
}
