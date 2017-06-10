<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;
use Mautic\LeadBundle\Entity\Lead;
/**
 * Class NeedPointRepository.
 */
class NeedPointRepository extends CommonRepository
{
    /**
     * {@inheritdoc}
     */
    public function getEntities(array $args = [])
    {
        $q = $this->_em
            ->createQueryBuilder()
            ->select($this->getTableAlias().', cat')
            ->from('KompulseNeedBundle:NeedPoint', $this->getTableAlias())
            ->leftJoin($this->getTableAlias().'.category', 'cat');

        $args['qb'] = $q;

        return parent::getEntities($args);
    }

    /**
     * {@inheritdoc}
     */
    public function getTableAlias()
    {
        return 'np';
    }

    /**
     * Get array of published actions based on type.
     *
     * @param string $type
     *
     * @return array
     */
    public function getPublishedByType($type)
    {
        $q = $this->createQueryBuilder('np')
            ->select('partial np.{id, type, name, delta, need, properties}')
            ->setParameter('type', $type);

        //make sure the published up and down dates are good
        $expr = $this->getPublishedByDateExpression($q);
        $expr->add($q->expr()->eq('np.type', ':type'));

        $q->where($expr);

        return $q->getQuery()->getResult();
    }

    /**
     *
     */
    public function getContactNeed(Lead $lead, Need $need)
    {
        $q = $this->_em->getConnection()->createQueryBuilder()
           ->select('cn.*')
           ->from(MAUTIC_TABLE_PREFIX.'plugin_kompulse_contact_need', 'cn');

        $q->where(
            $q->expr()->andX(
                $q->expr()->eq('cn.need_id', (int) $need->getId()),
                $q->expr()->eq('cn.lead_id', (int) $lead->getId())
            )
        );

        $results = $q->execute()->fetchAll();

        return $results[0] ?? null;
    }

    /**
     * @param string $type
     * @param int    $leadId
     *
     * @return array
     */
    public function getCompletedLeadActions($type, $leadId)
    {
        $q = $this->_em->getConnection()->createQueryBuilder()
            ->select('np.*')
            ->from(MAUTIC_TABLE_PREFIX.'plugin_kompulse_need_point_contact_action_log', 'x')
            ->innerJoin('x', MAUTIC_TABLE_PREFIX.'plugin_kompulse_need_points', 'np', 'x.need_point_id = np.id');

        //make sure the published up and down dates are good
        $q->where(
            $q->expr()->andX(
                $q->expr()->eq('np.type', ':type'),
                $q->expr()->eq('x.lead_id', (int) $leadId)
            )
        )
            ->setParameter('type', $type);

        $results = $q->execute()->fetchAll();

        $return = [];

        foreach ($results as $r) {
            $return[$r['id']] = $r;
        }

        return $return;
    }

    // /**
    //  * @param int $leadId
    //  *
    //  * @return array
    //  */
    // public function getCompletedLeadActionsByLeadId($leadId)
    // {
    //     $q = $this->_em->getConnection()->createQueryBuilder()
    //         ->select('p.*')
    //         ->from(MAUTIC_TABLE_PREFIX.'point_lead_action_log', 'x')
    //         ->innerJoin('x', MAUTIC_TABLE_PREFIX.'points', 'p', 'x.point_id = p.id');

    //     //make sure the published up and down dates are good
    //     $q->where(
    //         $q->expr()->andX(
    //             $q->expr()->eq('x.lead_id', (int) $leadId)
    //         )
    //     );

    //     $results = $q->execute()->fetchAll();

    //     $return = [];

    //     foreach ($results as $r) {
    //         $return[$r['id']] = $r;
    //     }

    //     return $return;
    // }
    // /**
    //  * {@inheritdoc}
    //  */
    // protected function addCatchAllWhereClause(&$q, $filter)
    // {
    //     return $this->addStandardCatchAllWhereClause($q, $filter, [
    //         'p.name',
    //         'p.description',
    //     ]);
    // }

    // /**
    //  * {@inheritdoc}
    //  */
    // protected function addSearchCommandWhereClause(&$q, $filter)
    // {
    //     return $this->addStandardSearchCommandWhereClause($q, $filter);
    // }

    // /**
    //  * {@inheritdoc}
    //  */
    // public function getSearchCommands()
    // {
    //     return $this->getStandardSearchCommands();
    // }
}
