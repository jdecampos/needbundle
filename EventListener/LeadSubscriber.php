<?php

/*
 * @copyright   2017 Kgtech. All rights reserved
 * @author      Kgtech
 *
 * @link        https://www.kgtech.fi
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle\EventListener;

use Doctrine\ORM\Events;
use \Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Mautic\ConfigBundle\ConfigEvents;
use Mautic\ConfigBundle\Event\ConfigBuilderEvent;
use Mautic\ConfigBundle\Event\ConfigEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Symfony\Component\Translation\TranslatorInterface;
use Mautic\LeadBundle\Event\LeadListFiltersChoicesEvent;
use Mautic\LeadBundle\Event\LeadListFilteringEvent;
use Mautic\LeadBundle\LeadEvents;
use MauticPlugin\KompulseNeedBundle\Entity\ContactNeed;
use MauticPlugin\KompulseNeedBundle\Model\NeedModel;

/**
 * Class LeadSubscriber.
 */
class LeadSubscriber extends CommonSubscriber
{

    /**
     * @var NeedModel
     */
    protected $model;

    /**
        @var Mautic\CoreBundle\Translation\Translator
     */
    protected $translator;

    /**
     * LeadSubscriber constructor.
     *
     * @param NeedModel $model
     */
    public function __construct(NeedModel $model, TranslatorInterface $translator)
    {
        $this->model = $model;
        $this->translator = $translator;
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::LIST_FILTERS_CHOICES_ON_GENERATE   => ['onListChoicesGenerate', 0],
            LeadEvents::LIST_FILTERS_ON_FILTERING          => ['onListFiltering', 0],

        ];
    }

    /**
     * @param LeadListFiltersChoicesEvent $event
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \InvalidArgumentException
     */
    public function onListChoicesGenerate(LeadListFiltersChoicesEvent $event)
    {
        $needs = $this->model->getEntities();

        foreach ($needs as $need) {
            $event->addChoice(
                'lead',
                'need-filter-'.$need->getId(),
                array(
                    'label' => $this->translator->trans('kompulse.need.filter_need_prefix') . $need->getName(),
                    'properties' => [
                        'type' => 'number',
                    ],
                    'operators' => [
                        'eq' =>  $this->translator->trans('mautic.lead.list.form.operator.equals'),
                        'gt' =>  $this->translator->trans('mautic.lead.list.form.operator.greaterthan'),
                        'gte' =>  $this->translator->trans('mautic.lead.list.form.operator.greaterthanequals'),
                        'lt' =>  $this->translator->trans('mautic.lead.list.form.operator.lessthan'),
                        'lte' =>  $this->translator->trans('mautic.lead.list.form.operator.lessthanequals'),
                    ]
                )
            );
        }
    }


    /**
     * @param LeadListFilteringEvent $event
     */
    public function onListFiltering(LeadListFilteringEvent $event)
    {
        $details           = $event->getDetails();
        $leadId            = $event->getLeadId();
        $em                = $event->getEntityManager();
        $q                 = $event->getQueryBuilder();
        $alias             = $event->getAlias();
        $func              = $event->getFunc();
        $currentFilter     = $details['field'];
        $contactNeedTable = $em->getClassMetadata('KompulseNeedBundle:ContactNeed')->getTableName();

        $needs = $this->model->getEntities();

        foreach ($needs as $need) {
            $eventFilters[] = 'need-filter-'.$need->getId();
        }

        if (in_array($currentFilter, $eventFilters, true)) {
            $needId   = preg_replace('/\D/', '', $details['field']);
            $value    = $details['filter'];
            $operator = $details['operator'];

            $query = $em->getConnection()->createQueryBuilder()
                ->select($alias.$needId.'.id')
                ->from($contactNeedTable, $alias.$needId);

            $andX = $q->expr()->andX();
            $andX->add($q->expr()->eq($alias.$needId.'.lead_id', 'l.id'));
            $andX->add($q->expr()->eq($alias.$needId.'.need_id', $needId));
            $andX->add($q->expr()->$operator($alias.$needId . '.points ', $value));
            $query->where($andX);

            $subQueriesSQL['need-filter-' . $needId] = sprintf('EXISTS (%s) ', $query->getSQL());

            $event->setSubQuery(implode(' AND ', $subQueriesSQL));
        }
    }
}
