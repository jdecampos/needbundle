<?php

namespace MauticPlugin\KompulseNeedBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;
use Mautic\LeadBundle\Entity\Lead;

/**
 * NeedRepository
 */
class NeedRepository extends CommonRepository
{

    public function getEntities(array $args = array())
    {
        $q = $this
            ->createQueryBuilder('n');

        $args['qb'] = $q;

        return parent::getEntities($args);
    }

    /**
     * Get a count of users that belong to the need.
     *
     * @param $needIds
     *
     * @return array
     */
    public function getUserCount($needIds)
    {
        $q = $this->_em->getConnection()->createQueryBuilder();

        $q->select('count(cn.lead_id) as thecount, cn.need_id')
            ->from(MAUTIC_TABLE_PREFIX.'plugin_kompulse_contact_need', 'cn');

        $returnArray = (is_array($needIds));

        if (!$returnArray) {
            $needIds = [$needIds];
        }

        $q->where(
            $q->expr()->in('cn.need_id', $needIds)
        )
            ->groupBy('cn.need_id');

        $result = $q->execute()->fetchAll();

        $return = [];
        foreach ($result as $r) {
            $return[$r['need_id']] = $r['thecount'];
        }

        // Ensure lists without leads have a value
        foreach ($needIds as $r) {
            if (!isset($return[$r])) {
                $return[$r] = 0;
            }
        }

        return ($returnArray) ? $return : $return[$needIds[0]];
    }

    public function getSelectListQueryBuilder(Lead $lead)
    {
        $in = $this->getEntityManager()->getRepository('KompulseNeedBundle:ContactNeed')
             ->createQueryBuilder('cn')
             ->select('IDENTITY(cn.need)')
             ->andWhere("cn.lead = '". $lead->getId() ."'");

        $qb = $this->createQueryBuilder('n');

        return $qb->where(
            $qb->expr()->notIn('n.id', $in->getDQL())
        );
    }

    public function getNeedList($prefix = '')
    {
        $return = [];

        $qb = $this->createQueryBuilder('n');
        $needs = $qb->getQuery()->getResult();

        foreach ($needs as $need) {
            $return[$prefix.$need->getId()] = $need->getName();
        }

        return $return;
    }
}
