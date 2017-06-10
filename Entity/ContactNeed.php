<?php

namespace MauticPlugin\KompulseNeedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\CommonEntity;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\KompulseNeedBundle\Entity\Need;

/**
 * Class ContactNeed.
 */
class ContactNeed extends CommonEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Lead
     */
    protected $lead;

    /**
     * @var Need
     */
    protected $need;

    /**
     * @var int
     */
    private $points = 0;

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('plugin_kompulse_contact_need')
            ->setCustomRepositoryClass('MauticPlugin\KompulseNeedBundle\Entity\ContactNeedRepository')
            ->addUniqueConstraint(['lead_id', 'need_id'], 'unique_contact_need');

        $builder->addId();

        $builder->addLead();
        $builder->createField('points', 'integer')
            ->build();

        $need = $builder->createManyToOne('need', 'MauticPlugin\KompulseNeedBundle\Entity\Need');
        $need->addJoinColumn('need_id', 'id', false, false, 'CASCADE')
            ->build();

    }

    public function getName()
    {
        return $this->getLead() . '-' . $this->getNeed()->getName();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Lead
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @param Lead $lead
     *
     * @return ContactNeed
     */
    public function setLead(Lead $lead)
    {
        $this->lead = $lead;

        return $this;
    }

    /**
     * @return Need
     */
    public function getNeed()
    {
        return $this->need;
    }

    /**
     * @param Need $need
     *
     * @return ContactNeed
     */
    public function setNeed(Need $need)
    {
        $this->need = $need;

        return $this;
    }

    /**
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param int $points
     *
     * @return ContactNeed
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }
    /**
     * @param int    $points
     * @param string $operator
     *
     * @return Lead
     */
    public function adjustPoints($points, $operator = 'plus')
    {
        $oldPoints = $this->points;
        switch ($operator) {
            case 'plus':
                $this->points += $points;
                break;
            case 'minus':
                $this->points -= $points;
                break;
            case 'times':
                $this->points *= $points;
                break;
            case 'divide':
                $this->points /= $points;
                break;
            default:
                throw new \UnexpectedValueException('Invalid operator');
        }

        $this->isChanged('points', (int) $this->points, (int) $oldPoints);

        return $this;
    }

    public function initChange()
    {
        $this->addChange('points', [0, $this->points]);
    }

    public function isChanged($prop, $val, $oldValue = null)
    {
        $getter  = 'get'.ucfirst($prop);
        $current = $oldValue !== null ? $oldValue : $this->$getter();

        if ($prop == 'points'  && $current != $val) {
            $this->changes['points'] = [$current, $val];
        } else {
            parent::isChanged($prop, $val);
        }
    }



}
