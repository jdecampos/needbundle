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

use Mautic\AssetBundle\AssetEvents;
use Mautic\AssetBundle\Event\AssetLoadEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use MauticPlugin\KompulseNeedBundle\Event\NeedPointBuilderEvent;
use MauticPlugin\KompulseNeedBundle\KompulseNeedEvents;
use MauticPlugin\KompulseNeedBundle\Model\NeedPointModel;

/**
 * Class NeedPointActionSubscriber.
 */
class NeedPointActionSubscriber extends CommonSubscriber
{
    /**
     * @var NeedPointModel
     */
    protected $needPointModel;

    /**
     * NeedPointActionSubscriber constructor.
     *
     * @param NeedPointModel $needPointModel
     */
    public function __construct(NeedPointModel $needPointModel)
    {
        $this->needPointModel = $needPointModel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KompulseNeedEvents::NEED_POINT_ON_BUILD => ['onNeedPointBuild', 0],
            AssetEvents::ASSET_ON_LOAD  => ['onAssetDownload', 0],
        ];
    }

    /**
     * @param NeedPointBuilderEvent $event
     */
    public function onNeedPointBuild(NeedPointBuilderEvent $event)
    {
        // from AssetBundle
        $action = [
            'group'       => 'mautic.asset.actions',
            'label'       => 'mautic.asset.point.action.download',
            'description' => 'mautic.asset.point.action.download_descr',
            'callback'    => ['\\MauticPlugin\\KompulseNeedBundle\\Helper\\NeedPointActionHelper', 'validateAssetDownload'],
            'formType'    => 'pointaction_assetdownload',
        ];

        $event->addAction('asset.download', $action);

        // from FormBundle
        $action = [
            'group'       => 'mautic.form.point.action',
            'label'       => 'mautic.form.point.action.submit',
            'description' => 'mautic.form.point.action.submit_descr',
            'callback'    => ['\\MauticPlugin\\KompulseNeedBundle\\Helper\\NeedPointActionHelper', 'validateFormSubmit'],
            'formType'    => 'pointaction_formsubmit',
        ];

        $event->addAction('form.submit', $action);

        // from EmailBundle
        $action = [
            'group'    => 'mautic.email.actions',
            'label'    => 'mautic.email.point.action.open',
            'callback' => ['\\Mautic\\EmailBundle\\Helper\\PointEventHelper', 'validateEmail'],
            'formType' => 'emailopen_list',
        ];

        $event->addAction('email.open', $action);

        $action = [
            'group'    => 'mautic.email.actions',
            'label'    => 'mautic.email.point.action.send',
            'callback' => ['\\Mautic\\EmailBundle\\Helper\\PointEventHelper', 'validateEmail'],
            'formType' => 'emailopen_list',
        ];

        $event->addAction('email.send', $action);

        // from PageBundle
        $action = [
            'group'       => 'mautic.page.point.action',
            'label'       => 'mautic.page.point.action.pagehit',
            'description' => 'mautic.page.point.action.pagehit_descr',
            'callback'    => ['\\Mautic\\PageBundle\\Helper\\PointActionHelper', 'validatePageHit'],
            'formType'    => 'pointaction_pagehit',
        ];

        $event->addAction('page.hit', $action);

        $action = [
            'group'       => 'mautic.page.point.action',
            'label'       => 'mautic.page.point.action.urlhit',
            'description' => 'mautic.page.point.action.urlhit_descr',
            'callback'    => ['\\Mautic\\PageBundle\\Helper\\PointActionHelper', 'validateUrlHit'],
            'formType'    => 'pointaction_urlhit',
            'formTheme'   => 'MauticPageBundle:FormTheme\Point',
        ];

        $event->addAction('url.hit', $action);
    }

    /**
     * Trigger point actions for asset download.
     *
     * @param AssetLoadEvent $event
     */
    public function onAssetDownload(AssetLoadEvent $event)
    {
        $asset = $event->getRecord()->getAsset();

        if ($asset !== null) {
            $this->needPointModel->triggerAction('asset.download', $asset);
        }
    }
}
