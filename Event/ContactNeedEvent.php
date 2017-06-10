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
use MauticPlugin\KompulseNeedBundle\Entity\ContactNeed;

/**
 * Class ContactNeedEvent.
 */
class ContactNeedEvent extends CommonEvent
{
    /**
     * @param ContactNeed $contactNeed
     * @param bool $isNew
     */
    public function __construct(ContactNeed &$contactNeed, $isNew = false)
    {
        $this->entity = &$contactNeed;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the ContactNeed entity.
     *
     * @return ContactNeed
     */
    public function getContactNeed()
    {
        return $this->entity;
    }

    /**
     * Sets the ContactNeed entity.
     *
     * @param ContactNeed $contactNeed
     */
    public function setContactNeed(ContactNeed $contactNeed)
    {
        $this->entity = $contactNeed;
    }
}
