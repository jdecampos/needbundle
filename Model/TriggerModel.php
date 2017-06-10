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

use Mautic\CoreBundle\Helper\DateTimeHelper;
use Mautic\CoreBundle\Helper\IpLookupHelper;
use Mautic\CoreBundle\Model\FormModel as CommonFormModel;
use Mautic\PointBundle\Entity\LeadTriggerLog;
//use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\KompulseNeedBundle\Entity\ContactNeed;
use MauticPlugin\KompulseNeedBundle\Entity\ContactNeedTriggerLog;
use MauticPlugin\KompulseNeedBundle\Entity\Trigger;
use MauticPlugin\KompulseNeedBundle\Entity\TriggerEvent;
use MauticPlugin\KompulseNeedBundle\Event as Events;
use MauticPlugin\KompulseNeedBundle\KompulseNeedEvents;
use MauticPlugin\KompulseNeedBundle\Model\ContactNeedModel;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Class TriggerModel.
 */
class TriggerModel extends CommonFormModel
{
    /**
     * @deprecated Remove in 2.0
     *
     * @var MauticFactory
     */
    protected $factory;

    /**
     * @var IpLookupHelper
     */
    protected $ipLookupHelper;

    /**
     * @var ContactNeedModel
     */
    protected $contactNeedModel;

    /**
     * @var TriggerEventModel
     */
    protected $pointTriggerEventModel;

    /**
     * EventModel constructor.
     *
     * @param IpLookupHelper    $ipLookupHelper
     * @param LeadModel         $leadModel
     * @param TriggerEventModel $pointTriggerEventModel
     * @param ContactNeedModel  $contactNeedModel
     */
    public function __construct(
        IpLookupHelper $ipLookupHelper,
        ContactNeedModel $contactNeedModel,
        TriggerEventModel $pointTriggerEventModel
    )
    {
        $this->ipLookupHelper         = $ipLookupHelper;
        $this->contactNeedModel       = $contactNeedModel;
        $this->pointTriggerEventModel = $pointTriggerEventModel;
    }

