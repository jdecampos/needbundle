<?php

/*
 * @author      Kgtech
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle\EventListener;

use Mautic\CampaignBundle\Model\CampaignModel;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Helper\Chart\ChartQuery;
use Mautic\CoreBundle\Helper\Chart\LineChart;
use Mautic\CoreBundle\Helper\Chart\PieChart;
use Mautic\LeadBundle\Model\CompanyModel;
use Mautic\LeadBundle\Model\FieldModel;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\LeadBundle\Model\ListModel;
use Mautic\ReportBundle\Event\ReportBuilderEvent;
use Mautic\ReportBundle\Event\ReportDataEvent;
use Mautic\ReportBundle\Event\ReportGeneratorEvent;
use Mautic\ReportBundle\Event\ReportGraphEvent;
use Mautic\ReportBundle\ReportEvents;
use Mautic\StageBundle\Model\StageModel;
use Mautic\UserBundle\Model\UserModel;

/**
 * Class ReportSubscriber.
 */
class ReportSubscriber extends CommonSubscriber
{
    /**
     * @var ListModel
     */
    protected $listModel;

    /**
     * @var ListModel
     */
    protected $fieldModel;

    /**
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var StageModel
     */
    protected $stageModel;

    /**
     * @var CampaignModel
     */
    protected $campaignModel;

    /**
     * @var CompanyModel
     */
    protected $companyModel;

    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var array
     */
    protected $channels;

    /**
     * @var array
     */
    protected $channelActions;

    /**
     * ReportSubscriber constructor.
     *
     * @param ListModel     $listModel
     * @param FieldModel    $fieldModel
     * @param LeadModel     $leadModel
     * @param StageModel    $stageModel
     * @param CampaignModel $campaignModel
     * @param UserModel     $userModel
     */
    public function __construct(
        ListModel $listModel,
        FieldModel $fieldModel
    ) {
        $this->listModel     = $listModel;
        $this->fieldModel    = $fieldModel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ReportEvents::REPORT_ON_BUILD          => ['onReportBuilder', 0],
            ReportEvents::REPORT_ON_GENERATE       => ['onReportGenerate', 0],
        ];
    }

    /**
     * Add available tables, columns, and graphs to the report builder lookup
     *
     * @param ReportBuilderEvent $event
     *
     * @return void
     */
    public function onReportBuilder (ReportBuilderEvent $event)
    {
        // Use checkContext() to determine if the report being requested is this report
        if ($event->checkContext(array('plugin_kompulse_contact_need'))) {
            // Define the columns that are available to the report.
            $prefix  = 'cn.';
            $needPrefix = 'n.';
            $leadPrefix = 'l.';
            $columns = array(
                $needPrefix . 'name' => array(
                    'label' => 'kompulse.report.need',
                    'type'  => 'string'
                ),
                $prefix . 'points' => array(
                    'label' => 'kompulse.report.points',
                    'type'  => 'int'
                ),
            );

            $leadFields = $this->fieldModel->getEntities([
                'filter' => [
                    'force' => [
                        [
                            'column' => 'f.object',
                            'expr'   => 'like',
                            'value'  => 'lead',
                        ],
                    ],
                ],
            ]);

            // Append segment filters
            $userSegments = $this->listModel->getUserLists();
            $list         = [];
            foreach ($userSegments as $segment) {
                $list[$segment['id']] = $segment['name'];
            }
            $filters['s.leadlist_id'] = [
                'alias'     => 'segment_id',
                'label'     => 'mautic.core.filter.lists',
                'type'      => 'select',
                'list'      => $list,
                'operators' => [
                    'eq' => 'mautic.core.operator.equals',
                ],
            ];

            // Several helper functions are available to append common columns such as categories, publish state fields, lead, etc.  Refer to the ReportBuilderEvent class for more details.
            $columns = $filters = array_merge($columns, $this->getFieldColumns($leadFields, 'l.'));


            // Add the table to the list
            $event->addTable('plugin_kompulse_contact_need',
                array(
                    'display_name' => 'kompulse.data_source.contact_need',
                    'columns'      => $columns,
                    'filters'      => $filters,
                )
            );
        }
    }



    /**
     * Initialize the QueryBuilder object to generate reports from.
     *
     * @param ReportGeneratorEvent $event
     */
    public function onReportGenerate(ReportGeneratorEvent $event)
    {
        $context = $event->getContext();
        $qb      = $event->getQueryBuilder();

        switch ($context) {
            case 'plugin_kompulse_contact_need':
                $qb->from(MAUTIC_TABLE_PREFIX.'plugin_kompulse_contact_need', 'cn')
                    ->leftJoin('cn', MAUTIC_TABLE_PREFIX.'leads', 'l', 'l.id = cn.lead_id')
                    ->leftJoin('cn', MAUTIC_TABLE_PREFIX.'plugin_kompulse_need', 'n', 'n.id = cn.need_id')
                ;
                break;
        }

        $event->setQueryBuilder($qb);
    }


    /**
     * @param $fields
     * @param $prefix
     *
     * @return array
     */
    protected function getFieldColumns($fields, $prefix)
    {
        $columns = [];
        foreach ($fields as $f) {
            switch ($f->getType()) {
                case 'boolean':
                    $type = 'bool';
                    break;
                case 'date':
                    $type = 'date';
                    break;
                case 'datetime':
                    $type = 'datetime';
                    break;
                case 'time':
                    $type = 'time';
                    break;
                case 'url':
                    $type = 'url';
                    break;
                case 'email':
                    $type = 'email';
                    break;
                case 'number':
                    $type = 'float';
                    break;
                default:
                    $type = 'string';
                    break;
            }
            $columns[$prefix.$f->getAlias()] = [
                'label' => $f->getLabel(),
                'type'  => $type,
            ];
        }

        return $columns;
    }
}
