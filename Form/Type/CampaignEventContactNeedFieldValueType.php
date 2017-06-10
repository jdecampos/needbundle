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

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\LeadBundle\Helper\FormFieldHelper;
use Mautic\LeadBundle\Model\LeadModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class CampaignEventLeadFieldValueType.
 */
class CampaignEventContactNeedFieldValueType extends AbstractType
{
    private $factory;

    public function __construct(MauticFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $needModel = $this->factory->getModel('kompulse.need');

        $builder->add(
            'field',
            ChoiceType::class,
            [
                'label'       => 'kompulse.contact_need.campaign.event.field',
                'label_attr'  => ['class' => 'control-label'],
                'multiple'    => false,
                'choices'     => $needModel->getNeedList('need'),
                'empty_value' => 'mautic.core.select',
                'attr'        => [
                    'class'    => 'form-control',
                    'tooltip'  => 'mautic.lead.campaign.event.field_descr',
                    'onchange' => 'Mautic.updateLeadFieldValues(this)',
                ],
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        ['message' => 'mautic.core.value.required']
                    ),
                ],
            ]
        );

        $builder->add(
            'operator',
            'choice',
            [
                'label'      => 'mautic.lead.lead.submitaction.operator',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'onchange' => 'Mautic.updateLeadFieldValues(this)',
                ],
                'choices' => [
                    'eq' => 'eq',
                    'lt' => 'lower than',
                    'gt' => 'greater than',
                ]
            ]
        );

       $builder->add(
            'value',
            'text',
            [
                'label'       => 'mautic.form.field.form.value',
                'label_attr'  => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(
                        ['message' => 'mautic.core.value.required']
                    ),
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'campaignevent_contact_need_field_value';
    }
}
