<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;

/**
 * Class LeadTriggerLog.
 */
class ContactNeedTriggerLog
{
    /**
     * @var TriggerEvent
     **/
    private $event;

    /**
     * @var \MauticPlugin\KompulseNeedBundle\Entity\ContactNeed
     **/
    private $contactNeed;

    /**
     * @var \Mautic\CoreBundle\Entity\IpAddress
     **/
    private $ipAddress;

    /**
     * @var \DateTime
     **/
    private $dateFired;

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('plugin_kompulse_point_contact_need_event_log')
            ->setCustomRepositoryClass('MauticPlugin\KompulseNeedBundle\Entity\ContactNeedTriggerLogRepository');

        $builder->createManyToOne('event', 'TriggerEvent')
            ->isPrimaryKey()
            ->addJoinColumn('event_id', 'id', false, false, 'CASCADE')
            ->inversedBy('log')
            ->build();

        $builder->createManyToOne('contactNeed', 'MauticPlugin\KompulseNeedBundle\Entity\ContactNeed')
            ->isPrimaryKey()
            ->addJoinColumn('contact_need_id', 'id', false, false, 'CASCADE')
            ->build();

        $builder->addIpAddress(true);

        $builder->createField('dateFired', 'datetime')
            ->columnName('date_fired')
            ->build();
    }

    /**
     * @return mixed
     */
    public function getDateFired()
    {
        return $this->dateFired;
    }

    /**
     * @param mixed $dateFired
     */
    public function setDateFired($dateFired)
    {
        $this->dateFired = $dateFired;
    }

    /**
     * @return mixed
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @param mixed $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }

    /**
     * @return mixed
     */
    public function getContactNeed()
    {
        return $this->contactNeed;
    }

    /**
     * @param mixed $contactNeed
     */
    public function setContactNeed($contactNeed)
    {
        $this->contactNeed = $contactNeed;
    }

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param mixed $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }
}
