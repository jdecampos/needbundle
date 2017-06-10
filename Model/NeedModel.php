<?php

namespace MauticPlugin\KompulseNeedBundle\Model;

use Mautic\LeadBundle\Entity\OperatorListTrait;
use Mautic\CoreBundle\Model\FormModel;
use MauticPlugin\KompulseNeedBundle\Entity\Need;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Class NeedModel
 * {@inheritdoc}
 */
class NeedModel extends FormModel
{
    use OperatorListTrait;
    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * NeedModel constructor.
     *
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getRepository()
    {
        return $this->em->getRepository('KompulseNeedBundle:Need');
    }

    public function getNameGetter()
    {
        return 'getName';
    }

    public function getPermissionBase($bundle = null)
    {
        if (null === $bundle) {
            $bundle = $this->request->get('bundle');
        }

        if ('global' === $bundle || empty($bundle)) {
            $bundle = 'need';
        }

        return $bundle.':needs';
    }


    /**
     * {@inheritdoc}
     *
     * @param       $entity
     * @param       $formFactory
     * @param null  $action
     * @param array $options
     *
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof Need) {
            throw new MethodNotAllowedHttpException(['Need']);
        }
        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create('need', $entity, $options);
    }

    /**
     * Get a specific entity or generate a new one if id is empty.
     *
     * @param $id
     *
     * @return Need
     */
    public function getEntity($id = null)
    {
        if ($id === null) {
            return new Need();
        }

        $entity = parent::getEntity($id);

        return $entity;
    }

    /**
     * Get list of entities for filter list
     *
     * @return array
     */
    public function getNeedList($prefix = '')
    {
        return $this->getRepository()->getNeedList($prefix);
    }



    // /**
    //  * {@inheritdoc}
    //  *
    //  * @param $action
    //  * @param $event
    //  * @param $entity
    //  * @param $isNew
    //  *
    //  * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
    //  */
    // protected function dispatchEvent($action, &$entity, $isNew = false, Event $event = null)
    // {
    //     if (!$entity instanceof Need) {
    //         throw new MethodNotAllowedHttpException(['Category']);
    //     }

    //     switch ($action) {
    //         case 'pre_save':
    //             $name = CategoryEvents::CATEGORY_PRE_SAVE;
    //             break;
    //         case 'post_save':
    //             $name = CategoryEvents::CATEGORY_POST_SAVE;
    //             break;
    //         case 'pre_delete':
    //             $name = CategoryEvents::CATEGORY_PRE_DELETE;
    //             break;
    //         case 'post_delete':
    //             $name = CategoryEvents::CATEGORY_POST_DELETE;
    //             break;
    //         default:
    //             return null;
    //     }

    //     if ($this->dispatcher->hasListeners($name)) {
    //         if (empty($event)) {
    //             $event = new CategoryEvent($entity, $isNew);
    //             $event->setEntityManager($this->em);
    //         }

    //         $this->dispatcher->dispatch($name, $event);

    //         return $event;
    //     } else {
    //         return null;
    //     }
    // }

    // /**
    //  * Get list of entities for autopopulate fields.
    //  *
    //  * @param $bundle
    //  * @param $filter
    //  * @param $limit
    //  *
    //  * @return array
    //  */
    // public function getLookupResults($bundle, $filter = '', $limit = 10)
    // {
    //     static $results = [];

    //     $key = $bundle.$filter.$limit;
    //     if (!isset($results[$key])) {
    //         $results[$key] = $this->getRepository()->getCategoryList($bundle, $filter, $limit, 0);
    //     }

    //     return $results[$key];
    // }
}
