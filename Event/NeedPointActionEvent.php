<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;
use MauticPlugin\KompulseNeedBundle\Entity\ContactNeed;
use MauticPlugin\KompulseNeedBundle\Entity\NeedPoint;

/**
 * Class NeedPointActionEvent.
 */
class NeedPointActionEvent extends CommonEvent
{
    /**
     * @var NeedPoint
     */
    protected $needPoint;

    /**
     * @var ContactNeed
     */
    protected $contactNeed;

    /**
     * @param NeedPoint $needPoint
     * @param ContactNeed  $contactNeed
     */
    public function __construct(NeedPoint &$needPoint, ContactNeed &$contactNeed)
    {
        $this->needPoint    = $needPoint;
        $this->contactNeed  = $contactNeed;
    }

    /**
     * Returns the NeedPoint entity.
     *
     * @return NeedPoint
     */
    public function getNeedPoint()
    {
        return $this->needPoint;
    }

    /**
     * Sets the NeedPoint entity.
     *
     * @param NeedPoint $needPoint
     */
    public function setNeedPoint(NeedPoint $needPoint)
    {
        $this->needPoint = $needPoint;
    }

    /**
     * Returns the ContactNeed entity.
     *
     * @return ContactNeed
     */
    public function getContactNeed()
    {
        return $this->contactNeed;
    }

    /**
     * Sets the ContactNeed entity.
     *
     * @param $contactNeed
     */
    public function setContactNeed($contactNeed)
    {
        $this->contactNeed = $contactNeed;
    }
}
