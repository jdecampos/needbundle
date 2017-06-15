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

use Mautic\CoreBundle\Helper\Chart\ChartQuery;
use Mautic\CoreBundle\Helper\Chart\LineChart;
use Mautic\CoreBundle\Helper\IpLookupHelper;
use Mautic\CoreBundle\Model\FormModel as CommonFormModel;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PointBundle\Entity\Action;
use Mautic\PointBundle\Entity\LeadPointLog;
use Mautic\PointBundle\Entity\Point;
use Mautic\PointBundle\Event\PointEvent;
use MauticPlugin\KompulseNeedBundle\Entity\NeedPoint;
use MauticPlugin\KompulseNeedBundle\Entity\ContactNeedPointLog;
use MauticPlugin\KompulseNeedBundle\Event\NeedPointActionEvent;
use MauticPlugin\KompulseNeedBundle\Event\NeedPointBuilderEvent;
use MauticPlugin\KompulseNeedBundle\KompulseNeedEvents;
use MauticPlugin\KompulseNeedBundle\Model\ContactNeedModel;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Class NeedPointModel.
  */
class NeedPointModel extends CommonFormModel
{
    /**
     * @deprecated Remove in 2.0
     *
     * @var MauticFactory
     */
    protected $factory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var IpLookupHelper
     */
    protected $ipLookupHelper;

    /**
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var ContactNeedModel
     */
    protected $contactNeedModel;

    /**
     * PointModel constructor.
     *
     * @param Session        $session
     * @param IpLookupHelper $ipLookupHelper
     * @param LeadModel      $leadModel
     */
    public function __construct(
        Session $session,
        IpLookupHelper $ipLookupHelper,
        LeadModel $leadModel,
        ContactNeedModel $contactNeedModel
    )
    {
        $this->session          = $session;
        $this->ipLookupHelper   = $ipLookupHelper;
        $this->leadModel        = $leadModel;
        $this->contactNeedModel = $contactNeedModel;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Mautic\PointBundle\Entity\PointRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('KompulseNeedBundle:NeedPoint');
    }

    /**
     * {@inheritdoc}
     *
     * @return \MauticPlugin\KompulseNeedBundle\Entity\PointRepository
     */
    public function getNeedPointRepository()
    {
        return $this->em->getRepository('KompulseNeedBundle:NeedPoint');
    }

    /**
     * {@inheritdoc}
     *
     * @return \MauticPlugin\KompulseNeedBundle\Entity\ContactNeedPointLogRepository
     */
    public function getContactNeedPointLogRepository()
    {
        return $this->em->getRepository('KompulseNeedBundle:ContactNeedPointLog');
    }
    /**
     * {@inheritdoc}
     */
    public function getPermissionBase()
    {
        return 'kompulse:need_point';
    }

