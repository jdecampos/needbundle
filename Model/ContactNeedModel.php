<?php

namespace MauticPlugin\KompulseNeedBundle\Model;

use Mautic\CoreBundle\Model\FormModel;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\KompulseNeedBundle\KompulseNeedEvents;
use MauticPlugin\KompulseNeedBundle\Entity\Need;
use MauticPlugin\KompulseNeedBundle\Entity\ContactNeed;
use MauticPlugin\KompulseNeedBundle\Event\ContactNeedEvent;
use MauticPlugin\KompulseNeedBundle\Form\Type\ContactNeedType;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Class ContactNeedModel
 * {@inheritdoc}
 */
class ContactNeedModel extends FormModel
{
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
        return $this->em->getRepository('KompulseNeedBundle:ContactNeed');
    }

    public function getNameGetter()
    {
        return 'getName';
    }

    public function getPermissionBase($bundle = null)
    {
        return 'kompulse:contactneeds';
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
        if (!$entity instanceof ContactNeed) {
            throw new MethodNotAllowedHttpException(['ContactNeed']);
        }
        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create(new ContactNeedType($entity->getLead()), $entity, $options);
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
            return new ContactNeed();
        }

        $entity = parent::getEntity($id);

        return $entity;
    }

    public function createOrFindBy(Lead $lead, Need $need)
    {
        $qb = $this->getRepository()->createQueryBuilder('cn')
            ->andWhere('cn.need = :need')
            ->andWhere('cn.lead = :lead')
            ->setParameters(['need' => $need, 'lead' => $lead]);

        $contactNeed = $qb->getQuery()->getOneOrNullResult();

        if ($contactNeed !== null) {
            return $contactNeed;
        }

        $contactNeed = new ContactNeed();
        $contactNeed->setLead($lead);
        $contactNeed->setNeed($need);

        return $contactNeed;
    }

    public function getFilteredLeads($args)
    {
        $qb = $this->getRepository()->createQueryBuilder('cn')
            ->select('l')
            ->innerJoin('cn.lead', 'l')
            ->leftJoin('l.category', 'cat')
            ->andWhere('cn.need = :need')
            ->andWhere('cn.lead = :lead')
           ;

        $args = array_merge($args, $qb);

        return $this->getEntities($args);
    }

    /**
     * {@inheritdoc}
     *
     * @param $action
     * @param $event
     * @param $entity
     * @param $isNew
     *
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, Event $event = null)
    {
        if (!$entity instanceof ContactNeed) {
            throw new MethodNotAllowedHttpException(['Kompulse']);
        }

        switch ($action) {
            case 'pre_save':
                $name = KompulseNeedEvents::CONTACT_NEED_PRE_SAVE;
                break;
            case 'post_save':
                $name = KompulseNeedEvents::CONTACT_NEED_POST_SAVE;
                break;
            case 'pre_delete':
                $name = KompulseNeedEvents::CONTACT_NEED_PRE_DELETE;
                break;
            case 'post_delete':
                $name = KompulseNeedEvents::CONTACT_NEED_POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (empty($event)) {
                $event = new ContactNeedEvent($entity, $isNew);
                $event->setEntityManager($this->em);
            }

            $this->dispatcher->dispatch($name, $event);

            return $event;
        } else {
            return null;
        }
    }



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
    //         $results[$key] = $this->getRepository()->getKompulseList($bundle, $filter, $limit, 0);
    //     }

    //     return $results[$key];
    // }
}
