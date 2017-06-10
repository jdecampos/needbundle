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
 * Class ContactNeedPointLog.
 */
class ContactNeedPointLog
{
    /**
     * @var NeedPoint
     **/
    private $needPoint;

    /**
     * @var \Mautic\CoreBundle\Entity\IpAddress
     */
    private $ipAddress;

    /**
     * @var \DateTime
     **/
    private $dateFired;

    /**
     * @var \Mautic\LeadBundle\Entity\Lead
     */
    private $lead;


    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('plugin_kompulse_need_point_contact_action_log')
            ->setCustomRepositoryClass('MauticPlugin\KompulseNeedBundle\Entity\ContactNeedPointLogRepository');

        $builder->createManyToOne('needPoint', 'NeedPoint')
            ->isPrimaryKey()
            ->addJoinColumn('need_point_id', 'id', true, false, 'CASCADE')
            ->inversedBy('log')
            ->build();

        // $builder->createManyToOne('contactNeed', 'MauticPlugin\KompulseNeedBundle\Entity\ContactNeed')
        //     ->addJoinColumn('contact_need_id', 'id', false, false, 'CASCADE')
        //     ->build();

        $builder->addLead(false, 'CASCADE', true);

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
    public function getNeedPoint()
    {
        return $this->needPoint;
    }

    /**
     * @param mixed $needPoint
     */
    public function setNeedPoint($needPoint)
    {
        $this->needPoint = $needPoint;
    }


    /**
     * @return mixed
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @param mixed $lead
     */
    public function setLead($lead)
    {
        $this->lead = $lead;
    }

}
