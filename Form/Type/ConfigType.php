<?php

namespace MauticPlugin\KompulseNeedBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ConfigType
 */
class ConfigType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'licence_key',
            'text',
            array(
                'label' => 'plugin.kompulse.config.licence_key',
                'data'  => $options['data']['licence_key'],
                'attr'  => array(
                    'tooltip' => 'plugin.kompulse.config.licence_key_tooltip'
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'kompulse_config';
    }
}
