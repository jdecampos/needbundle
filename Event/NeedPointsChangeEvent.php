<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Kgtech
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;
use MauticPlugin\KompulseNeedBundle\Entity\ContactNeed;

/**
 * Class NeedPointsChangeEvent.
 */
class NeedPointsChangeEvent extends CommonEvent
{
    protected $old;
    protected $new;

    /**
     * @param Lead $lead
     * @param bool $isNew
     */
    public function __construct(ContactNeed &$contactNeed, $old, $new)
    {
        $this->entity = &$contactNeed;
        $this->old    = (int) $old;
        $this->new    = (int) $new;
    }

    /**
     * Returns the Lead entity.
     *
     * @return ContactNeed
     */
    public function getContactNeed()
    {
        return $this->entity;
    }

    /**
     * Returns the new points.
     *
     * @return int
     */
    public function getNewPoints()
    {
        return $this->new;
    }

    /**
     * Returns the old points.
     *
     * @return int
     */
    public function getOldPoints()
    {
        return $this->old;
    }
}
