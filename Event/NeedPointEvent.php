<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;
use MauticPlugin\KompulseNeedBundle\Entity\NeedPoint;

/**
 * Class NeedPointEvent.
 */
class NeedPointEvent extends CommonEvent
{
    /**
     * @param Point $point
     * @param bool  $isNew
     */
    public function __construct(NeedPoint &$needPoint, $isNew = false)
    {
        $this->entity = &$needPoint;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Point entity.
     *
     * @return Point
     */
    public function getPoint()
    {
        return $this->entity;
    }

    /**
     * Sets the Point entity.
     *
     * @param Point $point
     */
    public function setPoint(NeedPoint $needPoint)
    {
        $this->entity = $needPoint;
    }
}
