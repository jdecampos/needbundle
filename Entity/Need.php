<?php

namespace MauticPlugin\KompulseNeedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\CommonEntity;

/**
 * Class Need.
 */
class Need extends CommonEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('plugin_kompulse_need')
            ->setCustomRepositoryClass('MauticPlugin\KompulseNeedBundle\Entity\NeedRepository');

        $builder->addId();
        $builder->addNamedField('name', 'string', 'name');
        $builder->addNamedField('description', 'string', 'description', $nullable = 'true');
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Name $name
     *
     * @return Need
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param Description $description
     *
     * @return Need
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }


}