    /**
     * {@inheritdoc}
      *
     * @throws MethodNotAllowedHttpException
     */
    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof NeedPoint) {
            throw new MethodNotAllowedHttpException(['NeedPoint']);
        }

        if (!empty($action)) {
            $options['action'] = $action;
        }

        if (empty($options['pointActions'])) {
            $options['pointActions'] = $this->getPointActions();
        }

        return $formFactory->create('need_point', $entity, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @return Point|null
     */
    public function getEntity($id = null)
    {
        if ($id === null) {
            return new NeedPoint();
        }

        return parent::getEntity($id);
    }

    /**
     * {@inheritdoc}
     *
     * @throws MethodNotAllowedHttpException
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, Event $event = null)
    {
        if (!$entity instanceof NeedPoint) {
            throw new MethodNotAllowedHttpException(['Point']);
        }

        switch ($action) {
            case 'pre_save':
                $name = KompulseNeedEvents::NEED_POINT_PRE_SAVE;
                break;
            case 'post_save':
                $name = KompulseNeedEvents::NEED_POINT_POST_SAVE;
                break;
            case 'pre_delete':
                $name = KompulseNeedEvents::NEED_POINT_PRE_DELETE;
                break;
            case 'post_delete':
                $name = KompulseNeedEvents::NEED_POINT_POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (empty($event)) {
                $event = new KompulseEvent($entity, $isNew);
                $event->setEntityManager($this->em);
            }

            $this->dispatcher->dispatch($name, $event);

            return $event;
        }

        return null;
    }

    /**
     * Gets array of custom actions from bundles subscribed PointEvents::POINT_ON_BUILD.
     *
     * @return mixed
     */
    public function getPointActions()
    {
        static $actions;

        if (empty($actions)) {
            //build them
            $actions = [];
            $event   = new NeedPointBuilderEvent($this->translator);
            $this->dispatcher->dispatch(KompulseNeedEvents::NEED_POINT_ON_BUILD, $event);
            $actions['actions'] = $event->getActions();
            $actions['list']    = $event->getActionList();
            $actions['choices'] = $event->getActionChoices();
        }

        return $actions;
    }

    /**
     * Triggers a specific point change.
     *
     * @param       $type
     * @param mixed $eventDetails passthrough from function triggering action to the callback function
     * @param mixed $typeId       Something unique to the triggering event to prevent unnecessary duplicate calls
     * @param Lead  $lead
     */
    public function triggerAction($type, $eventDetails = null, $typeId = null, Lead $lead = null)
    {
        //only trigger actions for anonymous users
        if (!$this->security->isAnonymous()) {
            return;
        }

        if ($typeId !== null && MAUTIC_ENV === 'prod') {
            //let's prevent some unnecessary DB calls
            $triggeredEvents = $this->session->get('mautic.triggered.need_point.actions', []);
            if (in_array($typeId, $triggeredEvents)) {
                return;
            }
            $triggeredEvents[] = $typeId;
            $this->session->set('mautic.triggered.need_point.actions', $triggeredEvents);
        }

        //find all the actions for published points
        /** @var \MauticPlugin\KompulseNeedBundle\Entity\NeedPointRepository $repo */
        $repo            = $this->getRepository();
        $needPointRepo   = $this->getNeedPointRepository();
        $availablePoints = $repo->getPublishedByType($type);
        $ipAddress       = $this->ipLookupHelper->getIpAddress();

        // temp hack for debug
        //$lead = $this->leadModel->getEntity(13);
        if (null === $lead) {
            $lead = $this->leadModel->getCurrentLead();

            if (null === $lead || !$lead->getId()) {
                return;
            }
        }



        //get available actions
        $availableActions = $this->getPointActions();

        //get a list of actions that has already been performed on this lead
        $completedActions = $needPointRepo->getCompletedLeadActions($type, $lead->getId());

        $persist = [];
        foreach ($availablePoints as $action) {
            //if it's already been done, then skip it
            if (isset($completedActions[$action->getId()])) {
                continue;
            }

            //make sure the action still exists
            if (!isset($availableActions['actions'][$action->getType()])) {
                continue;
            }
            $settings = $availableActions['actions'][$action->getType()];

            $contactNeed = $this->contactNeedModel->createOrFindBy($lead, $action->getNeed());

            $args = [
                'action' => [
                    'id'         => $action->getId(),
                    'type'       => $action->getType(),
                    'name'       => $action->getName(),
                    'properties' => $action->getProperties(),
                    'points'     => $action->getDelta(),
                ],
                'lead'         => $lead,
                'contactNeed'  => $contactNeed,
                'need'         => $action->getNeed(),
                'factory'      => $this->factory, // WHAT?
                'eventDetails' => $eventDetails,
            ];

            $callback = (isset($settings['callback'])) ? $settings['callback'] :
                ['\\MauticPlugin\\KompulseNeedBundle\\Helper\\EventHelper', 'engagePointAction'];

            if (is_callable($callback)) {
                if (is_array($callback)) {
                    $reflection = new \ReflectionMethod($callback[0], $callback[1]);
                } elseif (strpos($callback, '::') !== false) {
                    $parts      = explode('::', $callback);
                    $reflection = new \ReflectionMethod($parts[0], $parts[1]);
                } else {
                    $reflection = new \ReflectionMethod(null, $callback);
                }

                $pass = [];
                foreach ($reflection->getParameters() as $param) {
                    if (isset($args[$param->getName()])) {
                        $pass[] = $args[$param->getName()];
                    } else {
                        $pass[] = null;
                    }
                }
                $pointsChange = $reflection->invokeArgs($this, $pass);

                if ($pointsChange) {
                    $delta = $action->getDelta();
                    $contactNeed->adjustPoints($delta);
                    $parsed = explode('.', $action->getType());
                    // $lead->addPointsChangeLogEntry(
                    //     $parsed[0],
                    //     $action->getId().': '.$action->getName(),
                    //     $parsed[1],
                    //     $delta,
                    //     $ipAddress
                    // );

                    $event = new NeedPointActionEvent($action, $contactNeed);
                    $this->dispatcher->dispatch(KompulseNeedEvents::NEED_POINT_ON_ACTION, $event);

                    $log = new ContactNeedPointLog();
                    $log->setIpAddress($ipAddress);
                    $log->setNeedPoint($action);
                    $log->setLead($lead);
                    $log->setDateFired(new \DateTime());

                    $this->contactNeedModel->saveEntity($contactNeed);
                    $persist[] = $log;
                }
            }
        }

        if (!empty($persist)) {
            $this->getContactNeedPointLogRepository()->saveEntities($persist);

            // Detach logs to reserve memory
            $this->em->clear('MauticPlugin\KompulseNeedBundle\Entity\ContactNeedPointLog');
        }
    }

    // /**
    //  * Get line chart data of points.
    //  *
    //  * @param char      $unit          {@link php.net/manual/en/function.date.php#refsect1-function.date-parameters}
    //  * @param \DateTime $dateFrom
    //  * @param \DateTime $dateTo
    //  * @param string    $dateFormat
    //  * @param array     $filter
    //  * @param bool      $canViewOthers
    //  *
    //  * @return array
    //  */
    // public function getPointLineChartData($unit, \DateTime $dateFrom, \DateTime $dateTo, $dateFormat = null, $filter = [], $canViewOthers = true)
    // {
    //     $chart = new LineChart($unit, $dateFrom, $dateTo, $dateFormat);
    //     $query = new ChartQuery($this->em->getConnection(), $dateFrom, $dateTo);
    //     $q     = $query->prepareTimeDataQuery('lead_points_change_log', 'date_added', $filter);

    //     if (!$canViewOthers) {
    //         $q->join('t', MAUTIC_TABLE_PREFIX.'leads', 'l', 'l.id = t.lead_id')
    //             ->andWhere('l.owner_id = :userId')
    //             ->setParameter('userId', $this->userHelper->getUser()->getId());
    //     }

    //     $data = $query->loadAndBuildTimeData($q);
    //     $chart->setDataset($this->translator->trans('mautic.point.changes'), $data);

    //     return $chart->render();
    // }
}
