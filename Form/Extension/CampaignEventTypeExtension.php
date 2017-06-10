<?php

namespace MauticPlugin\KompulseNeedBundle\Form\Extension;

use Mautic\CampaignBundle\Form\Type\EventType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class CampaignEventTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        //parent::buildForm($builder, $options);

        $builder
            ->add('need', 'entity', array(
            'class' => 'MauticPlugin\KompulseNeedBundle\Entity\Need'
            ));
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return EventType::class;
    }
}