    /**
     * {@inheritdoc}
     *
     * @return \MauticPluginBundle\Entity\TriggerRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('KompulseNeedBundle:Trigger');
    }

    /**
     * Retrieves an instance of the TriggerEventRepository.
     *
     * @return \MauticPlugin\KompulseNeedBundle\Entity\TriggerEventRepository
     */
    public function getEventRepository()
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
     * @throws MethodNotAllowedHttpException
     */
    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof Trigger) {
            throw new MethodNotAllowedHttpException(['Trigger']);
        }

        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create('kompulsetrigger', $entity, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Mautic\KompulseNeedBundle\Entity\Trigger $entity
     * @param bool                               $unlock
     */
    public function saveEntity($entity, $unlock = true)
    {
        $isNew = ($entity->getId()) ? false : true;

        parent::saveEntity($entity, $unlock);

        //should we trigger for existing contact needs?
        if ($entity->getTriggerExistingLeads() && $entity->isPublished()) {
            $events    = $entity->getEvents();
            $repo      = $this->getEventRepository();
            $persist   = [];
            $ipAddress = $this->ipLookupHelper->getIpAddress();

            foreach ($events as $event) {
                $filter = ['force' => [
                    // [
                    //     'column' => 'cn.date_added',
                    //     'expr'   => 'lte',
                    //     'value'  => (new DateTimeHelper($entity->getDateAdded()))->toUtcString(),
                    // ],
                    [
                        'column' => 'cn.points',
                        'expr'   => 'gte',
                        'value'  => $entity->getPoints(),
                    ],
                    [
                        'column' => 'cn.need',
                        'expr'   => 'eq',
                        'value'  => $entity->getNeed()->getId(),
                    ]
                ]];

                if (!$isNew) {
                    //get a list of leads that has already had this event applied
                    $contactNeedIds = $repo->getContactNeedsForEvent($event->getId());
                    if (!empty($contactNeedIds)) {
                        $filter['force'][] = [
                            'column' => 'cn.id',
                            'expr'   => 'notIn',
                            'value'  => $contactNeedIds,
                        ];
                    }
                }

                //get a list of leads that are before the trigger's date_added and trigger if not already done so
                $contactNeeds = $this->contactNeedModel->getEntities([
                    'filter' => $filter,
                ]);

                foreach ($contactNeeds as $contactNeed) {
                    if ($this->triggerEvent($event->convertToArray(), $contactNeed, true)) {
                        $log = new ContactNeedTriggerLog();
                        $log->setIpAddress($ipAddress);
                        $log->setEvent($event);
                        $log->setContactNeed($contactNeed);
                        $log->setDateFired(new \DateTime());
                        $event->addLog($log);
                        $persist[] = $event;
                    }
                }
            }

            if (!empty($persist)) {
                $repo->saveEntities($persist);
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return Trigger|null
     */
    public function getEntity($id = null)
    {
        if ($id === null) {
            return new Trigger();
        }

        return parent::getEntity($id);
    }

    /**
     * {@inheritdoc
     *
     * @throws MethodNotAllowedHttpException
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, Event $event = null)
    {
        if (!$entity instanceof Trigger) {
            throw new MethodNotAllowedHttpException(['Trigger']);
        }

        switch ($action) {
            case 'pre_save':
                $name = KompulseNeedEvents::TRIGGER_PRE_SAVE;
                break;
            case 'post_save':
                $name = KompulseNeedEvents::TRIGGER_POST_SAVE;
                break;
            case 'pre_delete':
                $name = KompulseNeedEvents::TRIGGER_PRE_DELETE;
                break;
            case 'post_delete':
                $name = KompulseNeedEvents::TRIGGER_POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (empty($event)) {
                $event = new Events\TriggerEvent($entity, $isNew);
            }

            $this->dispatcher->dispatch($name, $event);

            return $event;
        }

        return null;
    }

    /**
     * @param Trigger $entity
     * @param array   $sessionEvents
     */
    public function setEvents(Trigger $entity, $sessionEvents)
    {
        $order           = 1;
        $existingActions = $entity->getEvents();

        foreach ($sessionEvents as $properties) {
            $isNew = (!empty($properties['id']) && isset($existingActions[$properties['id']])) ? false : true;
            $event = !$isNew ? $existingActions[$properties['id']] : new TriggerEvent();

            foreach ($properties as $f => $v) {
                if (in_array($f, ['id', 'order'])) {
                    continue;
                }

                $func = 'set'.ucfirst($f);
                if (method_exists($event, $func)) {
                    $event->$func($v);
                }
            }
            $event->setTrigger($entity);
            $event->setOrder($order);
            ++$order;
            $entity->addTriggerEvent($properties['id'], $event);
        }

        // Persist if editing the trigger
        if ($entity->getId()) {
            $this->pointTriggerEventModel->saveEntities($entity->getEvents());
        }
    }

    /**
     * Gets array of custom events from bundles subscribed PointEvents::TRIGGER_ON_BUILD.
     *
     * @return mixed
     */
    public function getEvents()
    {
        static $events;

        if (empty($events)) {
            //build them
            $events = [];
            $event  = new Events\TriggerBuilderEvent($this->translator);
            $this->dispatcher->dispatch(KompulseNeedEvents::TRIGGER_ON_BUILD, $event);
            $events = $event->getEvents();
        }

        return $events;
    }

    /**
     * Gets array of custom events from bundles inside groups.
     *
     * @return mixed
     */
    public function getEventGroups()
    {
        $events = $this->getEvents();
        $groups = [];
        foreach ($events as $key => $event) {
            $groups[$event['group']][$key] = $event;
        }

        return $groups;
    }

    /**
     * Triggers a specific event.
     *
     * @param array $event
     * @param ContactNeed  $contactNeed
     * @param bool  $force
     *
     * @return bool Was event triggered
     */
    public function triggerEvent($event, ContactNeed $contactNeed,  $force = false)
    {
        //only trigger events for anonymous users
        if (!$force && !$this->security->isAnonymous()) {
            return false;
        }

        if (!$force) {
            //get a list of events that has already been performed on this lead
            $appliedEvents = $this->getEventRepository()->getContactNeedTriggeredEvents($contactNeed->getId());

            //if it's already been done, then skip it
            if (isset($appliedEvents[$event['id']])) {
                return false;
            }
        }

        $availableEvents = $this->getEvents();
        $eventType       = $event['type'];

        //make sure the event still exists
        if (!isset($availableEvents[$eventType])) {
            return false;
        }

        $settings = $availableEvents[$eventType];
        $args     = [
            'event'        => $event,
            'lead'         => $contactNeed->getLead(),
            'factory'      => $this->factory, // WHAT??
            'config'       => $event['properties'],
        ];

        if (is_callable($settings['callback'])) {
            if (is_array($settings['callback'])) {
                $reflection = new \ReflectionMethod($settings['callback'][0], $settings['callback'][1]);
            } elseif (strpos($settings['callback'], '::') !== false) {
                $parts      = explode('::', $settings['callback']);
                $reflection = new \ReflectionMethod($parts[0], $parts[1]);
            } else {
                $reflection = new \ReflectionMethod(null, $settings['callback']);
            }

            $pass = [];
            foreach ($reflection->getParameters() as $param) {
                if (isset($args[$param->getName()])) {
                    $pass[] = $args[$param->getName()];
                } else {
                    $pass[] = null;
                }
            }

            return $reflection->invokeArgs($this, $pass);
        }

        return false;
    }

    /**
     * Trigger events for the current contact need.
     *
     * @param ContactNeed $contactNeed
     */
    public function triggerEvents(ContactNeed $contactNeed)
    {
        $points = $contactNeed->getPoints();

        //find all published triggers that is applicable to this points
        /** @var \MauticPlugin\KompulseNeedBundle\Entity\TriggerEventRepository $repo */
        $repo   = $this->getEventRepository();
        $events = $repo->getPublishedByPointTotal($points, $contactNeed->getNeed());

        if (!empty($events)) {
            //get a list of actions that has already been applied to this lead
            $appliedEvents = $repo->getContactNeedTriggeredEvents($contactNeed->getId());
            $ipAddress     = $this->ipLookupHelper->getIpAddress();
            $persist       = [];
            foreach ($events as $event) {
                if (isset($appliedEvents[$event['id']])) {
                    //don't apply the event to the lead if it's already been done
                    continue;
                }

                if ($this->triggerEvent($event, $contactNeed, true)) {
                    $log = new ContactNeedTriggerLog();
                    $log->setIpAddress($ipAddress);
                    $log->setEvent($this->em->getReference('KompulseNeedBundle:TriggerEvent', $event['id']));
                    $log->setContactNeed($contactNeed);
                    $log->setDateFired(new \DateTime());
                    $persist[] = $log;
                }
            }

            if (!empty($persist)) {
                $this->getEventRepository()->saveEntities($persist);

                $this->em->clear('MauticPlugin\KompulseNeedBundle\Entity\ContactNeedTriggerLog');
                $this->em->clear('MauticPlugin\KompulseNeedBundle\Entity\TriggerEvent');
            }
        }
    }

    /**
     * Returns configured color based on passed in $points.
     *
     * @param $points
     *
     * @return string
     */
    public function getColorForContactNeedPoints($points)
    {
        static $triggers;

        if (!is_array($triggers)) {
            $triggers = $this->getRepository()->getTriggerColors();
        }

        foreach ($triggers as $trigger) {
            if ($points >= $trigger['points']) {
                return $trigger['color'];
            }
        }

        return '';
    }
}
