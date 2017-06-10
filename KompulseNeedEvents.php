<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://kompulse_need.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle;

/**
 * Class KompulseNeedEvents.
 *
 * Events available for KompulseNeedBundle
 */
final class KompulseNeedEvents
{

    const CONTACT_NEED_POINTS_CHANGE = 'kompulse_need.contact_need.change';


    /**
     * @var string
     */
    const CONTACT_NEED_PRE_SAVE = 'kompulse_need.contact_need_pre_save';

    /**
     * @var string
     */
    const CONTACT_NEED_POST_SAVE = 'kompulse_need.contact_need_post_save';

    /**
     * @var string
     */
    const CONTACT_NEED_PRE_DELETE = 'kompulse_need.contact_need_pre_delete';

    /**
     * @var string
     */
    const CONTACT_NEED_POST_DELETE = 'kompulse_need.contact_need_post_delete';

    /**
     * @var string
     */
    const CONTACT_NEED_ON_BUILD = 'kompulse_need.contact_need_on_build';

    /**
     * @var string
     */
    const CONTACT_NEED_ON_ACTION = 'kompulse_need.contact_need_on_action';

    /**
     * @var string
     */
    const TRIGGER_PRE_SAVE = 'kompulse_need.trigger_pre_save';

    /**
     * @var string
     */
    const TRIGGER_POST_SAVE = 'kompulse_need.trigger_post_save';

    /**
     * @var string
     */
    const TRIGGER_PRE_DELETE = 'kompulse_need.trigger_pre_delete';

    /**
     * @var string
     */
    const TRIGGER_POST_DELETE = 'kompulse_need.trigger_post_delete';

    /**
     * @var string
     */
    const TRIGGER_ON_BUILD = 'kompulse_need.trigger_on_build';

    /**
     * @var string
     */
    const TRIGGER_ON_EVENT_EXECUTE = 'kompulse_need.trigger_on_event_execute';

    /**
     * @var string
     */
    const NEED_POINT_ON_BUILD    = 'kompulse_need.need_point.on_build';

    /**
     * @var string
     */
    const NEED_POINT_PRE_SAVE    = 'kompulse_need.need_point.pre_save';

    /**
     * @var string
     */
    const NEED_POINT_POST_SAVE   = 'kompulse_need.need_point.post_save';

    /**
     * @var string
     */
    const NEED_POINT_PRE_DELETE  = 'kompulse_need.need_point.pre_delete';

    /**
     * @var string
     */
    const NEED_POINT_POST_DELETE = 'kompulse_need.need_point.post_delete';

    /**
     * @var string
     */
    const NEED_POINT_ON_ACTION   = 'kompulse_need.need_point.on_action';

    /**
     * @var string
     */
    const ON_CAMPAIGN_TRIGGER_CONDITION = 'kompulse_need.on_campaign_trigger_condition';
}
