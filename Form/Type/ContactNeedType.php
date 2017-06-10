<?php

namespace MauticPlugin\KompulseNeedBundle\Form\Type;

use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\EventListener\FormExitSubscriber;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\KompulseNeedBundle\Entity\NeedRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ContactNeedType.
 */
class ContactNeedType extends AbstractType
{
    /** @var Lead */
    private $lead;

    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $lead = $this->lead;
        $entity = $builder->getData();
        $builder->addEventSubscriber(new FormExitSubscriber('contactneed', $options));

        if ($entity->getId() === null) {
            $builder->add('need', 'entity', array(
                'class' => 'MauticPlugin\KompulseNeedBundle\Entity\Need',
                'property' => 'name',
                'query_builder' => function (NeedRepository $er) use ($lead) {
                    return $er->getSelectListQueryBuilder($lead);
                },
            ));
        }

        $builder->add('points');
        $builder->add('buttons', 'form_buttons');

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'MauticPlugin\KompulseNeedBundle\Entity\ContactNeed',
            'cascade_validation' => true,
            'permissionsConfig'  => [],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'contactneed';
    }
}
