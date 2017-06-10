<?php

/*
 * @author      Kgtech
 *
 * @link        https://www.kgtech.fi
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle\Form\Type;

use MauticPlugin\KompulseNeedBundle\Model\NeedModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotEqualTo;

/**
 * Class PointsActionType.
 */
class PointActionType extends AbstractType
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

    /**
     * @return string
     */
    public function getName()
    {
        return 'contactneed_points_action';
    }
}
