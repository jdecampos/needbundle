<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle\Model;

use Mautic\CoreBundle\Model\FormModel as CommonFormModel;
use MauticPlugin\KompulseNeedBundle\Entity\TriggerEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Class TriggerEventModel.
 */
class TriggerEventModel extends CommonFormModel
{
    /**
     * {@inheritdoc}
     *
     * @return \MauticPlugin\KompulseNeedBundle\Entity\TriggerEventRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('KompulseNeedBundle:TriggerEvent');
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissionBase()
    {
        return 'kompulse:triggers';
    }

    /**
     * {@inheritdoc}
     *
     * @return TriggerEvent|null
     */
    public function getEntity($id = null)
    {
        if ($id === null) {
            return new TriggerEvent();
        }

        return parent::getEntity($id);
    }

    /**
     * {@inheritdoc}
     *
     * @throws MethodNotAllowedHttpException
     */
    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof TriggerEvent) {
            throw new MethodNotAllowedHttpException(['Trigger']);
        }

        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create('pointtriggerevent', $entity, $options);
    }
}
