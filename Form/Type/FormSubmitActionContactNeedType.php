<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle\Form\Type;

use MauticPlugin\KompulseNeedBundle\Model\NeedModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotEqualTo;

/**
 * Class FormSubmitActionUserEmailType.
 */
class FormSubmitActionContactNeedType extends AbstractType
{
    /** @var NeedModel */
    protected $needModel;

    public function __construct(NeedModel $needModel)
    {
        $this->needModel = $needModel;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('need', 'choice', array(
                'choices' => $this->needModel->getNeedList(),
            ))
            ->add('operator', 'choice', [
                'label'      => 'mautic.lead.lead.submitaction.operator',
                'attr'       => ['class' => 'form-control'],
                'label_attr' => ['class' => 'control-label'],
                'choices'    => [
                    'plus'   => 'mautic.lead.lead.submitaction.operator_plus',
                    'minus'  => 'mautic.lead.lead.submitaction.operator_minus',
                    'times'  => 'mautic.lead.lead.submitaction.operator_times',
                    'divide' => 'mautic.lead.lead.submitaction.operator_divide',
                ],
            ])
            ->add(
                'points',
                'number',
                [
                    'label'       => 'kompulse.contact_need.event.points',
                    'attr'        => ['class' => 'form-control'],
                    'label_attr'  => ['class' => 'control-label'],
                    'precision'   => 0,
                    'data'        => (isset($options['data']['points'])) ? $options['data']['points'] : 0,
                    'constraints' => [
                        new NotEqualTo(
                            [
                                'value'   => '0',
                                'message' => 'mautic.core.value.required',
                            ]
                        ),
                    ],
                ]
            )

            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'kompulse_need_submitaction_contact_need';
    }
}
